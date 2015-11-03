<?php

namespace Queryr\TermStore;

use Doctrine\DBAL\Connection;

/**
 * Package private.
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TableQueryExecutor {

	private $connection;
	private $tableName;

	public function __construct( Connection $connection, $tableName ) {
		$this->connection = $connection;
		$this->tableName = $tableName;
	}

	public function selectOneField( $fieldName, array $conditions = [] ) {
		return $this->selectOne( [ $fieldName ], $conditions )[$fieldName];
	}

	public function selectOne( array $fieldNames = null, array $conditions = [] ) {
		$statement = $this->executeSelect( $fieldNames, $conditions, 1 );

		$result = $statement->fetch();
		return $result === false ? null : $result;
	}

	public function select( array $fieldNames = null, array $conditions = [] ) {
		$statement = $this->executeSelect( $fieldNames, $conditions );
		return $statement->fetchAll();
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
