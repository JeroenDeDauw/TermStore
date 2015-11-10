<?php

namespace Queryr\TermStore;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

/**
 * Package public, except for the constructor
 * @since 0.2
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermStoreInstaller {

	private $schemaManager;
	private $config;

	public function __construct( AbstractSchemaManager $schemaManager, TermStoreConfig $config ) {
		$this->schemaManager = $schemaManager;
		$this->config = $config;
	}

	/**
	 * @throws DBALException
	 */
	public function install() {
		$this->schemaManager->createTable( $this->newLabelTable() );
		$this->schemaManager->createTable( $this->newAliasesTable() );
	}

	private function newLabelTable() {
		$table = new Table( $this->config->getLabelTableName() );

		$table->addColumn( 'text', Type::STRING, array( 'length' => 255 ) );
		$table->addColumn( 'text_lowercase', Type::STRING, array( 'length' => 255 ) );
		$table->addColumn( 'language', Type::STRING, array( 'length' => 16 ) );
		$table->addColumn( 'entity_id', Type::STRING, array( 'length' => 32 ) );
		$table->addColumn( 'entity_type', Type::STRING, array( 'length' => 16 ) );

		$table->addIndex( array( 'text_lowercase', 'language' ) );
		$table->addIndex( array( 'entity_id', 'language' ) );
		$table->addIndex( array( 'entity_type' ) );

		return $table;
	}

	private function newAliasesTable() {
		$table = new Table( $this->config->getAliasesTableName() );

		$table->addColumn( 'text', Type::STRING, array( 'length' => 255 ) );
		$table->addColumn( 'text_lowercase', Type::STRING, array( 'length' => 255 ) );
		$table->addColumn( 'language', Type::STRING, array( 'length' => 16 ) );
		$table->addColumn( 'entity_id', Type::STRING, array( 'length' => 32 ) );
		$table->addColumn( 'entity_type', Type::STRING, array( 'length' => 16 ) );

		$table->addIndex( array( 'text_lowercase', 'language' ) );
		$table->addIndex( array( 'entity_id', 'language' ) );
		$table->addIndex( array( 'entity_type' ) );

		return $table;
	}

	public function uninstall() {
		$this->schemaManager->dropTable( $this->config->getLabelTableName() );
		$this->schemaManager->dropTable( $this->config->getAliasesTableName() );
	}

}