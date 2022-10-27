<?php

namespace Based\Fluent\Guards;

use Attribute;

/**
 * Specify that a property or class should be fillable (mass-assignable).
 *
 * Note: use of this attribute requires the associated class to be using
 * one of the {@see HasFluentBindings} or {@see HasProperties} traits.
 *
 * When used to annotate a class, most public properties of that class that are
 * being managed by this library will be inserted into the fillable array,
 * individually, unless they are annotated otherwise (via {@see Guarded}).
 * By default, the Model's primary key and the configured Laravel-managed
 * timestamp fields will be excluded. See {@see Fillable::INCLUDE_PRIMARY_KEY}
 * and {@see Fillable::INCLUDE_DATES} for flags that may be passed to explicitly
 * include these properties.
 *
 * When used to annotate an individual property, that property will be added
 * to the fillable array, if not already present. It will also be removed from
 * the guarded array, if it was explicitly declared there.
 */
#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class Fillable
{
    /**
     * The default behavior of declaring Fillable at the class-level excludes the Model's primary key.
     *
     * Passing this flag to an attribute declared at the class-level will indicate that the Model's
     * primary key should be _included_ in the properties that are being marked as fillable.
     *
     * This flag has no significance when passed to an attribute declared at the property level.
     */
    public const INCLUDE_PRIMARY_KEY = 1;
    /**
     * The default behavior of declaring Fillable at the class-level excludes the Model's Laravel-managed
     * timestamp columns (i.e. the created_at, updated_at, and deleted_at columns, by default).
     *
     * Passing this flag to an attribute declared at the class-level will indicate that those
     * timestamp columns should be _included_ in the properties that are being marked as fillable.
     *
     * This flag has no significance when passed to an attribute declared at the property level.
     */
    public const INCLUDE_DATES = 2;
    public const INCLUDE_ALL = self::INCLUDE_PRIMARY_KEY | self::INCLUDE_DATES;

    public function __construct(
        public int $flags = 0
    ) {}

    public function includesPrimaryKey(): bool
    {
        return ($this->flags & self::INCLUDE_PRIMARY_KEY) !== 0;
    }

    public function includesDates(): bool
    {
        return ($this->flags & self::INCLUDE_DATES) !== 0;
    }
}
