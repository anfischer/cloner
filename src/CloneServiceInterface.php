<?php

namespace Anfischer\Cloner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface CloneServiceInterface
{
    /**
     * @param   \Illuminate\Database\Eloquent\Model $model
     * @return  \Illuminate\Database\Eloquent\Model
     */
    public function clone(Model $model) : Model;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getKeyMap(): Collection;
}
