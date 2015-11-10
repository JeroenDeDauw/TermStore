<?php

namespace Queryr\TermStore;

use Doctrine\DBAL\Connection;

/**
 * Package public
 * @since 0.2
 *
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
	 * @since 1.1
	 *
	 * @return LabelLookup
	 */
	public function newLabelLookup() {
		return $this->newTermStore();
	}

	/**
	 * Work with a more segregated interface (TermStoreWriter, EntityIdLookup, LabelLookup) if you can.
	 *
	 * @return TermStore
	 */
	public function newTermStore() {
		return new TermStore( $this->connection, $this->config );
	}

}