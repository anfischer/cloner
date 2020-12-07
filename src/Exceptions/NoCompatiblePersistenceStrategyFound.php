<?php

namespace Anfischer\Cloner\Exceptions;

use Exception;

class NoCompatiblePersistenceStrategyFound extends Exception
{
    public static function forType(string $type): self
    {
        return new static("There are no compatable persistence strategies available for `{$type}`.");
    }
}
