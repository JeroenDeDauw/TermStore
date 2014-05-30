<?php

namespace Tests\Queryr\TermStore;

use Doctrine\DBAL\DriverManager;
use Queryr\TermStore\TermStore;
use Queryr\TermStore\StoreConfig;
use Queryr\TermStore\StoreInstaller;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Term\Fingerprint;

/**
 * @covers Queryr\TermStore\TermStore
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermStoreTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var TermStore
	 */
	private $store;

	public function setUp() {
		$connection = DriverManager::getConnection( array(
			'driver' => 'pdo_sqlite',
			'memory' => true,
		) );

		$config = new StoreConfig( '' );

		$installer = new StoreInstaller( $connection->getSchemaManager(), $config );
		$installer->install();

		$this->store = new TermStore( $connection, $config );
	}

	public function testGivenNotMatchingArgs_getTermByIdAndLanguageReturnsNull() {
		$this->assertNull( $this->store->getLabelByIdAndLanguage( new ItemId( 'Q1337' ), 'en' ) );
	}

	public function testStoreIdAndFingerprint() {
		$id = new ItemId( 'Q1337' );

		$fingerprint = Fingerprint::newEmpty();
		$fingerprint->setLabel( 'en', 'en label' );
		$fingerprint->setLabel( 'de', 'de label' );
		$fingerprint->setDescription( 'en', 'en description' );
		$fingerprint->setAliasGroup( 'en', [ 'first en alias', 'second en alias' ] );

		$this->store->storeEntityFingerprint( $id, $fingerprint );

		$this->assertSame(
			'en label',
			$this->store->getLabelByIdAndLanguage( $id, 'en' )
		);

		$this->assertSame(
			'de label',
			$this->store->getLabelByIdAndLanguage( $id, 'de' )
		);

		$this->assertSame(
			[ 'first en alias', 'second en alias' ],
			$this->store->getAliasesByIdAndLanguage( $id, 'en' )
		);
	}

	public function testGetIdByLabelAndLanguage() {
		$id = new ItemId( 'Q1337' );

		$fingerprint = Fingerprint::newEmpty();
		$fingerprint->setLabel( 'en', 'en label' );
		$fingerprint->setLabel( 'de', 'de label' );

		$this->store->storeEntityFingerprint( $id, $fingerprint );

		$this->assertSame(
			'Q1337',
			$this->store->getIdByLabel( 'en', 'en label' )
		);
	}

	public function testStoreFingerprintRemovesOldData() {
		$id = new ItemId( 'Q1337' );

		$fingerprint = Fingerprint::newEmpty();
		$fingerprint->setLabel( 'en', 'en label' );
		$fingerprint->setLabel( 'de', 'de label' );
		$fingerprint->setAliasGroup( 'en', [ 'first en alias', 'second en alias' ] );

		$this->store->storeEntityFingerprint( $id, $fingerprint );

		$fingerprint = Fingerprint::newEmpty();
		$fingerprint->setLabel( 'de', 'new de label' );

		$this->store->storeEntityFingerprint( $id, $fingerprint );

		$this->assertEquals(
			'new de label',
			$this->store->getLabelByIdAndLanguage( $id, 'de' )
		);

		$this->assertNull( $this->store->getLabelByIdAndLanguage( $id, 'en' ) );
		$this->assertEmpty( $this->store->getAliasesByIdAndLanguage( $id, 'en' ) );
	}

	public function testGivenNonMatchingArgs_getAliasesReturnsEmptyArray() {
		$this->assertSame( [], $this->store->getAliasesByIdAndLanguage( new ItemId( 'Q1337' ), 'en' ) );
	}

	public function testGetIdByTextReturnsMatchBasedOnLabel() {
		$id = new ItemId( 'Q1337' );

		$fingerprint = Fingerprint::newEmpty();
		$fingerprint->setLabel( 'en', 'kittens' );
		$fingerprint->setAliasGroup( 'en', [ 'first en alias', 'second en alias' ] );

		$this->store->storeEntityFingerprint( $id, $fingerprint );

		$id = new ItemId( 'Q42' );

		$fingerprint = Fingerprint::newEmpty();
		$fingerprint->setLabel( 'en', 'foobar' );
		$fingerprint->setAliasGroup( 'en', [ 'kittens', 'first en alias' ] );

		$this->store->storeEntityFingerprint( $id, $fingerprint );

		$this->assertSame(
			'Q1337',
			$this->store->getIdByText( 'en', 'kittens' )
		);
	}

	public function testGetIdByTextReturnsAliasBasedMatchIfNoLabelsMatch() {
		$id = new ItemId( 'Q1337' );

		$fingerprint = Fingerprint::newEmpty();
		$fingerprint->setLabel( 'en', 'foobar' );
		$fingerprint->setAliasGroup( 'en', [ 'first en alias', 'second en alias' ] );

		$this->store->storeEntityFingerprint( $id, $fingerprint );

		$id = new ItemId( 'Q42' );

		$fingerprint = Fingerprint::newEmpty();
		$fingerprint->setLabel( 'en', 'foobar' );
		$fingerprint->setAliasGroup( 'en', [ 'kittens', 'first en alias' ] );

		$this->store->storeEntityFingerprint( $id, $fingerprint );

		$this->assertSame(
			'Q42',
			$this->store->getIdByText( 'en', 'kittens' )
		);
	}

	public function testByLabelLookupIsCaseInsensitive() {
		$id = new ItemId( 'Q1337' );

		$fingerprint = Fingerprint::newEmpty();
		$fingerprint->setLabel( 'en', 'EN label' );

		$this->store->storeEntityFingerprint( $id, $fingerprint );

		$this->assertSame(
			'Q1337',
			$this->store->getIdByLabel( 'en', 'en LABEL' )
		);
	}

}
