<?php

namespace Based\Fluent\Casts;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class AsDate extends AbstractCaster
{
    public const TYPE_NAME = 'datetime';

    public function __construct(
        public ?string $modifier = null
    ) {}

    public function asType(): string
    {
        return collect([self::TYPE_NAME, $this->modifier])
            ->whereNotNull()
            ->join(':');
    }
}
