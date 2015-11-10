<?php

namespace Queryr\TermStore;

/**
 * Package public
 * @since 0.2
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class TermStoreException extends \RuntimeException {

	public function __construct( $message, \Exception $previous = null ) {
		parent::__construct( $message, 0, $previous );
	}

}