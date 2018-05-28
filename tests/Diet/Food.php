<?php

namespace Anfischer\Cloner\Diet;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{

    protected $table = 'diet_foods';

    public function meals()
    {
        return $this->belongsToMany(Meal::class, 'diet_food_meal');
    }

    public function associatedDietPlans()
    {
        return $this->meals->map(function ($meal) {
            return $meal->plan;
        });
    }

    public function isAssociatedToDiet()
    {
        return $this->meals()->count() > 0;
    }

    public function searchableAs()
    {
        return 'dev_foods_index';
    }

    public function toSearchableArray()
    {
        return [
            'name'          => $this->name,
            'owner_id'      => $this->type === 'predefined' ? 0 : $this->owner_id,
            'measure'       => $this->measure,
            'kilo_calories' => (int) $this->kilo_calories,
            'protein'       => (float) $this->protein,
            'carbohydrates' => (float) $this->carbohydrates,
            'fat'           => (float) $this->fat,
        ];
    }

    public function toArray()
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'measure'       => $this->measure,
            'kilo_calories' => (int) $this->kilo_calories,
            'protein'       => (float) $this->protein,
            'carbohydrates' => (float) $this->carbohydrates,
            'fat'           => (float) $this->fat,
        ];
    }
}
