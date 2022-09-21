<?php

namespace Anfischer\Cloner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class CloneService implements CloneServiceInterface
{
    protected $originalKeyToClonedKeyMap;

    public function __construct()
    {
        $this->originalKeyToClonedKeyMap = new Collection();
    }

    /**
     * Clones a model and its relationships
     *
     * @param Model $model
     * @return Model
     */
    public function clone(Model $model) : Model
    {
        return $this->cloneRecursive(
            $this->getFreshInstance($model)
        )->first();
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

                $isCollection = $item->getRelation($relation) instanceof Collection;

                $item->setRelation(
                    $relation,
                    $isCollection ? $collection : $collection->first()
                );
            });
        });

        return $model;
    }

    /**
     * Gets a fresh cloned instance of the model
     * which is stripped of the original models unique attributes
     *
     * @param object $model
     * @param object $parent
     * @return Collection
     */
    private function getFreshInstance($model, $parent = null) : Collection
    {
        return Collection::wrap($model)->map(function ($original) use ($parent) {
            return tap(new $original, function ($instance) use ($original, $parent) {
                // Ensure we can get hold of the new ID relative to the original
                $instance->saved(function () use ($original, $instance) {
                    $this->pushToKeyMap($original, $instance);
                });

                $filter = [
                    $original->getForeignKey(),
                    $original->getKeyName(),
                    $original->getCreatedAtColumn(),
                    $original->getUpdatedAtColumn(),
                ];

                if ($parent && ! is_a($instance, Pivot::class)) {
                    array_push($filter, $parent->getForeignKey());
                }

                $attributes = Arr::except(
                    $original->getAttributes(),
                    $filter
                );

                $instance->setRawAttributes($attributes);
                $instance->setRelations($original->getRelations());

                $instance->setTouchedRelations([]);
            });
        });
    }

    /**
     * Get the key map Collection.
     *
     * @return Collection
     */
    public function getKeyMap(): Collection
    {
        return $this->originalKeyToClonedKeyMap;
    }

    /**
     * Add an old to new object key to the map.
     *
     * @param Model $original The original model.
     * @param Model $cloned The model cloned from the original.
     * @return void
     */
    public function pushToKeyMap(Model $original, Model $cloned): void
    {
        $class = get_class($original);

        $this->originalKeyToClonedKeyMap->get($class, function () use ($class) {
            return tap(new Collection, function ($collection) use ($class) {
                $this->originalKeyToClonedKeyMap->put($class, $collection);
            });
        })->put($original->getKey(), $cloned->getKey());
    }
}
