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

		$this->assertEquals(
			'en label',
			$this->store->getLabelByIdAndLanguage( $id, 'en' )
		);

		$this->assertEquals(
			'de label',
			$this->store->getLabelByIdAndLanguage( $id, 'de' )
		);

		$this->assertEquals(
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

		$this->assertEquals(
			$id,
			$this->store->getIdByLabel( 'en', 'en label' )
		);
	}

}
