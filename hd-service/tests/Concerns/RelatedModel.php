<?php

namespace Tests\Concerns;

use Illuminate\Database\Eloquent\Model;

class RelatedModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'related_models';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'email' => false,
    ];
}
