<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use  RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // أي إعدادات خاصة قبل كل اختبار
        // مثل التأكد من استخدام Sanctum
    }
}
