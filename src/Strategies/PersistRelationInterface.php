<?php

namespace Anfischer\Cloner\Strategies;

use Illuminate\Database\Eloquent\Model;

interface PersistRelationInterface
{
    /**
     * @param Model $baseModel
     */
    public function __construct(Model $baseModel);

    /**
     * @param string $relationName
     * @param $models
     */
    public function persist(string $relationName, $models) : void;
}
