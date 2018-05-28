<?php

namespace Anfischer\Cloner\Facades;

use Anfischer\Cloner\Cloner as ClonerBase;
use Illuminate\Support\Facades\Facade;

class Cloner extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ClonerBase::class;
    }
}
