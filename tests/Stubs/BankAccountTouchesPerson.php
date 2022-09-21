<?php

namespace Anfischer\Cloner\Stubs;

use Anfischer\Cloner\Stubs\Person;
use Anfischer\Cloner\Stubs\BankAccount;

class BankAccountTouchesPerson extends BankAccount
{
    protected $touches = ['person'];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
