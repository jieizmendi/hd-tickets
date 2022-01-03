<?php

namespace App\Macros;

use Illuminate\Database\Eloquent\Builder;

Builder::macro('searchFromQuery', function ($attributes) {
    $this->search($attributes, request('search', ''));

    return $this;
});
