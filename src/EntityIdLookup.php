<?php

namespace Queryr\TermStore;

/**
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
interface EntityIdLookup {

	/**
	 * Returns the first matching entity id. Case insensitive.
	 *
	 * @param string $labelLanguageCode
	 * @param string $labelText
	 *
	 * @return string|null
	 * @throws TermStoreException
	 */
	public function getIdByLabel( $labelLanguageCode, $labelText );

	/**
	 * Returns the first matching item id. Case insensitive.
	 *
	 * @param string $labelLanguageCode
	 * @param string $labelText
	 *
	 * @return string|null
	 * @throws TermStoreException
	 */
	public function getItemIdByLabel( $labelLanguageCode, $labelText );

	/**
	 * Returns the first matching property id. Case insensitive.
	 *
	 * @param string $labelLanguageCode
	 * @param string $labelText
	 *
	 * @return string|null
	 * @throws TermStoreException
	 */
	public function getPropertyIdByLabel( $labelLanguageCode, $labelText );

	/**
	 * Returns the first matching entity id. Case insensitive.
	 *
	 * @param string $languageCode
	 * @param string $termText
	 *
	 * @return string|null
	 * @throws TermStoreException
	 */
	public function getIdByText( $languageCode, $termText );

	/**
	 * Returns the first matching item id. Case insensitive.
	 *
	 * @param string $languageCode
	 * @param string $termText
	 *
	 * @return string|null
	 * @throws TermStoreException
	 */
	public function getItemIdByText( $languageCode, $termText );

	/**
	 * Returns the first matching property id. Case insensitive.
	 *
	 * @param string $languageCode
	 * @param string $termText
	 *
	 * @return string|null
	 * @throws TermStoreException
	 */
	public function getPropertyIdByText( $languageCode, $termText );

}