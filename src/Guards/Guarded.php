<?php

namespace Based\Fluent\Guards;

use Attribute;

/**
 * Specify that a property or class should be guarded, or non-mass-assignable.
 *
 * Note: use of this attribute requires the associated class to be using
 * one of the {@see HasFluentBindings} or {@see HasProperties} traits.
 *
 * When used to annotate a class, the guarded array will be reset to contain only "*".
 * Note that this is already the default state within Laravel; callers should ensure
 * they have a circumstance that differs from this default before using this attribute.
 *
 * When used to annotate a property, that property will be explicitly excluded from the
 * fillable array, if present. That property will also be added to the guarded array,
 * unless the guarded array is already ["*"], as Laravel treats this as a special case.
 */
#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class Guarded
{

}
