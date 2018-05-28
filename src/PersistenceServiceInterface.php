<?php

namespace Anfischer\Cloner;

use Illuminate\Database\Eloquent\Model;

interface PersistenceServiceInterface
{
    /**
     * @param Model $model
     * @return Model
     */
    public function persist(Model $model) : Model;
}
