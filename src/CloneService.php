<?php

namespace Anfischer\Cloner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CloneService implements CloneServiceInterface
{
    /**
     * Clones a model and its relationships
     *
     * @param Model $model
     * @return Model
     */
    public function clone(Model $model) : Model
    {
        return $this->cloneRecursive($model->replicate());
    }

    /**
     * Recursively clones a model and its relationships
     *
     * @param $model
     * @return mixed
     */
    private function cloneRecursive($model)
    {
        Collection::wrap($model)->each(function ($item) {
            Collection::wrap($item->getRelations())->each(function ($method, $relation) use ($item) {
                $collection = $this->getFreshInstance($this->cloneRecursive($method), $item);

                $item->setRelation(
                    $relation,
                    $this->getItemOrCollection($collection)
                );
            });
        });

        return $model;
    }

    /**
     * Gets the first item of the collection if the collection
     * only contains one item, otherwise it returns the collection
     *
     * @param Collection $collection
     * @return Collection|mixed
     */
    private function getItemOrCollection(Collection $collection)
    {
        return $collection->count() > 1 ? $collection : $collection->first();
    }

    /**
     * Gets a fresh cloned instance of the model
     * which is stripped of the original models unique attributes
     *
     * @param object $model
     * @param object $parent
     * @return Collection
     */
    private function getFreshInstance($model, $parent) : Collection
    {
        return Collection::wrap($model)->map(function ($original) use ($parent) {
            return tap(new $original, function ($instance) use ($original, $parent) {
                $filter = [
                    $original->getForeignKey(),
                    $original->getKeyName(),
                    $original->getCreatedAtColumn(),
                    $original->getUpdatedAtColumn(),
                ];

                if (! is_a($instance, Pivot::class)) {
                    array_push($filter, $parent->getForeignKey());
                }

                $attributes = Arr::except(
                    $original->getAttributes(),
                    $filter
                );

                $instance->setRawAttributes($attributes);
                $instance->setRelations($original->getRelations());
            });
        });
    }
}
