<?php

namespace Anfischer\Cloner;

use Illuminate\Database\Eloquent\Model;

interface CloneServiceInterface
{
    /**
     * @param Model $model
     * @return Model
     */
    public function clone(Model $model) : Model;

    public function getKeyMap(): array;
}
