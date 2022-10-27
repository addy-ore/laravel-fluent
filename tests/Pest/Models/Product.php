<?php

namespace Based\Fluent\Tests\Models;

use Based\Fluent\HasFluentBindings;
use Based\Fluent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Product extends Model
{
    use HasFluentBindings;

    public Category $category;

    #[HasMany(Feature::class)]
    public Collection $features;

    protected $guarded = [];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
