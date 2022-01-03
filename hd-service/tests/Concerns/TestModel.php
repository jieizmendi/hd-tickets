<?php

namespace Tests\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    protected $table = 'test_models';

    public function relateds()
    {
        return $this->hasMany(RelatedModel::class);
    }

    public function scopeWhereName(Builder $query, string $name): Builder
    {
        return $query->where('name', $name);
    }
}
