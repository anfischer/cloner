<?php

namespace Anfischer\Cloner\Stubs;

use Illuminate\Database\Eloquent\Model;

class SocialSecurityNumber extends Model
{
    protected $table = 'social_security_numbers';
    protected $guarded = [];

    public function verificationRules()
    {
        return $this->hasMany(VerificationRule::class);
    }
}
