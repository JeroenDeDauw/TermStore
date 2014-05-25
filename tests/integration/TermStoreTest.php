<?php

namespace Tests\Queryr\TermStore;

use Doctrine\DBAL\DriverManager;
use Queryr\TermStore\TermStore;
use Queryr\TermStore\StoreConfig;
use Queryr\TermStore\StoreInstaller;
use Wikibase\DataModel\Entity\ItemId;

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
		$this->assertNull( $this->store->getTermByIdAndLanguage( new ItemId( 'Q1337' ), 'en' ) );
	}

}
