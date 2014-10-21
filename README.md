# QueryR TermStore

Stores terms of [Wikibase](http://wikiba.se) entities to facilitate

* lookup of EntityIds given a term
* lookup of term(s) given an EntityId
* storing a Fingerprint for an EntityId
* removing all terms associated with an EntityId

## Release notes

### Version 0.2.2 (2014-10-21)

* Installation with DataModel 2.x is now allowed

### Version 0.2.1 (2014-10-05)

* Improved performance of `TermStore::storeEntityFingerprint` via usage of a transaction

### Version 0.2 (2014-09-11)

* Added `TermStoreFactory`. Service construction should now happen via this factory
* Added `EntityIdLookup`, which is now implemented by `TermStore`
* Added `getItemIdByLabel`, `getPropertyIdByLabel`, `getItemIdByText` and `getPropertyIdByText` to `TermStore`

### Version 0.1 (2014-06-23)

* Initial release
