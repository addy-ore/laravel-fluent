<?php

namespace Based\Fluent\Casts;

use Attribute;

/**
 * Define an explicit cast to a given type.
 */
#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class Cast extends AbstractCaster
{
    public function __construct(
        public string $type
    ) {}

    public function asType(): string
    {
        return $this->type;
    }
}
