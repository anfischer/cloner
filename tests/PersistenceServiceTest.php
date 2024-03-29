<?php

namespace Anfischer\Cloner;

use Anfischer\Cloner\Exceptions\NoCompatiblePersistenceStrategyFound;
use Anfischer\Cloner\Stubs\BankAccount;
use Anfischer\Cloner\Stubs\BankAccountTouchesPerson;
use Anfischer\Cloner\Stubs\FinancialAdviser;
use Anfischer\Cloner\Stubs\Person;
use Anfischer\Cloner\Stubs\CustomPerson;
use Anfischer\Cloner\Stubs\SocialSecurityNumber;
use Anfischer\Cloner\Stubs\VerificationRule;
use Anfischer\Cloner\Stubs\WorkAddress;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PersistenceServiceTest extends TestCase
{
    /** @test */
    public function it_can_persist_a_cloned_model_with_no_relations()
    {
        $original = factory(Person::class)->create();

        $cloneService = new CloneService();
        $clone = ($cloneService)->clone($original);

        $clone = (new PersistenceService)->persist($clone);

        $this->assertEquals(
            Arr::except($original->fresh()->getAttributes(), ['id', 'created_at', 'updated_at']),
            Arr::except($clone->fresh()->getAttributes(), ['id', 'created_at', 'updated_at'])
        );

        $this->assertEquals([
            Person::class => [1 => 2],
        ], $cloneService->getKeyMap()->toArray());
    }

    /** @test */
    public function it_can_persist_a_cloned_model_with_a_belongs_to_relation()
    {
        $person = factory(Person::class)->create();
        $person->socialSecurityNumber()->save(factory(SocialSecurityNumber::class)->make());

        $original = Person::with('socialSecurityNumber')->first();
        $cloneService = new CloneService();
        $clone = ($cloneService)->clone($original);
        $clone = (new PersistenceService)->persist($clone);

        $this->assertCount(2, Person::all());
        $this->assertEquals(
            Arr::except($original->fresh()->getAttributes(), ['id', 'created_at', 'updated_at']),
            Arr::except($clone->fresh()->getAttributes(), ['id', 'created_at', 'updated_at'])
        );

        $this->assertCount(2, SocialSecurityNumber::all());
        $this->assertEquals($original->socialSecurityNumber->social_security_number, $clone->fresh()->socialSecurityNumber->social_security_number);

        $this->assertEquals([
            Person::class => [1 => 2],
            SocialSecurityNumber::class => [1 => 2]
        ], $cloneService->getKeyMap()->toArray());
    }

    /** @test */
    public function it_can_persist_a_cloned_model_with_a_has_many_relation()
    {
        $stub = factory(Person::class)->create();
        factory(BankAccount::class, 10)->make()->each(function ($account) use ($stub) {
            $stub->bankAccounts()->save($account);
        });

        $original = Person::with('bankAccounts')->first();
        $cloneService = new CloneService();
        $clone = ($cloneService)->clone($original);
        $clone = (new PersistenceService)->persist($clone);

        $this->assertCount(2, Person::all());
        $this->assertEquals(
            Arr::except($original->fresh()->getAttributes(), ['id', 'created_at', 'updated_at']),
            Arr::except($clone->fresh()->getAttributes(), ['id', 'created_at', 'updated_at'])
        );

        $this->assertCount(20, BankAccount::all());
        $this->assertCount(10, $clone->fresh()->bankAccounts);
        $clone->fresh()->bankAccounts->each(function ($item, $key) use ($original) {
            $this->assertEquals($original->bankAccounts[$key]->only(['account_number', 'account_name']), $item->only(['account_number', 'account_name']));
        });

        $this->assertEquals([
            Person::class => [1 => 2],
            BankAccount::class => [
                1 => 11,
                2 => 12,
                3 => 13,
                4 => 14,
                5 => 15,
                6 => 16,
                7 => 17,
                8 => 18,
                9 => 19,
                10 => 20,
            ]
        ], $cloneService->getKeyMap()->toArray());
    }

    /**
     * @group failing
     * @test
     */
    public function it_can_persist_a_cloned_model_with_a_has_many_relation_and_structure_the_cloned_relations_as_a_collection()
    {
        $stub = factory(Person::class)->create();

        factory(BankAccount::class, 1)->make()->each(function ($account) use ($stub) {
            $stub->bankAccounts()->save($account);
        });

        $original = Person::with('bankAccounts')->first();

        $clone = (new CloneService)->clone($original);
        $clone = (new PersistenceService)->persist($clone);

        $this->assertInstanceOf(Collection::class, $clone->bankAccounts);
        $this->assertCount(1, $clone->bankAccounts);

        // Just ensure that its not the original collection, i.e. account id 1
        $this->assertEquals(2, $clone->bankAccounts[0]->id);
    }

    /** @test */
    public function it_can_persist_a_cloned_model_with_a_many_to_many_relation()
    {
        $stub = factory(Person::class)->create();
        factory(WorkAddress::class, 10)->create()->each(function ($relation, $key) use ($stub) {
            $stub->workAddresses()->attach([$relation->id => ['pivot_data' => 'Test ' . $key]]);
        });

        $original = Person::with(['workAddresses' => function ($relation) {
            $relation->withPivot('pivot_data');
        }])->first();

        $cloneService = new CloneService();
        $clone = ($cloneService)->clone($original);
        $clone = (new PersistenceService)->persist($clone);

        $this->assertCount(2, Person::all());
        $this->assertEquals(
            Arr::except($original->fresh()->getAttributes(), ['id', 'created_at', 'updated_at']),
            Arr::except($clone->fresh()->getAttributes(), ['id', 'created_at', 'updated_at'])
        );

        $this->assertCount(10, WorkAddress::all());
        $this->assertCount(10, $clone->fresh()->workAddresses);

        $this->assertCount(20, DB::table('person_work_address')->get());

        $clone->fresh()->workAddresses->each(function ($item, $key) use ($original) {
            $this->assertEquals($original->workAddresses[$key]->only(['address', 'postcode']), $item->only(['address', 'postcode']));
            $this->assertEquals($original->workAddresses[$key]->pivot->work_address_id, $item->pivot->work_address_id);
        });

        $this->assertEquals([
            Person::class => [1 => 2],
        ], $cloneService->getKeyMap()->toArray());
    }

    /** @test */
    public function it_can_clone_a_model_with_a_has_one_relation_with_a_has_many_relation()
    {
        $parent = factory(Person::class)->create();
        $parent->socialSecurityNumber()->save(factory(SocialSecurityNumber::class)->make());

        $socialSecurityNumber = SocialSecurityNumber::first();
        factory(VerificationRule::class, 10)->make()->each(function ($relation) use ($socialSecurityNumber) {
            $socialSecurityNumber->verificationRules()->save($relation);
        });

        $original = Person::with('socialSecurityNumber.verificationRules')->first();

        $cloneService = new CloneService();
        $clone = ($cloneService)->clone($original);
        $clone = (new PersistenceService)->persist($clone);

        $this->assertCount(2, Person::all());
        $this->assertEquals(
            Arr::except($original->fresh()->getAttributes(), ['id', 'created_at', 'updated_at']),
            Arr::except($clone->fresh()->getAttributes(), ['id', 'created_at', 'updated_at'])
        );

        $this->assertCount(2, SocialSecurityNumber::all());
        $this->assertEquals($original->socialSecurityNumber->social_security_number, $clone->fresh()->socialSecurityNumber->social_security_number);

        $this->assertCount(10, $clone->fresh()->socialSecurityNumber->verificationRules);
        $clone->fresh()->socialSecurityNumber->verificationRules->each(function ($item, $key) use ($original) {
            $this->assertEquals($original->socialSecurityNumber->verificationRules[$key]->rule, $item->rule);
        });

        $this->assertEquals([
            Person::class => [1 => 2],
            SocialSecurityNumber::class => [1 => 2],
            VerificationRule::class => [
                1 => 11,
                2 => 12,
                3 => 13,
                4 => 14,
                5 => 15,
                6 => 16,
                7 => 17,
                8 => 18,
                9 => 19,
                10 => 20,
            ]
        ], $cloneService->getKeyMap()->toArray());
    }

    /** @test */
    public function it_can_persist_a_cloned_model_with_a_has_many_relations_with_a_belongs_to_many_relation()
    {
        $parent = factory(Person::class)->create();
        factory(BankAccount::class, 5)->make()->each(function ($account) use ($parent) {
            $parent->bankAccounts()->save($account);
        });

        $bankAccounts = BankAccount::all();
        $bankAccounts->each(function ($account) {
            factory(FinancialAdviser::class, 2)->create()->each(function ($relation) use ($account) {
                $account->financialAdvisers()->attach($relation);
            });
        });

        $original = Person::with('bankAccounts.financialAdvisers')->first();

        $cloneService = new CloneService();
        $clone = ($cloneService)->clone($original);
        $clone = (new PersistenceService)->persist($clone);

        $this->assertCount(2, Person::all());
        $this->assertEquals(
            Arr::except($original->fresh()->getAttributes(), ['id', 'created_at', 'updated_at']),
            Arr::except($clone->fresh()->getAttributes(), ['id', 'created_at', 'updated_at'])
        );

        $this->assertCount(10, BankAccount::all());
        $clone->fresh()->bankAccounts->each(function ($item, $key) use ($original) {
            $this->assertEquals(
                Arr::except($original->bankAccounts[$key]->getAttributes(), ['id', 'person_id', 'created_at', 'updated_at']),
                Arr::except($item->getAttributes(), ['id', 'person_id', 'created_at', 'updated_at'])
            );
        });

        $clone->fresh()->bankAccounts->each(function ($account, $key) use ($original) {
            $this->assertCount($original->bankAccounts[$key]->financialAdvisers()->count(), $account->financialAdvisers);
        });

        $clone->fresh()->bankAccounts->each(function ($account, $accountKey) use ($original) {
            $account->financialAdvisers->each(function ($adviser, $adviserKey) use ($original, $accountKey) {
                $this->assertEquals(
                    Arr::except($original->fresh()->bankAccounts[$accountKey]->financialAdvisers[$adviserKey]->getAttributes(), ['id', 'created_at', 'updated_at']),
                    Arr::except($adviser->getAttributes(), ['id', 'created_at', 'updated_at'])
                );
            });
        });

        $this->assertEquals([
            Person::class => [1 => 2],
            BankAccount::class => [
                1 => 6,
                2 => 7,
                3 => 8,
                4 => 9,
                5 => 10,
            ]
        ], $cloneService->getKeyMap()->toArray());
    }

    /** @test */
    public function it_throws_when_attempting_to_clone_an_unknown_relationship_type()
    {
        $person = factory(CustomPerson::class)->create();

        $person->socialSecurityNumber()->save(factory(SocialSecurityNumber::class)->make());

        $this->expectException(NoCompatiblePersistenceStrategyFound::class);
        $this->expectExceptionMessage(
            'There are no compatable persistence strategies available for `Anfischer\Cloner\Stubs\CustomHasOne`.'
        );

        try {
            $original = CustomPerson::with('socialSecurityNumber')->first();
            $clone = (new CloneService)->clone($original);
            $clone = (new PersistenceService)->persist($clone);
        } finally {
            $this->assertCount(2, CustomPerson::all());
            $this->assertCount(1, SocialSecurityNumber::all());
        }
    }

    /** @test */
    public function it_can_use_additonal_configured_persistence_strategies()
    {
        $person = factory(CustomPerson::class)->create();

        $person->socialSecurityNumber()->save(factory(SocialSecurityNumber::class)->make());

        config(['cloner.persistence_strategies' => [
            \Anfischer\Cloner\Stubs\CustomHasOne::class =>
                \Anfischer\Cloner\Strategies\PersistHasOneRelationStrategy::class
        ]]);

        $original = CustomPerson::with('socialSecurityNumber')->first();
        $clone = (new CloneService)->clone($original);
        $clone = (new PersistenceService)->persist($clone);

        $this->assertCount(2, CustomPerson::all());
        $this->assertEquals(
            Arr::except($original->fresh()->getAttributes(), ['id', 'created_at', 'updated_at']),
            Arr::except($clone->fresh()->getAttributes(), ['id', 'created_at', 'updated_at'])
        );

        $this->assertCount(2, SocialSecurityNumber::all());
        $this->assertEquals(
            $original->socialSecurityNumber->social_security_number,
            $clone->fresh()->socialSecurityNumber->social_security_number
        );
    }

    /** @test */
    public function it_can_handle_touch_of_parent_models_without_accidentally_loading_it()
    {
        $person = factory(Person::class)->create();

        factory(BankAccountTouchesPerson::class, 1)->make()->each(function ($account) use ($person) {
            $person->bankAccounts()->save($account);
        });

        // intentionally without Person relationship loaded
        $original = BankAccountTouchesPerson::first();

        $cloneService = new CloneService();
        $clone = ($cloneService)->clone($original);

        $clone = (new PersistenceService)->persist($clone);

        $this->assertCount(1, Person::all());
        $this->assertCount(2, BankAccountTouchesPerson::all());

        $this->assertEquals(
            Arr::except($original->fresh()->getAttributes(), ['id', 'created_at', 'updated_at']),
            Arr::except($clone->fresh()->getAttributes(), ['id', 'created_at', 'updated_at'])
        );
    }
}
