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


## Install

Via Composer

``` bash
$ composer require anfischer/cloner
```

## Usage

``` php
use Anfischer/Cloner;

$clone = (new CloneService)->clone($someEloquentModel);
$persistedModel = (new PersistenceService)->persist($clone);

or

$cloner = new Cloner(new CloneService, new PersistenceService);
$clone = $cloner->clone($someEloquentModel);
$persistedModel = $cloner->persist($clone);

or

$clone = \Cloner::clone($someEloquentModel);
$persistedModel = \Cloner::persist($clone);

---

Cloner also exposes a convinience method for cloning and persisting at the same time:

$cloner = new Cloner(new CloneService, new PersistenceService);
$persistedModel = $cloner->cloneAndPersist($someEloquentModel);

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
