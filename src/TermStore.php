<?php

namespace Queryr\TermStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Term\Fingerprint;

/**
 * Package public
 * @since 0.2
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermStore implements LabelLookup, EntityIdLookup {

	private $storeWriter;
	private $idLookup;

	private $labelTable;
	private $aliasesTable;

	public function __construct( Connection $connection, TermStoreConfig $config ) {
		$this->labelTable = new TableQueryExecutor( $connection, $config->getLabelTableName() );
		$this->aliasesTable = new TableQueryExecutor( $connection, $config->getAliasesTableName() );

		$this->storeWriter = new TermStoreWriter( $connection, $config );
		$this->idLookup = new IdLookup( $this->labelTable, $this->aliasesTable );
	}

	/**
	 * @param EntityId $id
	 * @param Fingerprint $fingerprint
	 *
	 * @throws TermStoreException
	 */
	public function storeEntityFingerprint( EntityId $id, Fingerprint $fingerprint ) {
		$this->storeWriter->storeEntityFingerprint( $id, $fingerprint );
	}

	/**
	 * @param EntityId $id
	 *
	 * @throws TermStoreException
	 */
	public function dropTermsForId( EntityId $id ) {
		$this->storeWriter->dropTermsForId( $id );
	}

	/**
	 * @throws TermStoreException
	 */
	public function getLabelByIdAndLanguage( EntityId $id, string $languageCode ): ?string {
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
	 * @param string $languageCode
	 *
	 * @return string[]
	 * @throws TermStoreException
	 */
	public function getAliasesByIdAndLanguage( EntityId $id, string $languageCode ): array {
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
	 * Returns the first matching entity id. Case insensitive.
	 *
	 * @throws TermStoreException
	 */
	public function getIdByLabel( string $labelLanguageCode, string $labelText ): ?string {
		return $this->idLookup->getIdByLabel( $labelLanguageCode, $labelText );
	}

	/**
	 * Returns the first matching item id. Case insensitive.
	 *
	 * @throws TermStoreException
	 */
	public function getItemIdByLabel( string $labelLanguageCode, string $labelText ): ?string {
		return $this->idLookup->getItemIdByLabel( $labelLanguageCode, $labelText );
	}

	/**
	 * Returns the first matching property id. Case insensitive.
	 *
	 * @throws TermStoreException
	 */
	public function getPropertyIdByLabel( string $labelLanguageCode, string $labelText ): ?string {
		return $this->idLookup->getPropertyIdByLabel( $labelLanguageCode, $labelText );
	}

	/**
	 * Returns the first matching entity id. Case insensitive.
	 *
	 * @throws TermStoreException
	 */
	public function getIdByText( string $languageCode, string $termText ): ?string {
		return $this->idLookup->getIdByText( $languageCode, $termText );
	}

	/**
	 * Returns the first matching item id. Case insensitive.
	 *
	 * @throws TermStoreException
	 */
	public function getItemIdByText( string $languageCode, string $termText ): ?string {
		return $this->idLookup->getItemIdByText( $languageCode, $termText );
	}

	/**
	 * Returns the first matching property id. Case insensitive.
	 *
	 * @throws TermStoreException
	 */
	public function getPropertyIdByText( string $languageCode, string $termText ): ?string {
		return $this->idLookup->getPropertyIdByText( $languageCode, $termText );
	}

}
