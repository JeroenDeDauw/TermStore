<?php

namespace Queryr\TermStore;

use Doctrine\DBAL\Connection;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Term\AliasGroup;
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
	private $aliasesTable;

	public function __construct( Connection $connection, StoreConfig $config ) {
		$this->labelTable = new TableQueryExecutor( $connection, $config->getLabelTableName() );
		$this->aliasesTable = new TableQueryExecutor( $connection, $config->getAliasesTableName() );
		$this->connection = $connection;
		$this->config = $config;
	}

	/**
	 * @param EntityId $id
	 * @param string $languageCode
	 *
	 * @return string|null
	 */
	public function getLabelByIdAndLanguage( EntityId $id, $languageCode ) {
		return $this->labelTable->selectOneField(
			'text',
			[
				'entity_id' => $id->getSerialization(),
				'language' => $languageCode
			]
		);
	}

	public function storeEntityFingerprint( EntityId $id, Fingerprint $fingerprint ) {
		$this->dropTermsForId( $id );

		/**
		 * @var Term $label
		 */
		foreach ( $fingerprint->getLabels() as $label ) {
			$this->storeLabel( $label->getLanguageCode(), $label->getText(), $id );
		}

		/**
		 * @var AliasGroup $aliasGroup
		 */
		foreach ( $fingerprint->getAliasGroups() as $aliasGroup ) {
			$this->storeAliases( $aliasGroup, $id );
		}
	}

	public function dropTermsForId( EntityId $id ) {
		$this->connection->delete(
			$this->config->getLabelTableName(),
			[ 'entity_id' => $id->getSerialization() ]
		);

		$this->connection->delete(
			$this->config->getAliasesTableName(),
			[ 'entity_id' => $id->getSerialization() ]
		);
	}

	private function storeLabel( $languageCode, $text, EntityId $id ) {
		$this->connection->insert(
			$this->config->getLabelTableName(),
			[
				'text' => $text,
				'text_lowercase' => strtolower( $text ),
				'language' => $languageCode,
				'entity_id' => $id->getSerialization(),
				'entity_type' => $id->getEntityType()
			]
		);
	}

	private function storeAliases( AliasGroup $aliasGroup, EntityId $id ) {
		foreach ( $aliasGroup->getAliases() as $alias ) {
			$this->connection->insert(
				$this->config->getAliasesTableName(),
				[
					'text' => $alias,
					'text_lowercase' => strtolower( $alias ),
					'language' => $aliasGroup->getLanguageCode(),
					'entity_id' => $id->getSerialization(),
					'entity_type' => $id->getEntityType()
				]
			);
		}
	}

	/**
	 * @param string $labelLanguageCode
	 * @param string $labelText
	 *
	 * @return string|null
	 */
	public function getIdByLabel( $labelLanguageCode, $labelText ) {
		return $this->labelTable->selectOneField(
			'entity_id',
			[
				'text_lowercase' => strtolower( $labelText ),
				'language' => $labelLanguageCode
			]
		);
	}

	/**
	 * @param EntityId $id
	 * @param string $languageCode
	 *
	 * @return string[]
	 */
	public function getAliasesByIdAndLanguage( EntityId $id, $languageCode ) {
		return $this->aliasesTable->selectField(
			'text',
			[
				'entity_id' => $id->getSerialization(),
				'language' => $languageCode
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

	public function selectOneField( $fieldName, array $conditions = [] ) {
		return $this->selectOne( [ $fieldName ], $conditions )[ $fieldName ];
	}

	public function selectOne( array $fieldNames = null, array $conditions = [] ) {
		$statement = $this->executeSelect( $fieldNames, $conditions );

		$result = $statement->fetch();
		return  $result === false ? null : $result;
	}

	private function executeSelect( array $fieldNames = null, array $conditions = [] ) {
		$fieldSql = $this->getFieldSql( $fieldNames );
		$conditionSql = $this->getConditionSql( $conditions );

		$sql = 'SELECT ' . $fieldSql . ' FROM ' . $this->tableName . ' WHERE ' . $conditionSql;

		$statement = $this->connection->prepare( $sql );
		$statement->execute( array_values( $conditions ) );

		return $statement;
	}

	public function select( array $fieldNames = null, array $conditions = [] ) {
		$statement = $this->executeSelect( $fieldNames, $conditions );
		return  $statement->fetchAll();
	}

	public function selectField( $fieldName, array $conditions = [] ) {
		return array_map(
			function( array $resultRow ) use ( $fieldName ) {
				return $resultRow[$fieldName];
			},
			$this->select( [ $fieldName ], $conditions )
		);
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