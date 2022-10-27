<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\HasFluentBindings;
use Based\Fluent\Tests\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Model;

class Mediable extends Model
{
    use HasFluentBindings,
        HasMedia;

    protected $guarded = [];
}
