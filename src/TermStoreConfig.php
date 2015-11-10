<?php

namespace Queryr\TermStore;

/**
 * Package public
 * @since 0.2
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermStoreConfig {

	private $prefix;

	public function __construct( $tablePrefix = '' ) {
		$this->prefix = $tablePrefix;
	}

	public function getLabelTableName() {
		return $this->prefix . 'labels';
	}

	public function getAliasesTableName() {
		return $this->prefix . 'aliases';
	}

}