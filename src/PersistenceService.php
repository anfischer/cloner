<?php

namespace Anfischer\Cloner;

use Anfischer\Cloner\Exceptions\NoCompatiblePersistenceStrategyFound;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use ReflectionObject;

class PersistenceService implements PersistenceServiceInterface
{
    /**
     * Persists a model and its relationships
     *
     * @param Model $model
     * @return Model
     */
    public function persist(Model $model) : Model
    {
        $model->save();
        return $this->persistRecursive($model);
    }

    /**
     * Recursively persists a model and its relationships
     *
     * @param $parent
     * @return mixed
     */
    private function persistRecursive($parent)
    {
        Collection::wrap($parent)->each(function ($model) {
            Collection::wrap($model->getRelations())->filter(function ($relationModel) {
                return ! is_a($relationModel, Pivot::class);
            })->each(function ($relationModel, $relationName) use ($model) {
                $className = get_class((new ReflectionObject($model))->newInstance()->{$relationName}());
                $strategy = $this->getPersistenceStrategy($className);
                (new $strategy($model))->persist($relationName, $relationModel);

                $this->persistRecursive($relationModel);
            });
        });

        return $parent;
    }

    /**
     * Gets the strategy to use for persisting a relation type
     *
     * @param string $relationType
     * @return string
     */
    public function getPersistenceStrategy(string $relationType): string
    {
        $config = config('cloner.persistence_strategies');

        return collect($config)->get($relationType, function () use ($relationType) {
            throw NoCompatiblePersistenceStrategyFound::forType($relationType);
        });
    }
}
