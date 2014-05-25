<?php

namespace Queryr\TermStore;

use Doctrine\DBAL\Connection;
use Wikibase\DataModel\Entity\EntityId;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermStore {

	private $connection;
	private $config;

	public function __construct( Connection $connection, StoreConfig $config ) {
		$this->connection = $connection;
		$this->config = $config;
	}

	public function getTermByIdAndLanguage( EntityId $id, $languageCode ) {
		return null; // TODO
	}

}