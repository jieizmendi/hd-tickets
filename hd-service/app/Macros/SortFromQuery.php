<?php

namespace App\Macros;

use Illuminate\Database\Eloquent\Builder;

Builder::macro('sortFromQuery', function ($attributes) {
    $sortBy = request('sortBy', '');
    if (is_array($sortBy)) {
        $sortBy = head($sortBy);
    }

    $sortDesc = request('sortDesc', '');
    if (is_array($sortDesc)) {
        $sortDesc = head($sortDesc);
    }

    $this->sort($attributes, $sortBy, $sortDesc == 'false' ? 'asc' : 'desc');

    return $this;
});
