<?php

namespace Tests\Queryr\TermStore;

use Doctrine\DBAL\DriverManager;
use Queryr\TermStore\TermStoreConfig;
use Queryr\TermStore\TermStoreFactory;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TestEnvironment {

	public static function newInstance() {
		$instance = new self();

		$instance->getFactory()->newTermStoreInstaller()->install();

		return $instance;
	}

	public static function newInstanceWithoutTables() {
		return new self();
	}

	/**
	 * @var TermStoreFactory
	 */
	private $factory;

	private function __construct() {
		$connection = DriverManager::getConnection( array(
				'driver' => 'pdo_sqlite',
				'memory' => true,
		) );

		$config = new TermStoreConfig( 'ts_' );
		$this->factory = new TermStoreFactory( $connection, $config );
	}

	public function getFactory() {
		return $this->factory;
	}

}