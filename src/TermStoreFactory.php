<?php

namespace Queryr\TermStore;

use Doctrine\DBAL\Connection;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermStoreFactory {

	private $connection;
	private $config;

	public function __construct( Connection $connection, TermStoreConfig $config ) {
		$this->connection = $connection;
		$this->config = $config;
	}

	public function newTermStoreInstaller() {
		return new TermStoreInstaller(
			$this->connection->getSchemaManager(),
			$this->config
		);
	}

	/**
	 * @return EntityIdLookup
	 */
	public function newEntityIdLookup() {
		$labelTable = new TableQueryExecutor( $this->connection, $this->config->getLabelTableName() );
		$aliasesTable = new TableQueryExecutor( $this->connection, $this->config->getAliasesTableName() );

		return new IdLookup( $labelTable, $aliasesTable );
	}

	/**
	 * @return TermStoreWriter
	 */
	public function newTermStoreWriter() {
		return new TermStoreWriter( $this->connection, $this->config );
	}

	/**
	 * Work with a more segregated interface if you can.
	 *
	 * @return TermStore
	 */
	public function newTermStore() {
		return new TermStore( $this->connection, $this->config );
	}

}