<?php

namespace Queryr\TermStore;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class StoreInstaller {

	private $schemaManager;
	private $config;

	public function __construct( AbstractSchemaManager $schemaManager, StoreConfig $config ) {
		$this->schemaManager = $schemaManager;
		$this->config = $config;
	}

	/**
	 * @throws DBALException
	 */
	public function install() {
		$this->schemaManager->createTable( $this->newLabelTable() );
	}

	private function newLabelTable() {
		$table = new Table( $this->config->getLabelTableName() );

		$table->addColumn( 'text', Type::STRING, array( 'length' => 255 ) );
		$table->addColumn( 'text_lowercase', Type::STRING, array( 'length' => 255 ) );
		$table->addColumn( 'language', Type::STRING, array( 'length' => 16 ) );
		$table->addColumn( 'entity', Type::STRING, array( 'length' => 32 ) );
		$table->addColumn( 'entity_type', Type::STRING, array( 'length' => 16 ) );

		$table->addIndex( array( 'text_lowercase', 'language' ) );
		$table->addIndex( array( 'entity', 'language' ) );
		$table->addIndex( array( 'entity_type' ) );

		return $table;
	}

	public function uninstall() {
		$this->schemaManager->dropTable( $this->config->getLabelTableName() );
	}

}