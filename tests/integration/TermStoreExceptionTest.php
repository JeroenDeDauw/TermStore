<?php

namespace Tests\Queryr\TermStore;

use Doctrine\DBAL\DriverManager;
use Queryr\TermStore\TermStoreConfig;
use Queryr\TermStore\TermStore;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Term\Fingerprint;

/**
 * @covers Queryr\TermStore\TermStore
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermStoreExceptionTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var TermStore
	 */
	private $store;

	public function setUp() {
		$connection = DriverManager::getConnection( array(
			'driver' => 'pdo_sqlite',
			'memory' => true,
		) );

		$this->store = new TermStore( $connection, new TermStoreConfig( '' ) );
	}

	public function testInsertWhenStoreNotInstalledCausesTermStoreException() {
		$id = new ItemId( 'Q1337' );

		$fingerprint = Fingerprint::newEmpty();
		$fingerprint->setLabel( 'en', 'EN label' );

		$this->setExpectedException( 'Queryr\TermStore\TermStoreException' );
		$this->store->storeEntityFingerprint( $id, $fingerprint );
	}

}
