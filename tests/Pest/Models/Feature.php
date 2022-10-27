<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\HasFluentBindings;
use Based\Fluent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFluentBindings;

    #[BelongsTo]
    public Product $product;

    protected $guarded = [];
}
