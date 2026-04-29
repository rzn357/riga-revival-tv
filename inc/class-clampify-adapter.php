<?php
/**
 * Clampify adapter class.
 *
 * @package RRTV\Classes
 */

namespace RRTV;

defined( 'ABSPATH' ) || exit;

/**
 * This class adapts Clampify functionality to work with WordPress plugins.
 */
class Clampify_Adapter {

	/**
	 * Vite integration layer.
	 *
	 * @var Vite
	 */
	private $vite;

	/**
	 * Extra data or functionality for future use.
	 *
	 * @var mixed
	 */
	private $extra;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->vite = new Vite();
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'unload_scripts_and_styles' ), 999 );
		add_action( 'wp_print_styles', array( $this, 'unload_scripts_and_styles' ), 999 );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts_and_styles' ), 999 );
		add_action( 'wp_print_styles', array( $this, 'load_scripts_and_styles' ), 999 );
	}

	/**
	 * Load specific scripts and styles on frontend.
	 *
	 * @return void
	 */
	public function load_scripts_and_styles() {
		if ( isset( $_GET['givewp-route'] ) && 'donation-form-view' === $_GET['givewp-route'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			global $wp_styles;
			$this->vite->enqueue_style_file( 'givewp-base-form-styles', 'src/sass/clampify-adapter/give/baseFormDesignCss.css' );
			if ( ! empty( $wp_styles->registered['givewp-base-form-styles'] ) ) {
				$wp_styles->registered['givewp-base-form-styles']->extra = $this->extra;
			}
		}
	}

	/**
	 * Unload specific scripts and styles on frontend.
	 *
	 * @return void
	 */
	public function unload_scripts_and_styles() {
		if ( isset( $_GET['givewp-route'] ) && 'donation-form-view' === $_GET['givewp-route'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			global $wp_styles;
			if ( ! empty( $wp_styles->registered['givewp-base-form-styles'] ) ) {
				$this->extra = $wp_styles->registered['givewp-base-form-styles']->extra;
			}
			$this->unload_styles( 'givewp-base-form-styles' );
		}
	}

	/**
	 * Unload a specific style on frontend.
	 *
	 * @param string $handle - The style handle.
	 *
	 * @return void
	 */
	private function unload_styles( $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}
}
