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

		$table->addColumn( 'item_id', Type::BIGINT );
		$table->addColumn( 'item_json', Type::BLOB );
		$table->addColumn( 'page_title', Type::STRING, array( 'length' => 255 ) );
		$table->addColumn( 'revision_id', Type::BIGINT );
		$table->addColumn( 'revision_time', Type::STRING, array( 'length' => 25 ) );

		$table->addIndex( array( 'item_id' ) );
		$table->addIndex( array( 'page_title' ) );
		$table->addIndex( array( 'revision_id' ) );
		$table->addIndex( array( 'revision_time' ) );

		return $table;
	}

	public function uninstall() {
		$this->schemaManager->dropTable( $this->config->getLabelTableName() );
	}

}