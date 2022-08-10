<?php

namespace Anfischer\Cloner\Stubs;

use Anfischer\Cloner\Stubs\Person;
use Illuminate\Database\Eloquent\Model;

class BankAccountTouchesPerson extends Model
{
    protected $table = 'bank_accounts';
    protected $guarded = [];
    protected $touches = ['person'];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function financialAdvisers()
    {
        return $this->belongsToMany(FinancialAdviser::class);
    }
}
