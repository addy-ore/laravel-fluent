<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\HasFluentBindings;
use Based\Fluent\Guards\Guarded;
use Illuminate\Database\Eloquent\Model;

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
