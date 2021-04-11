<?php

namespace Anfischer\Cloner\Stubs;

use Anfischer\Cloner\Stubs\CustomHasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CustomPerson extends Model
{
    protected $table = 'people';
    protected $guarded = [];

    /**
     * Instantiate a new HasOne relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    protected function newHasOne(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        return new CustomHasOne($query, $parent, $foreignKey, $localKey);
    }

    public function socialSecurityNumber()
    {
        return $this->hasOne(SocialSecurityNumber::class, 'person_id');
    }
}
