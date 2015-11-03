<?php

namespace Tests\Queryr\TermStore;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Queryr\TermStore\TermStoreConfig;
use Queryr\TermStore\TermStoreInstaller;

/**
 * @covers Queryr\TermStore\TermStoreInstaller
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermStoreInstallerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var TermStoreInstaller
	 */
	private $storeInstaller;

	/**
	 * @var AbstractSchemaManager
	 */
	private $schemaManager;

	public function setUp() {
		$connection = DriverManager::getConnection( array(
			'driver' => 'pdo_sqlite',
			'memory' => true,
		) );

		$this->schemaManager = $connection->getSchemaManager();
		$this->storeInstaller = new TermStoreInstaller( $this->schemaManager, new TermStoreConfig( 'kittens_' ) );
	}

	public function testInstallationAndRemoval() {
		$this->storeInstaller->install();

		$this->assertTrue( $this->schemaManager->tablesExist( 'kittens_labels' ) );
		$this->assertTrue( $this->schemaManager->tablesExist( 'kittens_aliases' ) );

		$this->storeInstaller->uninstall();

		$this->assertFalse( $this->schemaManager->tablesExist( 'kittens_labels' ) );
		$this->assertFalse( $this->schemaManager->tablesExist( 'kittens_aliases' ) );
	}

	public function testStoresPage() {
		$this->storeInstaller->install();
	}

}
