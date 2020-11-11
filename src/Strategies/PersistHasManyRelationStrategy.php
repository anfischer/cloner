<?php

namespace Anfischer\Cloner\Strategies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PersistHasManyRelationStrategy implements PersistRelationInterface
{
    private $baseModel;

    /**
     * @param Model $baseModel
     */
    public function __construct(Model $baseModel)
    {
        $this->baseModel = $baseModel;
    }

    /**
     * @param string $relationName
     * @param $models
     */
    public function persist(string $relationName, $models) : void
    {
        Collection::wrap($models)->each(function ($model) use ($relationName) {
            $this->baseModel->{$relationName}()->save($model);
        });
    }
}
