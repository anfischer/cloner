<?php

namespace Anfischer\Cloner\Stubs;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = 'people';
    protected $guarded = [];

    public function socialSecurityNumber()
    {
        return $this->hasOne(SocialSecurityNumber::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function workAddresses()
    {
        return $this->belongsToMany(WorkAddress::class);
    }
}
