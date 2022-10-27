<?php

use Pest\TestSuite;
use PHPUnit\Framework\TestCase;

uses(Based\Fluent\Tests\TestCase::class)->in('Pest');

function this(): TestCase
{
    return TestSuite::getInstance()->test;
}
