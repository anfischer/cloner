<?php

namespace Anfischer\Cloner;

use Anfischer\Cloner\Facades\Cloner;
use Anfischer\Cloner\Stubs\BankAccount;
use Anfischer\Cloner\Stubs\FinancialAdviser;
use Anfischer\Cloner\Stubs\Person;
use Anfischer\Cloner\Stubs\SocialSecurityNumber;
use Anfischer\Cloner\Stubs\VerificationRule;
use Anfischer\Cloner\Stubs\WorkAddress;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

class TestCase extends OrchestraTestCase
{
    public function setUp() : void
    {
        parent::setUp();
        $this->defineFactories();
        $this->migrateTables();
    }

    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ClonerServiceProvider::class
        ];
    }

    /**
     * Load package alias
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Cloner' => Cloner::class,
        ];
    }

    /**
     * Define factories used during the test
     */
    private function defineFactories(): void
    {
        $factory = app(EloquentFactory::class);

        $factory->define(Person::class, function (Generator $faker) {
            return [
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->email,
                'phone' => $faker->numberBetween(60000000, 90000000),
                'gender' => $faker->boolean,
            ];
        });

        $factory->define(SocialSecurityNumber::class, function (Generator $faker) {
            return [
                'social_security_number' => $faker->randomNumber(8),
            ];
        });

        $factory->define(BankAccount::class, function (Generator $faker) {
            return [
                'account_number' => $faker->randomNumber(),
                'account_name' => $faker->text(10),
            ];
        });

        $factory->define(WorkAddress::class, function (Generator $faker) {
            return [
                'address' => $faker->streetAddress(),
                'postcode' => $faker->postcode(),
            ];
        });

        $factory->define(VerificationRule::class, function (Generator $faker) {
            return [
                'rule' => $faker->text(),
            ];
        });

        $factory->define(FinancialAdviser::class, function (Generator $faker) {
            return [
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->email,
            ];
        });
    }

    private function migrateTables() : void
    {
        DB::getSchemaBuilder()->create('people', function ($table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->integer('phone');
            $table->boolean('gender');
            $table->timestamps();
        });

        DB::getSchemaBuilder()->create('social_security_numbers', function ($table) {
            $table->increments('id');
            $table->integer('person_id')->references('id')->on('people');
            $table->string('social_security_number');
            $table->timestamps();
        });

        DB::getSchemaBuilder()->create('bank_accounts', function ($table) {
            $table->increments('id');
            $table->integer('person_id')->references('id')->on('people');
            $table->integer('account_number');
            $table->string('account_name');
            $table->timestamps();
        });

        DB::getSchemaBuilder()->create('work_addresses', function ($table) {
            $table->increments('id');
            $table->integer('person_id')->references('id')->on('people')->nullable();
            $table->string('address');
            $table->integer('postcode');
            $table->timestamps();
        });

        DB::getSchemaBuilder()->create('person_work_address', function ($table) {
            $table->integer('person_id')->references('id')->on('people');
            $table->integer('work_address_id')->references('id')->on('work_addresses');
            $table->string('pivot_data')->nullable();
        });

        DB::getSchemaBuilder()->create('verification_rules', function ($table) {
            $table->increments('id');
            $table->integer('social_security_number_id')->references('id')->on('social_security_numbers')->nullable();
            $table->string('rule');
            $table->timestamps();
        });

        DB::getSchemaBuilder()->create('financial_advisers', function ($table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->timestamps();
        });

        DB::getSchemaBuilder()->create('bank_account_financial_adviser', function ($table) {
            $table->integer('bank_account_id')->references('id')->on('bank_accounts');
            $table->integer('financial_adviser_id')->references('id')->on('financial_advisers');
            $table->string('pivot_data')->nullable();
        });
    }
}
