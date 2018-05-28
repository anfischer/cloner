<?php

namespace Anfischer\Cloner\Stubs;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $table = 'bank_accounts';
    protected $guarded = [];

    public function financialAdvisers()
    {
        return $this->belongsToMany(FinancialAdviser::class);
    }
}
