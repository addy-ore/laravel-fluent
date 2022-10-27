<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\Casts\AsDecimal;
use Based\Fluent\Casts\Cast;
use Based\Fluent\Fluent;
use Based\Fluent\Guards\Fillable;
use Based\Fluent\Guards\Guarded;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

#[Fillable]
class FluentModelGuardedWithGuards extends Model
{
    use Fluent;

    public int $id;
    public string $one;
    public string $two;
    #[Guarded]
    public string $three;
    public string $four;
}
