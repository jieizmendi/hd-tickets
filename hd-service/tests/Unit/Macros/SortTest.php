<?php

namespace Tests\Unit\Macros;

use Tests\Concerns\TestModel;
use Tests\TestCase;

class SortMacroTest extends TestCase
{
    public function test_empty_sort_doesnt_affect_query()
    {
        $this->assertEquals(
            TestModel::toSql(),
            TestModel::sort(['id', 'name'], '')->toSql()
        );
    }

    public function test_sort_with_not_allowed_field_doesnt_affect_query()
    {
        $this->assertEquals(
            TestModel::toSql(),
            TestModel::sort([], 'valid')->toSql()
        );
    }

    public function test_sort_with_not_allowed_direction_doesnt_affect_query()
    {
        $this->assertEquals(
            TestModel::toSql(),
            TestModel::sort(['name'], 'name', 'a')->toSql()
        );
    }

    public function test_sort_desc()
    {
        $this->assertTrue(
            str_contains(
                TestModel::sort(['name'], 'name')->toSql(),
                'order by "name" desc'
            )
        );
    }

    public function test_sort_asc()
    {
        $this->assertTrue(
            str_contains(
                TestModel::sort(['name'], 'name', 'asc')->toSql(),
                'order by "name" asc'
            )
        );
    }
}
