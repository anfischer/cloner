<?php

namespace Anfischer\Cloner;

use Illuminate\Database\Eloquent\Collection;

class NullWrapper
{
    /**
     * Since we only have null checks in Collection::wrap after
     * illuminate/support v5.6.23 we wrap null-values our self
     * for extended backward compatibility support
     *
     * @param $value
     * @return Collection
     */
    public static function from($value) : Collection
    {
        return Collection::wrap($value ?? []);
    }
}
