# QueryR TermStore

[![Build Status](https://secure.travis-ci.org/JeroenDeDauw/TermStore.png?branch=master)](http://travis-ci.org/JeroenDeDauw/TermStore)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/JeroenDeDauw/TermStore/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/JeroenDeDauw/TermStore/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/JeroenDeDauw/TermStore/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/JeroenDeDauw/TermStore/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/queryr/term-store/version.png)](https://packagist.org/packages/queryr/term-store)
[![Download count](https://poser.pugx.org/queryr/term-store/d/total.png)](https://packagist.org/packages/queryr/term-store)

Stores terms of [Wikibase](http://wikiba.se) entities to facilitate

* lookup of EntityIds given a term
* lookup of term(s) given an EntityId
* storing a Fingerprint for an EntityId
* removing all terms associated with an EntityId

## System dependencies

* PHP 5.5 or later (PHP 7 and HHVM are supported)
* php5-sqlite (only needed for running the tests)

## Running the tests

For tests only

    composer test

For style checks only

	composer cs

For a full CI run

	composer ci

## Release notes

### Version 1.0.0 (2015-11-03)

* Installation with Wikibase DataModel 4.x is now allowed
* Installation with Wikibase DataModel 3.x is now allowed
* Changed minimum Wikibase DataModel version to 2.5
* Added ci command that runs PHPUnit, PHPCS, PHPMD and covers tags validation
* Added TravisCI and ScrutinizerCI integration

### Version 0.2.2 (2014-10-21)

* Installation with Wikibase DataModel 2.x is now allowed

### Version 0.2.1 (2014-10-05)

* Improved performance of `TermStore::storeEntityFingerprint` via usage of a transaction

### Version 0.2 (2014-09-11)

* Added `TermStoreFactory`. Service construction should now happen via this factory
* Added `EntityIdLookup`, which is now implemented by `TermStore`
* Added `getItemIdByLabel`, `getPropertyIdByLabel`, `getItemIdByText` and `getPropertyIdByText` to `TermStore`

### Version 0.1 (2014-06-23)

* Initial release
