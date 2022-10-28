<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\HasFluentBindings;
use Illuminate\Database\Eloquent\Model;

class FluentModelWithDefaults extends Model
{
    public const ALPHA_DEFAULT = 123;
    public const BETA_DEFAULT = 456;

    use HasFluentBindings;

    protected $attributes = [
        'alpha' => self::ALPHA_DEFAULT
    ];

    public int $alpha;
    public int $beta = self::BETA_DEFAULT;
    public ?int $gamma;
}
