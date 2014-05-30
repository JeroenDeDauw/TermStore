<?php

namespace Queryr\TermStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
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
	 * @throws TermStoreException
	 */
	public function getLabelByIdAndLanguage( EntityId $id, $languageCode ) {
		try {
			return $this->labelTable->selectOneField(
				'text',
				[
					'entity_id' => $id->getSerialization(),
					'language' => $languageCode
				]
			);
		}
		catch ( DBALException $ex ) {
			throw new TermStoreException( $ex->getMessage(), $ex );
		}
	}

	/**
	 * @param EntityId $id
	 * @param Fingerprint $fingerprint
	 *
	 * @throws TermStoreException
	 */
	public function storeEntityFingerprint( EntityId $id, Fingerprint $fingerprint ) {
		$this->dropTermsForId( $id );

		try {
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
		catch ( DBALException $ex ) {
			throw new TermStoreException( $ex->getMessage(), $ex );
		}
	}

	/**
	 * @param EntityId $id
	 *
	 * @throws TermStoreException
	 */
	public function dropTermsForId( EntityId $id ) {
		try {
			$this->connection->delete(
				$this->config->getLabelTableName(),
				[ 'entity_id' => $id->getSerialization() ]
			);

			$this->connection->delete(
				$this->config->getAliasesTableName(),
				[ 'entity_id' => $id->getSerialization() ]
			);
		}
		catch ( DBALException $ex ) {
			throw new TermStoreException( $ex->getMessage(), $ex );
		}
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
	 * @throws TermStoreException
	 */
	public function getIdByLabel( $labelLanguageCode, $labelText ) {
		try {
			return $this->labelTable->selectOneField(
				'entity_id',
				[
					'text_lowercase' => strtolower( $labelText ),
					'language' => $labelLanguageCode
				]
			);
		}
		catch ( DBALException $ex ) {
			throw new TermStoreException( $ex->getMessage(), $ex );
		}
	}

	/**
	 * @param EntityId $id
	 * @param string $languageCode
	 *
	 * @return string[]
	 * @throws TermStoreException
	 */
	public function getAliasesByIdAndLanguage( EntityId $id, $languageCode ) {
		try {
			return $this->aliasesTable->selectField(
				'text',
				[
					'entity_id' => $id->getSerialization(),
					'language' => $languageCode
				]
			);
		}
		catch ( DBALException $ex ) {
			throw new TermStoreException( $ex->getMessage(), $ex );
		}
	}

	/**
	 * @param string $languageCode
	 * @param string $termText
	 *
	 * @return string|null
	 * @throws TermStoreException
	 */
	public function getIdByText( $languageCode, $termText ) {
		$labelMatch = $this->getIdByLabel( $languageCode, $termText );

		if ( $labelMatch !== null ) {
			return $labelMatch;
		}

		return $this->getIdByAlias( $languageCode, $termText );
	}

	/**
	 * @param string $aliasLanguageCode
	 * @param string $aliasText
	 *
	 * @return string|null
	 * @throws TermStoreException
	 */
	private function getIdByAlias( $aliasLanguageCode, $aliasText ) {
		try {
			return $this->aliasesTable->selectOneField(
				'entity_id',
				[
					'text_lowercase' => strtolower( $aliasText ),
					'language' => $aliasLanguageCode
				]
			);
		}
		catch ( DBALException $ex ) {
			throw new TermStoreException( $ex->getMessage(), $ex );
		}
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
		$statement = $this->executeSelect( $fieldNames, $conditions, 1 );

		$result = $statement->fetch();
		return  $result === false ? null : $result;
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

	private function executeSelect( array $fieldNames = null, array $conditions = [], $limit = null ) {
		$sql = $this->buildSelectSql( $fieldNames, $conditions, $limit );

		$statement = $this->connection->prepare( $sql );
		$statement->execute( array_values( $conditions ) );

		return $statement;
	}

	private function buildSelectSql( array $fieldNames = null, array $conditions = [], $limit = null ) {
		$fieldSql = $this->getFieldSql( $fieldNames );
		$conditionSql = $this->getConditionSql( $conditions );

		$sql = 'SELECT ' . $fieldSql . ' FROM ' . $this->tableName . ' WHERE ' . $conditionSql;

		if ( $limit !== null ) {
			$sql .= ' LIMIT ' . (int)$limit;
		}

		return $sql;
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