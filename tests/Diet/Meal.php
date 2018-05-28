<?php

namespace Anfischer\Cloner\Diet;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{

    protected $table = 'diet_meals';

    public function gg()
    {
        var_dump($this->guessBelongsToRelation());
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function foods()
    {
        return $this->belongsToMany(Food::class, 'diet_food_meal')
            ->withPivot(['order', 'amount'])
            ->orderBy('pivot_order', 'asc');
    }
}
