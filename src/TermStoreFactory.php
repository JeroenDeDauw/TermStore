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

	public function newTermStoreInstaller(): TermStoreInstaller {
		return new TermStoreInstaller(
			$this->connection->getSchemaManager(),
			$this->config
		);
	}

	public function newEntityIdLookup(): EntityIdLookup {
		$labelTable = new TableQueryExecutor( $this->connection, $this->config->getLabelTableName() );
		$aliasesTable = new TableQueryExecutor( $this->connection, $this->config->getAliasesTableName() );

		return new IdLookup( $labelTable, $aliasesTable );
	}

	public function newTermStoreWriter(): TermStoreWriter {
		return new TermStoreWriter( $this->connection, $this->config );
	}

	public function newLabelLookup(): LabelLookup {
		return $this->newTermStore();
	}

	/**
	 * Work with a more segregated interface (TermStoreWriter, EntityIdLookup, LabelLookup) if you can.
	 */
	public function newTermStore(): TermStore {
		return new TermStore( $this->connection, $this->config );
	}

}