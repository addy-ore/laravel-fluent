<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\Casts\AsDecimal;
use Based\Fluent\Casts\Cast;
use Based\Fluent\HasFluentBindings;
use Based\Fluent\Guards\Fillable;
use Based\Fluent\Guards\Guarded;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FluentModelUnguardedWithGuards extends Model
{
    use HasFluentBindings;

    public int $id;
    public string $alpha;
    public string $bravo;
    #[Guarded]
    public string $charlie;
    #[Guarded]
    public string $delta;

    protected $guarded = [];
}
