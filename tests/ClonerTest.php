<?php

namespace Anfischer\Cloner;

use Illuminate\Database\Eloquent\Model;
use Mockery;

class ClonerTest extends TestCase
{
    /** @test */
    public function it_calls_cloner_service_when_clone_method_is_called()
    {
        $model = Mockery::mock(Model::class);

        $cloneService = Mockery::mock(CloneServiceInterface::class);
        $cloneService->shouldReceive('clone')->once()->getMock();

        $persistenceService = Mockery::mock(PersistenceServiceInterface::class);
        $persistenceService->shouldNotHaveReceived('persist');

        (new Cloner($cloneService, $persistenceService))->clone($model);
    }

    /** @test */
    public function it_exposes_a_method_to_get_the_key_map_from_private_cloneservice()
    {
        $model = Mockery::mock(Model::class);

        $cloneService = Mockery::mock(CloneServiceInterface::class);
        $cloneService->shouldReceive('getKeyMap')->once();

        $persistenceService = Mockery::mock(PersistenceServiceInterface::class);

        (new Cloner($cloneService, $persistenceService))->getKeyMap();
    }

    /** @test */
    public function it_calls_persistence_service_when_clone_method_is_called()
    {
        $model = Mockery::mock(Model::class);

        $cloneService = Mockery::mock(CloneServiceInterface::class);
        $cloneService->shouldNotHaveReceived('clone');

        $persistenceService = Mockery::mock(PersistenceServiceInterface::class);
        $persistenceService->shouldReceive('persist')->once()->getMock();

        (new Cloner($cloneService, $persistenceService))->persist($model);
    }

    /** @test */
    public function it_both_call_clone_service_and_persistence_service_when_clone_and_persist_method_is_called()
    {
        $model = Mockery::mock(Model::class);

        $cloneService = Mockery::mock(CloneServiceInterface::class);
        $cloneService->shouldReceive('clone')->once()->getMock();

        $persistenceService = Mockery::mock(PersistenceServiceInterface::class);
        $persistenceService->shouldReceive('persist')->once()->getMock();

        (new Cloner($cloneService, $persistenceService))->cloneAndPersist($model);
    }

    /** @test */
    public function it_registerers_a_cloner_facade_in_the_container()
    {
        $this->assertInstanceOf(Cloner::class, $this->app['cloner']);
        $this->assertTrue(method_exists(\Cloner::getFacadeRoot(), 'clone'));
        $this->assertTrue(method_exists(\Cloner::getFacadeRoot(), 'persist'));
    }
}
