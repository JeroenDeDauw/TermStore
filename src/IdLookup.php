<?php

namespace Queryr\TermStore;

use Doctrine\DBAL\DBALException;

/**
 * Package private.
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class IdLookup implements EntityIdLookup {

	private $labelTable;
	private $aliasesTable;

	public function __construct( TableQueryExecutor $labelTable, TableQueryExecutor $aliasesTable ) {
		$this->labelTable = $labelTable;
		$this->aliasesTable = $aliasesTable;
	}

	/**
	 * Returns the first matching entity id. Case insensitive.
	 *
	 * @throws TermStoreException
	 */
	public function getIdByLabel( string $labelLanguageCode, string $labelText ): ?string {
		return $this->getEntityIdByLabel( $labelLanguageCode, $labelText );
	}

	/**
	 * Returns the first matching item id. Case insensitive.
	 *
	 * @throws TermStoreException
	 */
	public function getItemIdByLabel( string $labelLanguageCode, string $labelText ): ?string {
		return $this->getEntityIdByLabel( $labelLanguageCode, $labelText, 'item' );
	}

	/**
	 * Returns the first matching property id. Case insensitive.
	 *
	 * @throws TermStoreException
	 */
	public function getPropertyIdByLabel( string $labelLanguageCode, string $labelText ): ?string {
		return $this->getEntityIdByLabel( $labelLanguageCode, $labelText, 'property' );
	}

	/**
	 * @param string $labelLanguageCode
	 * @param string $labelText
	 * @param string|null $entityTypeFilter
	 *
	 * @return string|null
	 * @throws TermStoreException
	 */
	private function getEntityIdByLabel( $labelLanguageCode, $labelText, $entityTypeFilter = null ) {
		$conditions = [
			'text_lowercase' => strtolower( $labelText ),
			'language' => $labelLanguageCode,
		];

		if ( $entityTypeFilter !== null ) {
			$conditions['entity_type'] = $entityTypeFilter;
		}

		try {
			return $this->labelTable->selectOneField(
				'entity_id',
				$conditions
			);
		}
		catch ( DBALException $ex ) {
			throw new TermStoreException( $ex->getMessage(), $ex );
		}
	}

	/**
	 * Returns the first matching entity id. Case insensitive.
	 *
	 * @throws TermStoreException
	 */
	public function getIdByText( string $languageCode, string $termText ): ?string {
		return $this->getEntityIdByText( $languageCode, $termText );
	}

	/**
	 * Returns the first matching item id. Case insensitive.
	 *
	 * @throws TermStoreException
	 */
	public function getItemIdByText( string $languageCode, string $termText ): ?string {
		return $this->getEntityIdByText( $languageCode, $termText, 'item' );
	}

	/**
	 * Returns the first matching property id. Case insensitive.
	 *
	 * @throws TermStoreException
	 */
	public function getPropertyIdByText( string $languageCode, string $termText ): ?string {
		return $this->getEntityIdByText( $languageCode, $termText, 'property' );
	}

	/**
	 * @param string $languageCode
	 * @param string $termText
	 * @param string|null $entityTypeFilter
	 *
	 * @return string|null
	 * @throws TermStoreException
	 */
	private function getEntityIdByText( string $languageCode, string $termText, $entityTypeFilter = null ) {
		$labelMatch = $this->getEntityIdByLabel( $languageCode, $termText, $entityTypeFilter );

		if ( $labelMatch !== null ) {
			return $labelMatch;
		}

		return $this->getIdByAlias( $languageCode, $termText, $entityTypeFilter );
	}

	/**
	 * @param string $aliasLanguageCode
	 * @param string $aliasText
	 * @param string|null $entityTypeFilter
	 *
	 * @return string|null
	 * @throws TermStoreException
	 */
	private function getIdByAlias( string $aliasLanguageCode, string $aliasText, $entityTypeFilter = null ) {
		$conditions = [
			'text_lowercase' => strtolower( $aliasText ),
			'language' => $aliasLanguageCode
		];

		if ( $entityTypeFilter !== null ) {
			$conditions['entity_type'] = $entityTypeFilter;
		}

		try {
			return $this->aliasesTable->selectOneField(
				'entity_id',
				$conditions
			);
		}
		catch ( DBALException $ex ) {
			throw new TermStoreException( $ex->getMessage(), $ex );
		}
	}

}