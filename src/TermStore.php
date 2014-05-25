<?php

namespace Queryr\TermStore;

use Doctrine\DBAL\Connection;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermStore {

	private $connection;
	private $config;
	private $labelTable;

	public function __construct( Connection $connection, StoreConfig $config ) {
		$this->labelTable = new TableQueryExecutor( $connection, $config->getLabelTableName() );
		$this->connection = $connection;
		$this->config = $config;
	}

	public function getLabelByIdAndLanguage( EntityId $id, $languageCode ) {
		return $this->labelTable->selectOneField(
			'text',
			[
				'entity' => $id->getSerialization(),
				'language' => $languageCode
			]
		);
	}

	public function storeEntityFingerprint( EntityId $id, Fingerprint $fingerprint ) {
		/**
		 * @var Term $label
		 */
		foreach ( $fingerprint->getLabels() as $label ) {
			$this->storeLabel( $label->getLanguageCode(), $label->getText(), $id );
		}
	}

	private function storeLabel( $languageCode, $text, EntityId $id ) {
		$this->connection->insert(
			$this->config->getLabelTableName(),
			[
				'text' => $text,
				'text_lowercase' => strtolower( $text ),
				'language' => $languageCode,
				'entity' => $id->getSerialization(),
				'entity_type' => $id->getEntityType()
			]
		);
	}

}

class TableQueryExecutor {

	private $connection;
	private $tableName;

	public function __construct( Connection $connection, $tableName ) {
		$this->connection = $connection;
		$this->tableName = $tableName;
	}

	public function selectOneField( $fieldName, $conditions ) {
		return $this->selectOne( [ $fieldName ], $conditions )[ $fieldName ];
	}

	public function selectOne( array $fieldNames = null, array $conditions = [] ) {
		$fieldSql = $this->getFieldSql( $fieldNames );
		$conditionSql = $this->getConditionSql( $conditions );

		$sql = 'SELECT ' . $fieldSql . ' FROM ' . $this->tableName . ' WHERE ' . $conditionSql;

		$statement = $this->connection->prepare( $sql );
		$statement->execute( array_values( $conditions ) );

		$result = $statement->fetch();
		return  $result === false ? null : $result;
	}

	private function getFieldSql( array $fields = null ) {
		return $fields === null || $fields === [] ? '*' : implode( ', ', (array)$fields );
	}

	private function getConditionSql( array $conditions ) {
		$wherePredicates = [];

		foreach ( $conditions as $columnName => $columnValue ) {
			$wherePredicates[] = $columnName . ' = ?';
		}

		return implode( ' AND ', $wherePredicates );
	}

}