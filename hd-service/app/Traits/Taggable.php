<?php

namespace App\Traits;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;

trait Taggable
{
    /**
     * Get the tags.
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Tag this model.
     *
     * @param   \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|array    $ids
     */
    public function tag($ids): void
    {
        $this->tags()->syncWithoutDetaching($ids);

        activity('tag')
            ->performedOn($this)
            ->withProperties(['tags' => $ids])
            ->log("synced");
    }

    /**
     * Untag this model.
     *
     * @param   \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|array    $ids
     */
    public function untag($ids): void
    {
        $this->tags()->detach($ids);

        activity('tag')
            ->performedOn($this)
            ->withProperties(['tags' => $ids])
            ->log("detached");
    }

    /**
     * Filter model to subset with any of the given tags.
     *
     * @param   int|array   $id
     */
    public function scopeWithAnyTag(Builder $query, $id): Builder
    {
        return $query->whereHas('tags',
            function ($query) use ($id) {
                $query->whereIn('id', Arr::wrap($id));
            }
        );
    }

    /**
     * Filter model to subset with the given tags
     *
     * @param   int|array   $id
     */
    public function scopeWithAllTag(Builder $query, $id): Builder
    {
        foreach (Arr::wrap($id) as $tag_id) {
            $query = $query->whereHas('tags',
                function (Builder $query) use ($tag_id) {
                    $query->where('id', $tag_id);
                }
            );
        }

        return $query;
    }
}
