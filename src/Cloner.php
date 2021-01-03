<?php

namespace Anfischer\Cloner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Cloner
{
    private $cloneService;
    private $persistenceService;

    /**
     * @param CloneServiceInterface $cloneService
     * @param PersistenceServiceInterface $persistenceService
     */
    public function __construct(CloneServiceInterface $cloneService, PersistenceServiceInterface $persistenceService)
    {
        $this->cloneService = $cloneService;
        $this->persistenceService = $persistenceService;
    }

    /**
     * Clone a model without persisting it
     *
     * @param $model
     * @return Model
     */
    public function clone($model): Model
    {
        return $this->cloneService->clone($model);
    }

    /**
     * Persist a cloned model
     *
     * @param $model
     * @return Model
     */
    public function persist($model): Model
    {
        return $this->persistenceService->persist($model);
    }

    /**
     * Clone and persist a model
     *
     * @param $model
     * @return Model
     */
    public function cloneAndPersist($model) : Model
    {
        return $this->persist($this->clone($model));
    }

    /**
     * Retrieve the map of original keys to cloned keys
     *
     * @return Collection
     */
    public function getKeyMap(): Collection
    {
        return $this->cloneService->getKeyMap();
    }
}
