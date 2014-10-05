<?php

namespace Tests\Queryr\TermStore;

use Queryr\TermStore\TermStoreWriter;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Term\Fingerprint;

/**
 * @covers Queryr\TermStore\TermStoreWriter
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermStoreWriterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var TermStoreWriter
	 */
	private $store;

	public function setUp() {
		$this->store = TestEnvironment::newInstance()->getFactory()->newTermStoreWriter();
	}

	public function testWhenNoConnection_storeEntityFingerprintThrowsException() {
		$writer = TestEnvironment::newInstanceWithoutTables()->getFactory()->newTermStoreWriter();

		$this->setExpectedException( 'Queryr\TermStore\TermStoreException' );
		$writer->storeEntityFingerprint( new ItemId( 'Q1' ), Fingerprint::newEmpty() );
	}

	public function testWhenNoConnection_dropTermsForIdThrowsException() {
		$writer = TestEnvironment::newInstanceWithoutTables()->getFactory()->newTermStoreWriter();

		$this->setExpectedException( 'Queryr\TermStore\TermStoreException' );
		$writer->dropTermsForId( new ItemId( 'Q1' ) );
	}

}
