<?php

namespace Based\Fluent;

use Based\Fluent\Casts\AbstractCaster;
use Based\Fluent\Guards\Fillable;
use Based\Fluent\Guards\Guarded;
use Based\Fluent\Relations\AbstractRelation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait HasProperties
{
    protected Collection $fluentProperties;
    protected bool $fluentAvoidParentSetAttribute = false;

    public function __construct(array $attributes = [])
    {
        $this->buildFluentGuards();
        $this->buildFluentDefaults();
        $this->buildFluentCasts();

        parent::__construct($attributes);

        $this->hydrateFluentProperties();
    }

    /**
     * Get public properties.
     *
     * @return \Illuminate\Support\Collection<ReflectionProperty>|ReflectionProperty[]
     */
    public function getFluentProperties(): Collection
    {
        if (isset($this->fluentProperties)) {
            return $this->fluentProperties;
        }

        $reflection = new ReflectionClass($this);

        return $this->fluentProperties = collect($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
            ->filter(fn (ReflectionProperty $property) => $property->getDeclaringClass()->getName() === self::class)
            ->reject(function (ReflectionProperty $property) {
                return collect($property->getDeclaringClass()->getTraits())
                    ->contains(function (ReflectionClass $trait) use ($property) {
                        return collect($trait->getProperties(ReflectionProperty::IS_PUBLIC))
                            ->contains(function (ReflectionProperty $traitProperty) use ($property) {
                                return $traitProperty->getName() === $property->getName();
                            });
                    });
            })
            ->filter(fn (ReflectionProperty $property) => $property->hasType())
            ->reject(function (ReflectionProperty $property) {
                return is_subclass_of($property->getType()->getName(), Model::class)
                    || !empty($property->getAttributes(AbstractRelation::class, ReflectionAttribute::IS_INSTANCEOF));
            });
    }

    /**
     * Overload the method to avoid interfering with model loading
     * Set the array of model attributes. No checking is done.
     *
     * @param  array  $attributes
     * @param  bool  $sync
     * @return $this
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        $keys = $this->getFluentProperties()
            ->filter(fn (ReflectionProperty $property) => $property->isInitialized($this))
            ->map(fn (ReflectionProperty $property) => $property->getName())->toArray();

        /*
         * Unset all properties that we're managing, so any current values aren't prioritized
         * over the ones being set here. We would otherwise clobber new values during operations
         * that retreive a model, such as Model::newFromBuilder or Model::refresh.
         */
        foreach ($keys as $key) {
            unset($this->{$key});
        }

        parent::setRawAttributes($attributes, $sync);

        try {
            /*
             * Laravel has already set these attributes internally— avoid redundant calls to
             * parent::setAttribute during this logic (due to property assignment calling __set).
             */
            $this->fluentAvoidParentSetAttribute = true;

            foreach ($keys as $key) {
                $this->{$key} = $this->getAttribute($key);
            }
        } finally {
            $this->fluentAvoidParentSetAttribute = false;
        }

        return $this;
    }

    /**
     * Overload the method to populate public properties from Model attributes
     * Set a given attribute on the model.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        if ($this->fluentAvoidParentSetAttribute) {
            return $this;
        }

        // Tricky part to prevent attribute overwriting by mergeAttributesFromClassCasts
        if ($this->hasFluentProperty($key)) {
            unset($this->{$key});
        }

        parent::setAttribute($key, $value);

        if ($this->hasFluentProperty($key)) {
            $this->{$key} = $this->getAttribute($key);
        }

        return $this;
    }

    /**
     * Overload the method to populate attributes from public properties
     * Merge the cast class attributes back into the model.
     *
     * @return void
     */
    public function mergeAttributesFromClassCasts()
    {
        $this->getFluentProperties()
            ->filter(function (ReflectionProperty $property) {
                return $property->isInitialized($this);
            })
            ->each(function (ReflectionProperty $property) {
                parent::setAttribute($property->getName(), $this->{$property->getName()});
            });

        parent::mergeAttributesFromClassCasts();
    }

    /**
     * Hydrate public properties on model retrieve.
     *
     * @return void
     */
    protected static function bootHasProperties()
    {
        self::retrieved(function (self $model) {
            $model->hydrateFluentProperties();
        });
    }

    /**
     * Determine if a model has a public property.
     *
     * @param  string  $key
     * @return bool
     */
    protected function hasFluentProperty(string $key): bool
    {
        return $this->getFluentProperties()
            ->contains(fn (ReflectionProperty $property) => $property->getName() === $key);
    }

    /**
     * Hydrate public properties with attributes data.
     *
     * @return void
     */
    public function hydrateFluentProperties(): void
    {
        $this->getFluentProperties()
            ->filter(fn (ReflectionProperty $property) => array_key_exists($property->getName(), $this->attributes))
            ->each(function (ReflectionProperty $property) {
                $value = $this->getAttribute($property->getName());

                if (is_null($value) && ! $property->getType()->allowsNull()) {
                    return;
                }

                $this->{$property->getName()} = $value;
            });
    }

    /**
     * Crunch any {@see Fillable} and {@see Guarded} attributes into Laravel's backing arrays.
     *
     * @return void
     */
    protected function buildFluentGuards(): void
    {
        $class = new ReflectionClass($this);

        if (!empty($class->getAttributes(Guarded::class))) {
            // reset guarded array to ["*"], if it isn't already
            if ($this->guarded !== ["*"]) {
                $this->guarded = ["*"];
            }
        }

        $allFillable = false;
        $allFillableExcept = [];

        if (!empty($class->getAttributes(Fillable::class))) {
            $allFillable = true;

            /** @var Fillable $fillableFlags */
            $fillableFlags = $class->getAttributes(Fillable::class)[0]->newInstance();

            if (!$fillableFlags->includesPrimaryKey()) {
                $allFillableExcept[] = $this->getKeyName();
            }

            if (!$fillableFlags->includesDates()) {
                $allFillableExcept[] = static::CREATED_AT;
                $allFillableExcept[] = static::UPDATED_AT;
                $allFillableExcept[] = defined('static::DELETED_AT') ? static::DELETED_AT : 'deleted_at';
            }
        }

        $this->getFluentProperties()
            ->each(function (ReflectionProperty $property) use ($allFillable, $allFillableExcept) {
                if (!empty($property->getAttributes(Guarded::class))) {
                    // explicitly guarded, at the property level
                    $this->markAsGuarded($property);
                } else if ($allFillable && !in_array($property->getName(), $allFillableExcept)) {
                    // implicitly fillable, via a Fillable attribute at the class level
                    $this->markAsFillable($property);
                } else if (!empty($property->getAttributes(Fillable::class))) {
                    // explicitly fillable, at the property level
                    $this->markAsFillable($property);
                }
            });
    }

    /**
     * Mark the given individual property as fillable within Laravel's arrays.
     *
     * @param ReflectionProperty $property
     * @return void
     */
    private function markAsFillable(ReflectionProperty $property): void
    {
        // add this property to the fillable array, if absent
        if (!in_array($property->getName(), $this->fillable)) {
            $this->fillable[] = $property->getName();
        }

        // remove this property from the guarded array, if present
        if ($this->guarded !== ["*"]) {
            $indexOf = array_search($property->getName(), $this->guarded);

            if ($indexOf !== false) {
                $this->guarded = array_splice($this->guarded, $indexOf, 1);
            }
        }
    }

    /**
     * Mark the given individual property as guarded within Laravel's arrays.
     *
     * @param ReflectionProperty $property
     * @return void
     */
    private function markAsGuarded(ReflectionProperty $property): void
    {
        $indexOf = array_search($property->getName(), $this->fillable);

        // remove this property from the fillable array, if present
        if ($indexOf !== false) {
            $this->fillable = array_splice($this->fillable, $indexOf, 1);
        }

        // add this property to the guarded array, unless it's already ["*"]
        if ($this->guarded !== ["*"]) {
            $this->guarded[] = $property->getName();
        }
    }

    /**
     * Insert any property-level default values into Laravel's $attributes array.
     *
     * @return void
     */
    protected function buildFluentDefaults(): void
    {
        $propertyDefinedDefaults = [];

        $this->getFluentProperties()
            ->filter(fn (ReflectionProperty $property) => $property->hasDefaultValue())
            ->each(function (ReflectionProperty $property) use (&$propertyDefinedDefaults) {
                $propertyDefinedDefaults[$property->getName()] = $property->getDefaultValue();
            });

        $this->attributes = array_merge($this->attributes, $propertyDefinedDefaults);
    }

    /**
     * Build model casts for public properties.
     *
     * @return void
     */
    protected function buildFluentCasts(): void
    {
        $nativeCasts = $this->getFluentProperties()
            ->reject(function (ReflectionProperty $property) {
                return in_array(
                    $property->getName(),
                    [
                        static::CREATED_AT,
                        static::UPDATED_AT,
                        defined('static::DELETED_AT') ? static::DELETED_AT : 'deleted_at',
                    ]
                );
            })
            ->mapWithKeys(function (ReflectionProperty $property) {
                return [$property->getName() => $this->getFluentCastType($property)];
            })
            ->whereNotNull()
            ->toArray();

        $this->casts = array_merge($this->casts, $nativeCasts);
    }

    /**
     * Get cast type from native property type.
     *
     * @param  \ReflectionProperty  $property
     * @return null|string
     */
    private function getFluentCastType(ReflectionProperty $property): ?string
    {
        $type = str_replace('?', '', $property->getType());
        $type = match ($type) {
            Collection::class => 'collection',
            Carbon::class => 'datetime',
            'bool' => 'boolean',
            'int' => 'integer',
            default => $type,
        };

        if (!empty($abstractCasters = $property->getAttributes(AbstractCaster::class, ReflectionAttribute::IS_INSTANCEOF))) {
            /** @var AbstractCaster $abstractCaster */
            // if multiple AbstractCaster attributes are defined, we will favor the first
            $abstractCaster = $abstractCasters[0]->newInstance();

            return $abstractCaster->asType() ?? $type;
        }

        return $type;
    }
}
