<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\HasFluentBindings;
use Based\Fluent\Guards\Fillable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

#[Fillable(Fillable::INCLUDE_PRIMARY_KEY|Fillable::INCLUDE_DATES)]
class FluentModelAllFillableFlags extends Model
{
    use HasFluentBindings;

    public int $id;
    public string $foo;
    public string $bar;
    public string $baz;
    public Carbon $created_at;
    public Carbon $updated_at;
}
