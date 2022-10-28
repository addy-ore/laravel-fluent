<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\HasFluentBindings;
use Based\Fluent\Guards\Fillable;
use Based\Fluent\Guards\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Fillable]
class FluentModelGuardedWithGuards extends Model
{
    use HasFluentBindings;

    public int $id;
    public string $one;
    public string $two;
    #[Guarded]
    public string $three;
    public string $four;
}
