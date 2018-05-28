<?php

namespace Anfischer\Cloner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
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
        NullWrapper::from($parent)->each(function ($model) {
            NullWrapper::from($model->getRelations())->filter(function ($relationModel) {
                return ! is_a($relationModel, Pivot::class);
            })->each(function ($relationModel, $relationName) use ($model) {
                $className = \get_class((new ReflectionObject($model))->newInstance()->{$relationName}());
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
        $strategy = substr(strrchr($relationType, '\\'), 1);
        return __NAMESPACE__ . '\Strategies\Persist' . $strategy . 'RelationStrategy';
    }
}
