# Recursive cloning and persistence of Laravel Eloquent models

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

A package which allows for easy recursive cloning and persistence of Laravel Eloquent models, including:
- Recursive cloning of Eloquent models and their relationships without forced persistence, allowing for in-memory changes to cloned models before they are saved to the database
- Persistence of recursive relationships including cloned pivot data

_Since this is a feature I commonly rely on in client projects, I decided to extract the functionality into a package.
However this also has the consequence that your mileage may vary, and hence pull requests are welcome - please see [CONTRIBUTING](CONTRIBUTING.md) for details._


## Structure

```
src/
tests/
vendor/
```

## Version Compatibility

 Laravel  | Cloner    | PHP
:---------|:----------|:---------
 5.4.x    | 0.1.0     | ^5.4
 6.x      | 0.2.0     | ^7.3
 7.x      | 0.2.0     | ^7.3
 8.x      | 0.2.0     | ^7.3
 9.x      | 0.4.0     | ^8.0.2

## Install

Via Composer

``` bash
$ composer require anfischer/cloner
```

The package will automatically register its service provider.

## Usage

### Basic Usage

``` php
use Anfischer\Cloner;

$clone = (new CloneService)->clone($someEloquentModel);
$persistedModel = (new PersistenceService)->persist($clone);

or

$cloner = new Cloner(new CloneService, new PersistenceService);
$clone = $cloner->clone($someEloquentModel);
$persistedModel = $cloner->persist($clone);

or

$clone = \Cloner::clone($someEloquentModel);
$persistedModel = \Cloner::persist($clone);
```

### Convenience Methods

Cloner also exposes a convinience method for cloning and persisting at the same time:

``` php
$cloner = new Cloner(new CloneService, new PersistenceService);
$persistedModel = $cloner->cloneAndPersist($someEloquentModel);
```

### Cloned Model Map

You may wish to keep track of which models were cloned and the keys of their
respective clones. In order to do this Cloner keeps a record of these keys.

``` php
$cloneService = new CloneService()

// $personModel->id === 1;
// gettype($personModel) === App\Person;

$clone = ($cloneService)->clone($personModel);

$persistedModel = (new PersistenceService)->persist($clone);
// or
$persistedModel = $clone->save();

// $persistedModel->id === 2

$map = $cloneService->getKeyMap();

// $map === [App\Person => [1 => 2]];
```

## Configuration

To publish the config file to config/cloner.php run:

```
php artisan vendor:publish --provider="Anfischer\Cloner\ClonerServiceProvider"
```

Cloner supports various persistence strategies by default. These can be configured
by modifying the configuration in `config/cloner.php`.

For example

```
return [

    'persistence_strategies' => [
        Illuminate\Database\Eloquent\Relations\HasOne::class =>
            Anfischer\Cloner\Strategies\PersistHasOneRelationStrategy::class,
        Illuminate\Database\Eloquent\Relations\HasMany::class =>
            Anfischer\Cloner\Strategies\PersistHasManyRelationStrategy::class,
        Illuminate\Database\Eloquent\Relations\BelongsToMany::class =>
            Anfischer\Cloner\Strategies\PersistBelongsToManyRelationStrategy::class,

        // You can add your own strategies for relations
        SomePackage\Relations\CustomRelation =>
            App\Cloner\PersistenceStrategies\PersistSomePackageCustomRelationStrategy
    ]
];
```


## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email kontakt@season.dk instead of using the issue tracker.

## Credits

- [Andreas Fischer][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/anfischer/cloner.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/anfischer/cloner/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/anfischer/cloner.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/anfischer/cloner.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/anfischer/cloner.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/anfischer/cloner
[link-travis]: https://travis-ci.org/anfischer/cloner
[link-scrutinizer]: https://scrutinizer-ci.com/g/anfischer/cloner/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/anfischer/cloner
[link-downloads]: https://packagist.org/packages/anfischer/cloner
[link-author]: https://github.com/anfischer
[link-contributors]: ../../contributors
