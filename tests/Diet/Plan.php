<?php

namespace Anfischer\Cloner\Diet;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'diet_plans';
    protected $dates = ['date_start', 'date_end'];

    public function meals()
    {
        return $this->hasMany(Meal::class);
    }

    public function createFromArray(array $data)
    {
        $this->name         = $data['name'];
        $this->owner_id     = $data['owner_id'];
        $this->client_id    = $data['client_id'];
        $this->date_start   = $data['date_start'];
        $this->date_end     = $data['date_end'];
        $this->published_at = null;

        $this->save();
        $this->addMeals($data['meals']);

        return $this;
    }

    public function publish()
    {
        $this->published_at = $this->freshTimestamp();
        $this->save();
    }

    public function unpublish()
    {
        $this->published_at = null;
        $this->save();
    }

    public function updateFromArray(array $data)
    {
        $this->name       = $data['name'];
        $this->client_id  = $data['client_id'];
        $this->date_start = $data['date_start'];
        $this->date_end   = $data['date_end'];

        $this->save();
        $this->removeMeals($data['meals']);
        $this->updateMeals($data['meals']);

        return $this;
    }

    private function addMeals(array $meals)
    {
        foreach ($meals as $meal) {
            $this->meals()->create(['name' => $meal['name'], 'owner_id' => $this->owner_id]);
        }
    }

    private function updateMeals(array $meals)
    {
        foreach ($meals as $meal) {
            if (isset($meal['id']) && ! empty($meal['id'])) {
                $this->meals()->find($meal['id'])->update(['name' => $meal['name']]);
            } else {
                $this->addMeals([$meal]);
            }
        }
    }

    private function removeMeals(array $meals)
    {
        $mealsToRemove = $this->meals()->pluck('id')->diff(collect($meals)->pluck('id'));

        if ($mealsToRemove->count()) {
            $this->meals()->whereIn('id', $mealsToRemove)->delete();
        }
    }
}
