<?php

namespace Queryr\TermStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Term\AliasGroup;
use Wikibase\DataModel\Term\Fingerprint;
use Wikibase\DataModel\Term\Term;

/**
 * Package public
 * @since 0.2
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermStoreWriter {

	private $connection;
	private $config;

	public function __construct( Connection $connection, TermStoreConfig $config ) {
		$this->connection = $connection;
		$this->config = $config;
	}

	/**
	 * @param EntityId $id
	 * @param Fingerprint $fingerprint
	 *
	 * @throws TermStoreException
	 */
	public function storeEntityFingerprint( EntityId $id, Fingerprint $fingerprint ) {
		$this->connection->beginTransaction();

		$this->dropTermsForId( $id );

		try {
			$this->storeFingerprintParts( $id, $fingerprint );
		}
		catch ( DBALException $ex ) {
			$this->connection->rollBack();
			throw new TermStoreException( $ex->getMessage(), $ex );
		}

		$this->connection->commit();
	}

	private function storeFingerprintParts( EntityId $id, Fingerprint $fingerprint ) {
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

}
