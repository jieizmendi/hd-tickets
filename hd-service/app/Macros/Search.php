<?php

namespace App\Macros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

Builder::macro('search', function ($attributes, string $searchTerm) {
    if (empty($searchTerm)) {
        return $this;
    }

    $this->where(function (Builder $query) use ($attributes, $searchTerm) {
        foreach (Arr::wrap($attributes) as $attribute) {
            $query->when(
                str_contains($attribute, '.'),
                function (Builder $query) use ($attribute, $searchTerm) {
                    $attribute = explode('.', $attribute);
                    $relationAttribute = array_pop($attribute);
                    $relationName = implode('.', $attribute);

                    $query->orWhereHas($relationName, function (Builder $query) use ($relationAttribute, $searchTerm) {
                        $query->where($relationAttribute, 'LIKE', "%{$searchTerm}%");
                    });
                },
                function (Builder $query) use ($attribute, $searchTerm) {
                    $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
                }
            );
        }
    });

    return $this;
});
