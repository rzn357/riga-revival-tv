<?php
/**
 * Polylang integration.
 *
 * @package RRTV\Integrations\Classes
 */

namespace RRTV\Integrations;

defined( 'ABSPATH' ) || exit;

/**
 * Polylang Class.
 */
class Polylang {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'register_strings' ) );
	}

	/**
	 * Register strings for translation.
	 *
	 * @return void
	 */
	public function register_strings() {
		if ( ! function_exists( 'pll_register_string' ) ) {
			return;
		}
	}
}
