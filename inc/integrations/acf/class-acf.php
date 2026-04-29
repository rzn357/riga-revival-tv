<?php
/**
 * ACF Helper Class
 *
 * @package RRTV\Integrations\Acf\Classes
 */

namespace RRTV\Integrations\Acf;

use RRTV\Integrations\Acf\Blocks\Gutenberg_Block_Example\Gutenberg_Block_Example;

defined( 'ABSPATH' ) || exit;

/**
 * ACF Helper Class.
 */
class Acf {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'acf/include_fields', array( $this, 'include_fields' ) );
		add_action( 'acf/init', array( $this, 'add_google_api_key' ) );
	}

	/**
	 * Add Google Maps API Key to ACF settings.
	 *
	 * @return void
	 */
	public function add_google_api_key() {
		acf_update_setting( 'google_api_key', get_field( 'google_api_key', 'option' ) );
	}

	/**
	 * Include ACF fields.
	 *
	 * @return void
	 */
	public function include_fields() {
	}
}
