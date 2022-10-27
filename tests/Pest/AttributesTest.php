<?php

use Based\Fluent\Tests\Models\FluentModel;
use Based\Fluent\Tests\Models\FluentModelAllFillableFlags;
use Based\Fluent\Tests\Models\FluentModelUnguardedWithGuards;
use Based\Fluent\Tests\Models\FluentModelWithDefaults;
use Based\Fluent\Tests\Models\FluentModelGuardedWithGuards;
use function PHPUnit\Framework\assertContains;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotContains;
use function PHPUnit\Framework\assertTrue;

test('public attributes hydrate on model load', function () {
    FluentModel::create([
        'string' => 'value',
    ]);

    /** @var FluentModel $model */
    $model = FluentModel::first();

    assertEquals('value', $model->string);
    assertEquals($model->getAttribute('string'), $model->string);
    assertEquals('value', $model->toArray()['string']);
});

test('public property change affects model attributes', function () {
    $model = new FluentModel([
        'string' => 'one',
    ]);

    $model->string = 'two';

    assertEquals('two', $model->string);
    assertEquals('two', $model->getAttributes()['string']);
    assertEquals('two', $model->getAttribute('string'));
});

test('public property change affects dirty attributes array', function () {
    /** @var FluentModel $model */
    $model = FluentModel::create([
        'string' => 'one',
    ]);

    $model->string = 'two';

    assertEquals(['string' => 'two'], $model->getDirty());
});

test('public property change affects model changes array', function () {
    /** @var FluentModel $model */
    $model = FluentModel::create([
        'string' => 'one',
    ]);

    $model->string = 'two';
    $model->save();

    assertEquals(['string' => 'two'], $model->getChanges());
    assertTrue($model->wasChanged('string'));
});

test('model serializes to array with actual values', function () {
    $model = new FluentModel([
        'string' => 'one',
        'integer' => 1,
    ]);

    $model->string = 'two';
    $model->integer = 2;

    assertEquals('two', $model->toArray()['string']);
    assertEquals(2, $model->toArray()['integer']);
});

test('public properties populate on fill', function () {
    $model = new FluentModel([
        'string' => 'one',
    ]);

    /** @var FluentModel $model */
    $model->fill([
        'string' => 'two',
    ]);

    assertEquals('two', $model->string);
    assertEquals($model->getAttribute('string'), $model->string);
    assertEquals('two', $model->toArray()['string']);
});

test('public properties populate on update', function () {
    /** @var FluentModel $model */
    $model = FluentModel::create([
        'string' => 'one',
    ]);

    $model->update([
        'string' => 'two',
    ]);

    assertEquals('two', $model->string);
    assertEquals($model->getAttribute('string'), $model->string);
    assertEquals('two', $model->toArray()['string']);
});

test('managed public properties receive defaults', function () {
    $model = new FluentModelWithDefaults();

    // show that we can set a default via eloquent's $attributes array
    assertEquals(FluentModelWithDefaults::ALPHA_DEFAULT, $model->alpha ?? null);
    // show that we can set a default directly on the PHP property
    assertEquals(FluentModelWithDefaults::BETA_DEFAULT, $model->beta ?? null);
    // not set with a default
    assertEquals(null, $model->gamma ?? null);
});

test('managed public property defaults do not clobber model loading', function () {
    $customAttributes = [
        'alpha' => 1,
        'beta' => 2,
        'gamma' => 3
    ];

    // this is semantically identical to how eloquent models are initialized when retrieved (i.e., from a database)
    $model = (new FluentModelWithDefaults())->newFromBuilder($customAttributes);

    foreach ($customAttributes as $k => $v) {
        /*
         * for each custom attribute we set, ensure our desired value stuck on the model,
         * and that we did NOT clobber the intended value with the default value
         */
        assertEquals($v, $model->{$k});
    }
});

test('fillable model with all flags', function () {
    $model = new FluentModelAllFillableFlags();
    $fillable = $model->getFillable();

    // we passed INCLUDE_ALL flags; every property should be fillable, including id, created_at, updated_at
    assertContains('id', $fillable);
    assertContains('foo', $fillable);
    assertContains('bar', $fillable);
    assertContains('baz', $fillable);
    assertContains('created_at', $fillable);
    assertContains('updated_at', $fillable);
    assertCount(6, $fillable);

    // we didn't touch the guarded array, so it's still a catch-all "*"
    assertEquals(['*'], $model->getGuarded());
});

test('fillable model with no flags and one guarded property', function () {
    $model = new FluentModelGuardedWithGuards();
    $fillable = $model->getFillable();

    // these attributes that aren't otherwise excluded should now be fillable
    assertContains('one', $fillable);
    assertContains('two', $fillable);
    assertContains('four', $fillable);
    // with no flags passed, Fillable at the class-level will exclude primary key and Laravel-managed dates
    assertNotContains('id', $fillable);
    // we explicitly marked the property "three" as guarded
    assertNotContains('three', $fillable);
    assertCount(3, $fillable);

    // the guarded array is still untouched, as it was already ["*"]; we didn't add a redundant "three" to it
    assertEquals(['*'], $model->getGuarded());
});

test('unguarded default with explicltly guarded properties', function () {
    $model = new FluentModelUnguardedWithGuards();
    $guarded = $model->getGuarded();

    // we didn't touch the fillable array, so it's still the empty default
    assertCount(0, $model->getFillable());

    // we cleared the guarded array, but added these two properties back
    assertContains('charlie', $guarded);
    assertContains('delta', $guarded);
    assertCount(2, $guarded);
});
