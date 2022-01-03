<?php

namespace App\Macros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

Builder::macro('sort', function ($allowedSorts, string $field, string $direction = 'desc') {
    if (empty($field) || empty($direction)) {
        return $this;
    }

    if (
        !in_array($field, Arr::wrap($allowedSorts)) ||
        !in_array($direction, ['desc', 'asc'])
    ) {
        return $this;
    }

    return $this->orderBy($field, $direction);
});
