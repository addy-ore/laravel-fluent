<?php

namespace Based\Fluent\Casts;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class AsDecimal extends AbstractCaster
{
    public const TYPE_NAME = 'decimal';

    public function __construct(
        public int $modifier = 2
    ) {}

    public function asType(): string
    {
        return collect([self::TYPE_NAME, $this->modifier])
            ->whereNotNull()
            ->join(':');
    }
}
