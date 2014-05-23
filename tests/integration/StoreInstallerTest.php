<?php

namespace Tests\Queryr\TermStore;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Queryr\TermStore\StoreConfig;
use Queryr\TermStore\StoreInstaller;

/**
 * @covers Queryr\TermStore\StoreInstaller
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class StoreInstallerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var StoreInstaller
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
		$this->storeInstaller = new StoreInstaller( $this->schemaManager, new StoreConfig( 'kittens_' ) );
	}

	public function testInstallationAndRemoval() {
		$this->storeInstaller->install();

		$this->assertTrue( $this->schemaManager->tablesExist( 'kittens_labels' ) );

		$this->storeInstaller->uninstall();

		$this->assertFalse( $this->schemaManager->tablesExist( 'kittens_labels' ) );
	}

	public function testStoresPage() {
		$this->storeInstaller->install();
	}

}
