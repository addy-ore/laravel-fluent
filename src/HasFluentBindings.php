<?php

namespace Based\Fluent;

/** @mixin \Illuminate\Database\Eloquent\Model */
trait HasFluentBindings
{
    use HasRelations,
        HasProperties;
}
