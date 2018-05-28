<?php

namespace Anfischer\Cloner;

use Anfischer\Cloner\Stubs\BankAccount;
use Anfischer\Cloner\Stubs\FinancialAdviser;
use Anfischer\Cloner\Stubs\Person;
use Anfischer\Cloner\Stubs\SocialSecurityNumber;
use Anfischer\Cloner\Stubs\VerificationRule;
use Anfischer\Cloner\Stubs\WorkAddress;

class CloneServiceTest extends TestCase
{
    /** @test */
    public function it_can_clone_a_model_with_no_relations()
    {
        $original = factory(Person::class)->make();
        $clone = (new CloneService())->clone($original);

        $this->assertEquals($original, $clone);
    }

    /** @test */
    public function it_can_clone_a_model_with_a_belongs_to_relation()
    {
        $person = factory(Person::class)->create();
        $person->socialSecurityNumber()->save(factory(SocialSecurityNumber::class)->make());

        $original = Person::with('socialSecurityNumber')->first();
        $clone = (new CloneService())->clone($original);

        $this->assertEquals(
            $original->only([
                'first_name',
                'last_name',
                'email',
                'phone',
                'gender',
            ]),
            $clone->getAttributes()
        );

        $this->assertEquals($original->socialSecurityNumber->only('social_security_number'), $clone->socialSecurityNumber->getAttributes());
    }

    /** @test */
    public function it_can_clone_a_model_with_a_has_many_relation()
    {
        $stub = factory(Person::class)->create();
        factory(BankAccount::class, 10)->make()->each(function ($account) use ($stub) {
            $stub->bankAccounts()->save($account);
        });

        $original = Person::with('bankAccounts')->first();
        $clone = (new CloneService())->clone($original);

        $this->assertEquals(
            $original->only([
                'first_name',
                'last_name',
                'email',
                'phone',
                'gender',
            ]),
            $clone->getAttributes()
        );

        $this->assertCount(10, $clone->bankAccounts);
        $clone->bankAccounts->each(function ($item, $key) use ($original, $clone) {
            $this->assertEquals($original->bankAccounts[$key]->only(['account_number', 'account_name']), $item->getAttributes());
        });
    }

    /** @test */
    public function it_can_clone_a_model_with_a_many_to_many_relation()
    {
        $stub = factory(Person::class)->create();
        factory(WorkAddress::class, 10)->create()->each(function ($relation, $key) use ($stub) {
            $stub->workAddresses()->attach([$relation->id => ['pivot_data' => 'Test ' . $key]]);
        });

        $original = Person::with(['workAddresses' => function ($relation) {
            $relation->withPivot('pivot_data');
        }])->first();
        $clone = (new CloneService())->clone($original);

        $this->assertEquals(
            $original->only([
                'first_name',
                'last_name',
                'email',
                'phone',
                'gender',
            ]),
            $clone->getAttributes()
        );

        $this->assertCount(10, $clone->workAddresses);
        $clone->workAddresses->each(function ($item, $key) use ($original, $clone) {
            $this->assertEquals($original->workAddresses[$key]->only(['address', 'postcode']), $item->getAttributes());
            $this->assertEquals($original->workAddresses[$key]->pivot->only('pivot_data', 'work_address_id'), $item->pivot->getAttributes());
        });
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
        $clone = (new CloneService())->clone($original);

        $this->assertEquals(
            $original->only([
                'first_name',
                'last_name',
                'email',
                'phone',
                'gender',
            ]),
            $clone->getAttributes()
        );

        $this->assertEquals($original->socialSecurityNumber->only('social_security_number'), $clone->socialSecurityNumber->getAttributes());
        $this->assertCount(10, $clone->socialSecurityNumber->verificationRules);

        $clone->socialSecurityNumber->verificationRules->each(function ($item, $key) use ($original, $clone) {
            $this->assertEquals($original->socialSecurityNumber->verificationRules[$key]->only('rule'), $item->getAttributes());
        });
    }

    /** @test */
    public function it_can_clone_a_model_with_a_has_many_relations_with_a_belongs_to_many_relation()
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
        $clone = (new CloneService())->clone($original);

        $this->assertEquals(
            $original->only([
                'first_name',
                'last_name',
                'email',
                'phone',
                'gender',
            ]),
            $clone->getAttributes()
        );

        $this->assertCount($original->bankAccounts->count(), $clone->bankAccounts);
        $clone->bankAccounts->each(function ($account, $key) use ($original) {
            $this->assertEquals(
                $original->bankAccounts[$key]->only(['account_number', 'account_name']),
                $account->getAttributes()
            );
        });

        $clone->bankAccounts->each(function ($account, $accountKey) use ($original) {
            $account->financialAdvisers->each(function ($adviser, $adviserKey) use ($original, $accountKey) {
                $this->assertEquals(
                    $original->bankAccounts[$accountKey]->financialAdvisers[$adviserKey]->only(['first_name', 'last_name', 'email']),
                    $adviser->getAttributes()
                );
            });
        });
    }
}
