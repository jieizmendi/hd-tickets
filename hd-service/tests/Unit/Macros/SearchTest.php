<?php

namespace Tests\Unit\Macros;

use Tests\Concerns\TestModel;
use Tests\TestCase;

class SearchMacroTest extends TestCase
{
    protected $models;

    public function test_it_ignore_empty_search()
    {
        $this->assertEquals(
            TestModel::toSql(),
            TestModel::search(['name'], '')->toSql()
        );
    }

    public function test_search_without_fields_doesnt_affect_query()
    {
        $this->assertEquals(
            TestModel::toSql(),
            TestModel::search([], 'valid')->toSql()
        );
    }

    public function test_search()
    {
        $this->assertTrue(
            str_contains(
                TestModel::search(['name'], 'valid')->toSql(),
                '"name" LIKE ?'
            )
        );
    }

    public function test_multi_search()
    {
        $sql = TestModel::search(['name', 'email'], 'valid')->toSql();
        $case1 = '"name" LIKE ? or "email" LIKE ?';
        $case2 = '"email" LIKE ? or "name" LIKE ?';

        $this->assertTrue(
            str_contains($sql, $case1) ||
            str_contains($sql, $case2)
        );
    }

    public function test_related_search()
    {
        $sql = TestModel::search(['name', 'relateds.email'], 'valid')->toSql();
        $case1 = '("name" LIKE ? or exists (select * from "related_models" where "test_models"."id" = "related_models"."test_model_id" and "email" LIKE ?))';
        $case2 = '(exists (select * from "related_models" where "test_models"."id" = "related_models"."test_model_id" and "email" LIKE ?) or "name" LIKE ?)';

        $this->assertTrue(
            str_contains($sql, $case1) ||
            str_contains($sql, $case2)
        );
    }
}
