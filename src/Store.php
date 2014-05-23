<?php

namespace Queryr\TermStore;

use Doctrine\DBAL\Connection;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class Store {

	private $connection;
	private $config;

	public function __construct( Connection $connection, StoreConfig $config ) {
		$this->connection = $connection;
		$this->config = $config;
	}

}