<?php
/**
 * Riga Revival TV setup.
 *
 * @package RRTV\Classes
 */

namespace RRTV;

use RRTV\Integrations\Acf\Acf;
use RRTV\Integrations\Polylang;
use RRTV\Integrations\Yoast_SEO;
use RRTV\Optimizers\WP_Performance_Optimizer;
use RRTV\Optimizers\Accessibility_Optimizer;
use RRTV\Optimizers\Security_Optimizer;

defined( 'ABSPATH' ) || exit;

/**
 * Main Class.
 */
class Riga_Revival_Tv {

	/**
	 * Theme prefix.
	 *
	 * @var string
	 */
	public static $theme_prefix = 'RRTV';

	/**
	 * Theme prefix in lowercase.
	 *
	 * @var string
	 */
	public static $theme_prefix_lowercase = 'rrtv';

	/**
	 * Theme's options array name.
	 *
	 * @var string
	 */
	public static $theme_option_name = 'settings';

	/**
	 * Vite integration layer.
	 *
	 * @var Vite
	 */
	private $vite;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->vite = new Vite();
		$this->init_hooks();
		$this->load_components();
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'after_switch_theme', array( $this, 'activate' ) );
		add_action( 'switch_theme', array( $this, 'deactivate' ) );

		add_action( 'after_setup_theme', array( $this, 'theme_setup' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_gutenberg_editor_assets' ) );

		add_filter( 'use_block_editor_for_post', array( $this, 'disable_gutenberg_editor' ), 10, 2 );

		add_filter( 'mce_buttons_2', array( $this, 'extend_mce_buttons_2' ) );
		add_filter( 'tiny_mce_before_init', array( $this, 'change_tiny_mce_settings' ) );
		add_filter( 'mce_css', array( $this, 'add_custom_editor_styles' ) );

		add_filter( 'theme_page_templates', array( $this, 'filter_templates_exclude_dist' ) );
	}

	/**
	 * Filter theme templates to exclude those in the 'dist' directory to prevent conflicts.
	 *
	 * @param array $templates - Array of templates.
	 *
	 * @return array
	 */
	public function filter_templates_exclude_dist( $templates ) {
		foreach ( $templates as $file => $name ) {
			if ( strpos( $file, 'dist/' ) === 0 ) {
				unset( $templates[ $file ] );
			}
		}
		return $templates;
	}

	/**
	 * Add custom editor styles to TinyMCE.
	 *
	 * @param string $mce_css - Existing editor styles.
	 *
	 * @return string
	 */
	public function add_custom_editor_styles( $mce_css ) {
		$custom_style_url = $this->vite->get_vite_asset_url( 'assets/css/style-admin.min.css' );

		if ( $this->vite->is_vite_dev_server_running() ) {
			$custom_style_url = RRTV_THEME_DIR_URL . '/assets/css/style-admin.min.css';
		}

		if ( ! empty( $mce_css ) ) {
			$mce_css .= ',' . $custom_style_url;
		} else {
			$mce_css = $custom_style_url;
		}

		return $mce_css;
	}

	/**
	 * Add custom font sizes to TinyMCE editor.
	 *
	 * @param array $init - TinyMCE initialization settings.
	 *
	 * @return array
	 */
	public function change_tiny_mce_settings( $init ) {

		// Define custom font sizes for the TinyMCE editor.
		$init['fontsize_formats'] = '8px 9px 10px 11px 12px 13px 14px 15px 16px 18px 20px 22px 24px 26px 28px 30px 32px 36px 40px 48px 56px 64px 72px 96px';

		// Define custom font families for the TinyMCE editor.
		$custom_fonts = 'Cinzel=Cinzel,sans-serif;' .
		'Oswald=Oswald,Impact,sans-serif;' .
		'Open Sans=Open Sans,sans-serif;';

		if ( ! empty( $init['font_formats'] ) ) {
			$init['font_formats'] = rtrim( $init['font_formats'], ';' ) . ';' . ltrim( $custom_fonts, ';' );
		} else {
			$default =
			'Andale Mono=andale mono,times;' .
			'Arial=arial,helvetica,sans-serif;' .
			'Arial Black=arial black,avant garde;' .
			'Book Antiqua=book antiqua,palatino;' .
			'Comic Sans MS=comic sans ms,sans-serif;' .
			'Courier New=courier new,courier;' .
			'Georgia=georgia,palatino;' .
			'Helvetica=helvetica,arial,sans-serif;' .
			'Impact=impact,chicago;' .
			'Symbol=symbol;' .
			'Tahoma=tahoma,arial,helvetica,sans-serif;' .
			'Terminal=terminal,monaco;' .
			'Times New Roman=times new roman,times;' .
			'Trebuchet MS=trebuchet ms,geneva;' .
			'Verdana=verdana,geneva;' .
			'Webdings=webdings;' .
			'Wingdings=wingdings,zapf dingbats;';

			$init['font_formats'] = rtrim( $default, ';' ) . ';' . ltrim( $custom_fonts, ';' );
		}

		return $init;
	}

	/**
	 * Extend TinyMCE editor buttons.
	 *
	 * @param array $buttons - Existing buttons.
	 *
	 * @return array
	 */
	public function extend_mce_buttons_2( $buttons ) {

		// Add background color buttons to the second row of TinyMCE editor buttons.
		array_unshift( $buttons, 'backcolor' );

		// Add font family and font size dropdowns to the second row of TinyMCE editor buttons.
		array_unshift( $buttons, 'fontselect' );
		array_unshift( $buttons, 'fontsizeselect' );

		return $buttons;
	}

	/**
	 * Disable Gutenberg editor for posts.
	 *
	 * @param bool    $use_block_editor - Whether to use block editor.
	 * @param WP_Post $post             - Post object.
	 *
	 * @return bool
	 */
	public function disable_gutenberg_editor( $use_block_editor, $post ) {
		$template = get_page_template_slug( $post );

		if ( 'homepage.php' === $template || 'watch-live.php' === $template || 'videos.php' === $template || 'contacts.php' === $template || 'donate.php' === $template || 'about-us.php' === $template ) {
			return false;
		}

		return $use_block_editor;
	}

	/**
	 * Load components.
	 *
	 * @return void
	 */
	private function load_components() {
		new WP_Performance_Optimizer();
		new Accessibility_Optimizer();
		new Security_Optimizer();
		new Shortcodes();
		new Clampify_Adapter();
		new Ajax();
		new Router();
		new Acf();
		new Polylang();
		new Yoast_SEO();
	}

	/**
	 * Theme setup.
	 *
	 * @return void
	 */
	public function theme_setup() {
		load_theme_textdomain( 'riga-revival-tv', RRTV_THEME_DIR_PATH . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );

		// Add editor styles support.
		add_theme_support( 'editor-styles' );
		add_editor_style( $this->vite->get_vite_asset_relative_path( 'assets/css/style-admin-gutenberg-editor.min.css' ) );

		// Add support for wide and full alignment.
		add_theme_support( 'align-wide' );

		// Add support for responsive embeds.
		add_theme_support( 'responsive-embeds' );

		register_nav_menus(
			array(
				'primary-menu' => esc_html( 'Primary Menu' ),
				'footer-menu'  => esc_html( 'Footer Menu' ),
			)
		);
	}

	/**
	 * Get theme settings.
	 *
	 * @return array
	 */
	private function get_theme_settings() {
		return get_option( self::$theme_prefix_lowercase . '_' . self::$theme_option_name );
	}

	/**
	 * Update theme settings.
	 *
	 * @param string $name  - key.
	 * @param mixed  $value - value.
	 *
	 * @return void
	 */
	private function update_theme_settings( $name, $value ) {

		if ( ! $name || ! $value ) {
			return;
		}

		$data = $this->get_theme_settings();

		if ( ! $data || ! is_array( $data ) ) {
			$data = array();
		}

		$data[ $name ] = $value;

		update_option( self::$theme_prefix_lowercase . '_' . self::$theme_option_name, $data );
	}

	/**
	 * Run code during theme activation.
	 *
	 * @return void
	 */
	public function activate() {
	}

	/**
	 * Run code during theme deactivation.
	 *
	 * @return void
	 */
	public function deactivate() {
	}

	/**
	 * Load client assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		$script_handle = self::$theme_prefix_lowercase . '-scripts';
		$vite_loaded   = $this->vite->enqueue_vite_entry( $script_handle, 'src/js/main.js', array( 'jquery' ), true );

		if ( ! $vite_loaded ) {
			wp_enqueue_style( self::$theme_prefix_lowercase . '-style', RRTV_THEME_DIR_URL . '/assets/css/style.min.css', array(), RRTV_THEME_VERSION );
			wp_enqueue_script( $script_handle, RRTV_THEME_DIR_URL . '/assets/js/scripts.min.js', array( 'jquery' ), RRTV_THEME_VERSION, true );
		}

		$this->enqueue_critical_template_assets();

		// Get project settings config and pass it to the frontend via inline script.
		$project_settings_config      = $this->get_project_settings_config();
		$project_settings_config_json = wp_json_encode( $project_settings_config );

		if ( false !== $project_settings_config_json ) {
			wp_add_inline_script(
				$script_handle,
				'window.PROJECT_SETTINGS_CONFIG = ' . $project_settings_config_json . ';',
				'before'
			);
		}

		// Prepare data for AJAX requests and pass it to the frontend via localized script.
		$rrtv_ajax_data = array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'rrtv_theme_security_nonce' ),
		);

		wp_localize_script(
			$script_handle,
			'rrtvAjax',
			$rrtv_ajax_data
		);

		// Google Maps.
		if ( is_page_template( 'contacts.php' ) ) {
			$api_key  = get_field( 'google_api_key', 'option' );
			$map_data = get_field( 'map' );
			wp_localize_script(
				$script_handle,
				'rrtv_map_data',
				array(
					'apiKey' => is_string( $api_key ) ? $api_key : '',
					'lat'    => $map_data['lat'] ?? '',
					'lng'    => $map_data['lng'] ?? '',
				)
			);
		}
	}

	/**
	 * Load admin assets.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets() {
		$vite_loaded = $this->vite->enqueue_vite_entry( self::$theme_prefix_lowercase . '-scripts-admin', 'src/js/admin/main.js', array( 'jquery' ), true );

		if ( ! $vite_loaded ) {
			wp_enqueue_style( self::$theme_prefix_lowercase . '-style-admin', RRTV_THEME_DIR_URL . '/assets/css/style-admin.min.css', array(), RRTV_THEME_VERSION );
			wp_enqueue_script( self::$theme_prefix_lowercase . '-scripts-admin', RRTV_THEME_DIR_URL . '/assets/js/scripts-admin.min.js', array( 'jquery' ), RRTV_THEME_VERSION, true );
		}
	}

	/**
	 * Load Gutenberg editor assets.
	 *
	 * @return void
	 */
	public function enqueue_gutenberg_editor_assets() {
		$this->vite->enqueue_vite_entry( self::$theme_prefix_lowercase . '-scripts-admin-gutenberg-editor', 'src/js/admin/gutenberg-editor/main.js', array(), true );
	}

	/**
	 * Get normalized current template slug for critical assets.
	 *
	 * @return string
	 */
	private function get_current_template_slug() {
		$template_slug = '';

		if ( is_page() ) {
			$page_slug = get_page_template_slug();

			if ( is_string( $page_slug ) && '' !== $page_slug ) {
				$template_slug = basename( $page_slug, '.php' );
			}
		}

		if ( '' === $template_slug ) {
			global $template;

			if ( is_string( $template ) && '' !== $template ) {
				$template_slug = basename( $template, '.php' );
			}
		}

		$template_slug = strtolower( str_replace( '_', '-', (string) $template_slug ) );
		$template_slug = preg_replace( '/[^a-z0-9-]/', '-', $template_slug );
		$template_slug = preg_replace( '/-+/', '-', (string) $template_slug );

		return trim( (string) $template_slug, '-' );
	}

	/**
	 * Enqueue critical JS/CSS from per-template folders.
	 *
	 * @return void
	 */
	private function enqueue_critical_template_assets() {
		$template_slug = $this->get_current_template_slug();

		if ( '' === $template_slug ) {
			return;
		}

		if ( $this->vite->is_vite_dev_server_running() ) {
			$this->enqueue_critical_template_assets_in_dev( $template_slug );
			return;
		}

		$critical_entry_key   = 'virtual:critical-template/' . $template_slug;
		$critical_css_files   = $this->vite->get_vite_entry_css_files( $critical_entry_key );
		$css_handle_prefix    = self::$theme_prefix_lowercase . '-critical-style-' . $template_slug;
		$script_handle_prefix = self::$theme_prefix_lowercase . '-critical-script-' . $template_slug;

		if ( ! empty( $critical_css_files ) ) {
			foreach ( $critical_css_files as $index => $css_file ) {
				if ( ! is_string( $css_file ) || '' === $css_file ) {
					continue;
				}

				$css_path = trailingslashit( RRTV_THEME_DIR_PATH ) . Vite::get_vite_asset_relative_path( $css_file );
				$css_url  = Vite::get_vite_asset_url( $css_file );

				$this->enqueue_inline_critical_style( $css_handle_prefix . '-' . $index, $css_path, $css_url );
			}
		} else {
			$relative_css_directory = 'assets/css/critical/' . $template_slug;
			$absolute_css_directory = trailingslashit( RRTV_THEME_DIR_PATH ) . Vite::get_vite_asset_relative_path( $relative_css_directory );

			if ( is_dir( $absolute_css_directory ) ) {
				$css_files = glob( trailingslashit( $absolute_css_directory ) . '*.css' );

				if ( ! empty( $css_files ) && is_array( $css_files ) ) {
					sort( $css_files, SORT_STRING );

					foreach ( $css_files as $index => $css_file ) {
						$css_basename = basename( $css_file );
						$css_url      = Vite::get_vite_asset_url( $relative_css_directory . '/' . $css_basename );

						$this->enqueue_inline_critical_style( $css_handle_prefix . '-' . $index, $css_file, $css_url );
					}
				}
			}
		}

		$relative_js_directory = 'assets/js/critical/' . $template_slug;
		$absolute_js_directory = trailingslashit( RRTV_THEME_DIR_PATH ) . Vite::get_vite_asset_relative_path( $relative_js_directory );

		if ( ! is_dir( $absolute_js_directory ) ) {
			return;
		}

		$script_files = glob( trailingslashit( $absolute_js_directory ) . '*.js' );

		if ( empty( $script_files ) || ! is_array( $script_files ) ) {
			return;
		}

		sort( $script_files, SORT_STRING );

		foreach ( $script_files as $index => $script_file ) {
			$script_basename = basename( $script_file );
			$script_url      = Vite::get_vite_asset_url( $relative_js_directory . '/' . $script_basename );

			$this->enqueue_inline_critical_script( $script_handle_prefix . '-' . $index, $script_file, $script_url );
		}
	}

	/**
	 * Enqueue critical style as inline CSS with file fallback.
	 *
	 * @param string $style_handle       - Style handle.
	 * @param string $absolute_file_path - Absolute CSS file path.
	 * @param string $fallback_style_url - Fallback style URL when file cannot be read.
	 *
	 * @return void
	 */
	private function enqueue_inline_critical_style( $style_handle, $absolute_file_path, $fallback_style_url ) {
		if ( ! is_string( $absolute_file_path ) || '' === $absolute_file_path || ! is_readable( $absolute_file_path ) ) {
			wp_enqueue_style( $style_handle, $fallback_style_url, array(), RRTV_THEME_VERSION );
			return;
		}

		$style_content = file_get_contents( $absolute_file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( false === $style_content || '' === trim( $style_content ) ) {
			wp_enqueue_style( $style_handle, $fallback_style_url, array(), RRTV_THEME_VERSION );
			return;
		}

		wp_register_style( $style_handle, false, array(), RRTV_THEME_VERSION );
		wp_enqueue_style( $style_handle );
		wp_add_inline_style( $style_handle, $style_content );
	}

	/**
	 * Enqueue critical script as inline JS with file fallback.
	 *
	 * @param string $script_handle       - Script handle.
	 * @param string $absolute_file_path  - Absolute JS file path.
	 * @param string $fallback_script_url - Fallback script URL when file cannot be read.
	 *
	 * @return void
	 */
	private function enqueue_inline_critical_script( $script_handle, $absolute_file_path, $fallback_script_url ) {
		if ( ! is_string( $absolute_file_path ) || '' === $absolute_file_path || ! is_readable( $absolute_file_path ) ) {
			wp_enqueue_script( $script_handle, $fallback_script_url, array(), RRTV_THEME_VERSION, true );
			$this->vite->register_module_script_handle( $script_handle );
			return;
		}

		$script_content = file_get_contents( $absolute_file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( false === $script_content || '' === trim( $script_content ) ) {
			wp_enqueue_script( $script_handle, $fallback_script_url, array(), RRTV_THEME_VERSION, true );
			$this->vite->register_module_script_handle( $script_handle );
			return;
		}

		wp_register_script( $script_handle, false, array(), RRTV_THEME_VERSION, false );
		wp_enqueue_script( $script_handle );
		$this->vite->register_inline_module_script_handle( $script_handle );
		wp_add_inline_script( $script_handle, $script_content );
	}

	/**
	 * Enqueue critical assets from source files in development mode.
	 *
	 * @param string $template_slug - Normalized template slug.
	 *
	 * @return void
	 */
	private function enqueue_critical_template_assets_in_dev( $template_slug ) {
		$critical_template_map = $this->get_critical_template_asset_map( $template_slug );

		if ( empty( $critical_template_map ) || ! is_array( $critical_template_map ) ) {
			return;
		}

		$script_paths = ! empty( $critical_template_map['scriptPaths'] ) && is_array( $critical_template_map['scriptPaths'] )
			? $critical_template_map['scriptPaths']
			: array();
		$style_paths  = ! empty( $critical_template_map['stylePaths'] ) && is_array( $critical_template_map['stylePaths'] )
			? $critical_template_map['stylePaths']
			: array();

		foreach ( $style_paths as $index => $style_path ) {
			$style_handle = self::$theme_prefix_lowercase . '-critical-style-module-' . $template_slug . '-' . $index;
			$this->vite->enqueue_style_file( $style_handle, $style_path, array(), 'all' );
		}

		foreach ( $script_paths as $index => $script_path ) {
			$script_handle = self::$theme_prefix_lowercase . '-critical-script-' . $template_slug . '-' . $index;
			$this->vite->enqueue_script_file( $script_handle, $script_path, array(), true );
		}
	}

	/**
	 * Get template-specific critical asset map.
	 *
	 * @param string $template_slug - Normalized template slug.
	 *
	 * @return array
	 */
	private function get_critical_template_asset_map( $template_slug ) {
		$critical_map_path = trailingslashit( RRTV_THEME_DIR_PATH ) . 'dist/.vite/critical-component-map.json';

		if ( ! is_readable( $critical_map_path ) ) {
			return array();
		}

		if ( function_exists( 'wp_json_file_decode' ) ) {
			$critical_map = wp_json_file_decode( $critical_map_path, array( 'associative' => true ) );

			if ( is_array( $critical_map ) && ! empty( $critical_map[ $template_slug ] ) ) {
				return is_array( $critical_map[ $template_slug ] ) ? $critical_map[ $template_slug ] : array();
			}
		}

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();

		global $wp_filesystem;

		if ( ! $wp_filesystem || ! $wp_filesystem->exists( $critical_map_path ) ) {
			return array();
		}

		$critical_map_raw = $wp_filesystem->get_contents( $critical_map_path );

		if ( false === $critical_map_raw || '' === $critical_map_raw ) {
			return array();
		}

		$critical_map = json_decode( $critical_map_raw, true );

		if ( ! is_array( $critical_map ) || empty( $critical_map[ $template_slug ] ) ) {
			return array();
		}

		return is_array( $critical_map[ $template_slug ] ) ? $critical_map[ $template_slug ] : array();
	}

	/**
	 * Get project settings config.
	 *
	 * @return array
	 */
	public function get_project_settings_config() {
		$settings_file = trailingslashit( RRTV_THEME_DIR_PATH ) . 'projectsettings.json';

		if ( ! is_readable( $settings_file ) ) {
			return array();
		}

		if ( function_exists( 'wp_json_file_decode' ) ) {
			$settings = wp_json_file_decode( $settings_file, array( 'associative' => true ) );
			if ( is_array( $settings ) ) {
				return $settings;
			}
		}

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();

		global $wp_filesystem;

		if ( ! $wp_filesystem || ! $wp_filesystem->exists( $settings_file ) ) {
			return array();
		}

		$raw_settings = $wp_filesystem->get_contents( $settings_file );
		if ( false === $raw_settings || '' === $raw_settings ) {
			return array();
		}

		$decoded_settings = json_decode( $raw_settings, true );

		return is_array( $decoded_settings ) ? $decoded_settings : array();
	}
}
