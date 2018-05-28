<?php

namespace Anfischer\Cloner\Strategies;

use Anfischer\Cloner\NullWrapper;
use Illuminate\Database\Eloquent\Model;

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
        NullWrapper::from($models)->each(function ($model) use ($relationName) {
            $this->baseModel->{$relationName}()->save($model);
        });
    }
}
