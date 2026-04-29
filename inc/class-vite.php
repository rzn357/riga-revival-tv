<?php
/**
 * Vite class.
 *
 * @package RRTV\Classes
 */

namespace RRTV;

defined( 'ABSPATH' ) || exit;

/**
 * This class is used to handle Vite dev server functionality.
 */
class Vite {

	/**
	 * Cache of Vite dev server status.
	 *
	 * @var bool|null
	 */
	private $vite_dev_server_running = null;

	/**
	 * Cache of Vite manifest data.
	 *
	 * @var array|null
	 */
	private $vite_manifest = null;

	/**
	 * Cache of Vite build base path ('' or 'dist/').
	 *
	 * @var string|null
	 */
	public static $vite_build_base_path = null;

	/**
	 * Script handles that must be rendered as ES modules.
	 *
	 * @var array
	 */
	private $module_script_handles = array();

	/**
	 * Inline script handles that must be rendered as ES modules.
	 *
	 * @var array
	 */
	private $inline_module_script_handles = array();

	/**
	 * Stack for component template output buffering.
	 *
	 * @var array
	 */
	private $component_template_buffer_stack = array();

	/**
	 * Cache of static component imports from frontend entry.
	 *
	 * @var array|null
	 */
	private $frontend_static_component_imports = null;

	/**
	 * Cache of static component imports from critical entry config.
	 *
	 * @var array|null
	 */
	private $critical_static_component_imports = null;

	/**
	 * Counter of template files requested via get_template_part().
	 *
	 * @var array
	 */
	private $get_template_part_template_loads = array();


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
		add_filter( 'script_loader_tag', array( $this, 'add_module_script_attribute' ), 10, 3 );
		add_filter( 'wp_inline_script_attributes', array( $this, 'add_module_inline_script_attribute' ), 10, 2 );
		add_action( 'get_template_part', array( $this, 'track_get_template_part_templates' ), 10, 4 );
		add_action( 'wp_before_load_template', array( $this, 'before_load_template' ), 10, 3 );
		add_action( 'wp_after_load_template', array( $this, 'after_load_template' ), 10, 3 );
	}

	/**
	 * Track template paths requested through get_template_part().
	 *
	 * @param string $slug      - The slug name for the generic template.
	 * @param string $name      - The name of the specialized template.
	 * @param array  $templates - Candidate template file names.
	 * @param array  $args      - Additional arguments passed to template.
	 *
	 * @return void
	 */
	public function track_get_template_part_templates( $slug, $name, $templates, $args ) {
		unset( $slug, $name, $args );

		if ( empty( $templates ) || ! is_array( $templates ) ) {
			return;
		}

		foreach ( $templates as $template_name ) {
			if ( ! is_string( $template_name ) || '' === $template_name ) {
				continue;
			}

			$resolved_template_path = locate_template( array( $template_name ), false, false );

			if ( ! is_string( $resolved_template_path ) || '' === $resolved_template_path ) {
				continue;
			}

			$normalized_template_path = wp_normalize_path( $resolved_template_path );

			if ( empty( $this->get_template_part_template_loads[ $normalized_template_path ] ) ) {
				$this->get_template_part_template_loads[ $normalized_template_path ] = 0;
			}

			++$this->get_template_part_template_loads[ $normalized_template_path ];
			break;
		}
	}

	/**
	 * Start output buffering for component templates loaded via get_template_part().
	 *
	 * @param string $_template_file - Template file path.
	 * @param bool   $load_once      - Whether to load once.
	 * @param array  $args           - Template args.
	 *
	 * @return void
	 */
	public function before_load_template( $_template_file, $load_once, $args ) {
		unset( $load_once, $args );

		if ( ! $this->should_inject_module_loading_class( $_template_file ) ) {
			return;
		}

		$this->component_template_buffer_stack[] = $_template_file;
		ob_start();
	}

	/**
	 * End output buffering and inject class for component templates.
	 *
	 * @param string $_template_file - Template file path.
	 * @param bool   $load_once      - Whether to load once.
	 * @param array  $args           - Template args.
	 *
	 * @return void
	 */
	public function after_load_template( $_template_file, $load_once, $args ) {
		unset( $load_once, $args );

		if ( empty( $this->component_template_buffer_stack ) ) {
			return;
		}

		$stack_last_index = count( $this->component_template_buffer_stack ) - 1;
		$active_template  = $this->component_template_buffer_stack[ $stack_last_index ];

		if ( $_template_file !== $active_template ) {
			return;
		}

		array_pop( $this->component_template_buffer_stack );

		$template_markup = ob_get_clean();

		if ( false === $template_markup ) {
			return;
		}

		echo $this->inject_module_loading_class_to_markup( $template_markup ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Check if current template should receive module loading class.
	 *
	 * @param string $template_file - Absolute template path.
	 *
	 * @return bool
	 */
	private function should_inject_module_loading_class( $template_file ) {
		if ( ! is_string( $template_file ) || '' === $template_file ) {
			return false;
		}

		$normalized_template_path  = wp_normalize_path( $template_file );
		$components_directory_path = wp_normalize_path( trailingslashit( RRTV_THEME_DIR_PATH ) . 'components/' );

		if ( 0 !== strpos( $normalized_template_path, $components_directory_path ) ) {
			return false;
		}

		if ( ! $this->consume_tracked_get_template_part_template( $normalized_template_path ) ) {
			return false;
		}

		$component_name = strtolower( basename( $normalized_template_path, '.php' ) );

		return $this->has_dynamic_component_chunk( $component_name );
	}

	/**
	 * Check whether component has dynamically loaded JS/CSS chunk.
	 *
	 * @param string $component_name - Component name (file name without extension).
	 *
	 * @return bool
	 */
	private function has_dynamic_component_chunk( $component_name ) {
		if ( ! is_string( $component_name ) || '' === $component_name ) {
			return false;
		}

		$component_name          = strtolower( $component_name );
		$critical_static_imports = $this->get_critical_static_component_imports();

		if ( ! empty( $critical_static_imports['js'][ $component_name ] ) || ! empty( $critical_static_imports['scss'][ $component_name ] ) ) {
			return false;
		}

		if ( ! $this->is_vite_dev_server_running() ) {
			$manifest = $this->get_vite_manifest();

			if ( ! empty( $manifest ) ) {
				$js_manifest_key   = 'src/js/components/' . $component_name . '.js';
				$scss_manifest_key = 'src/sass/components/' . $component_name . '.scss';

				$has_dynamic_js_chunk  = ! empty( $manifest[ $js_manifest_key ]['isDynamicEntry'] );
				$has_dynamic_css_chunk = ! empty( $manifest[ $scss_manifest_key ] );

				return $has_dynamic_js_chunk || $has_dynamic_css_chunk;
			}
		}

		$frontend_static_imports = $this->get_frontend_static_component_imports();
		$static_imports          = array(
			'js'   => array_merge( $frontend_static_imports['js'], $critical_static_imports['js'] ),
			'scss' => array_merge( $frontend_static_imports['scss'], $critical_static_imports['scss'] ),
		);

		$component_js_path   = trailingslashit( RRTV_THEME_DIR_PATH ) . 'src/js/components/' . $component_name . '.js';
		$component_scss_path = trailingslashit( RRTV_THEME_DIR_PATH ) . 'src/sass/components/' . $component_name . '.scss';

		$has_component_js_file   = is_readable( $component_js_path );
		$has_component_scss_file = is_readable( $component_scss_path );

		$has_dynamic_js_chunk  = $has_component_js_file && empty( $static_imports['js'][ $component_name ] );
		$has_dynamic_css_chunk = $has_component_scss_file && empty( $static_imports['scss'][ $component_name ] );

		return $has_dynamic_js_chunk || $has_dynamic_css_chunk;
	}

	/**
	 * Get static component imports from src/js/main.js.
	 *
	 * @return array
	 */
	private function get_frontend_static_component_imports() {
		if ( null !== $this->frontend_static_component_imports ) {
			return $this->frontend_static_component_imports;
		}

		$this->frontend_static_component_imports = array(
			'js'   => array(),
			'scss' => array(),
		);

		$frontend_entry_path = trailingslashit( RRTV_THEME_DIR_PATH ) . 'src/js/main.js';

		if ( ! is_readable( $frontend_entry_path ) ) {
			return $this->frontend_static_component_imports;
		}

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();

		global $wp_filesystem;

		if ( ! $wp_filesystem || ! $wp_filesystem->exists( $frontend_entry_path ) ) {
			return $this->frontend_static_component_imports;
		}

		$frontend_entry_content = $wp_filesystem->get_contents( $frontend_entry_path );

		if ( false === $frontend_entry_content || '' === $frontend_entry_content ) {
			return $this->frontend_static_component_imports;
		}

		$js_matches = array();

		preg_match_all(
			'/import\s+["\'](?:\.\/components\/|\.\.\/js\/components\/)([a-zA-Z0-9_-]+)\.js["\']\s*;/',
			$frontend_entry_content,
			$js_matches
		);

		if ( ! empty( $js_matches[1] ) && is_array( $js_matches[1] ) ) {
			foreach ( $js_matches[1] as $js_module_name ) {
				$this->frontend_static_component_imports['js'][ strtolower( $js_module_name ) ] = true;
			}
		}

		$scss_matches = array();

		preg_match_all(
			'/import\s+["\']\.\.\/sass\/components\/([a-zA-Z0-9_-]+)\.scss["\']\s*;/',
			$frontend_entry_content,
			$scss_matches
		);

		if ( ! empty( $scss_matches[1] ) && is_array( $scss_matches[1] ) ) {
			foreach ( $scss_matches[1] as $scss_module_name ) {
				$this->frontend_static_component_imports['scss'][ strtolower( $scss_module_name ) ] = true;
			}
		}

		return $this->frontend_static_component_imports;
	}

	/**
	 * Get static component imports from src/js/critical.js config.
	 *
	 * @return array
	 */
	private function get_critical_static_component_imports() {
		if ( null !== $this->critical_static_component_imports ) {
			return $this->critical_static_component_imports;
		}

		$this->critical_static_component_imports = array(
			'js'   => array(),
			'scss' => array(),
		);

		$critical_map_relative_paths = array_values(
			array_unique(
				array(
					self::get_vite_build_base_path() . '.vite/critical-component-map.json',
					'dist/.vite/critical-component-map.json',
					'.vite/critical-component-map.json',
				)
			)
		);

		$critical_map_path = '';

		foreach ( $critical_map_relative_paths as $critical_map_relative_path ) {
			$candidate_critical_map_path = trailingslashit( RRTV_THEME_DIR_PATH ) . ltrim( (string) $critical_map_relative_path, '/' );

			if ( is_readable( $candidate_critical_map_path ) ) {
				$critical_map_path = $candidate_critical_map_path;
				break;
			}
		}

		if ( '' === $critical_map_path ) {
			return $this->critical_static_component_imports;
		}

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();

		global $wp_filesystem;

		if ( ! $wp_filesystem || ! $wp_filesystem->exists( $critical_map_path ) ) {
			return $this->critical_static_component_imports;
		}

		$critical_map_raw = $wp_filesystem->get_contents( $critical_map_path );

		if ( false === $critical_map_raw || '' === $critical_map_raw ) {
			return $this->critical_static_component_imports;
		}

		$critical_component_map = json_decode( $critical_map_raw, true );

		if ( ! is_array( $critical_component_map ) || empty( $critical_component_map ) ) {
			return $this->critical_static_component_imports;
		}

		$template_candidates = $this->get_current_template_slug_candidates();

		foreach ( $template_candidates as $template_candidate ) {
			if ( empty( $critical_component_map[ $template_candidate ] ) || ! is_array( $critical_component_map[ $template_candidate ] ) ) {
				continue;
			}

			$critical_template_entry = $critical_component_map[ $template_candidate ];

			if ( ! empty( $critical_template_entry['js'] ) && is_array( $critical_template_entry['js'] ) ) {
				foreach ( $critical_template_entry['js'] as $js_component_name ) {
					$this->critical_static_component_imports['js'][ strtolower( (string) $js_component_name ) ] = true;
				}
			}

			if ( ! empty( $critical_template_entry['scss'] ) && is_array( $critical_template_entry['scss'] ) ) {
				foreach ( $critical_template_entry['scss'] as $scss_component_name ) {
					$this->critical_static_component_imports['scss'][ strtolower( (string) $scss_component_name ) ] = true;
				}
			}

			break;
		}

		return $this->critical_static_component_imports;
	}

	/**
	 * Get normalized current template slug candidates.
	 *
	 * @return array
	 */
	private function get_current_template_slug_candidates() {
		$template_slug_candidates = array();

		$queried_object_id = get_queried_object_id();

		if ( ! empty( $queried_object_id ) ) {
			$queried_template_slug = get_page_template_slug( $queried_object_id );

			if ( is_string( $queried_template_slug ) && '' !== $queried_template_slug ) {
				$template_slug_candidates[] = basename( $queried_template_slug, '.php' );
			}
		}

		$page_template_slug = get_page_template_slug();

		if ( is_string( $page_template_slug ) && '' !== $page_template_slug ) {
			$template_slug_candidates[] = basename( $page_template_slug, '.php' );
		}

		global $template;

		if ( is_string( $template ) && '' !== $template ) {
			$template_slug_candidates[] = basename( $template, '.php' );
		}

		if ( function_exists( 'get_body_class' ) ) {
			$body_classes = get_body_class();

			if ( is_array( $body_classes ) ) {
				foreach ( $body_classes as $body_class_name ) {
					if ( ! is_string( $body_class_name ) || 0 !== strpos( $body_class_name, 'page-template-' ) ) {
						continue;
					}

					$template_slug_candidates[] = preg_replace( '/-php$/', '', str_replace( 'page-template-', '', $body_class_name ) );
				}
			}
		}

		$normalized_candidates = array();

		foreach ( $template_slug_candidates as $template_slug_candidate ) {
			$normalized_slug = $this->normalize_template_slug( $template_slug_candidate );

			if ( '' !== $normalized_slug ) {
				$normalized_candidates[] = $normalized_slug;
			}
		}

		return array_values( array_unique( $normalized_candidates ) );
	}

	/**
	 * Normalize template slug to compare names safely.
	 *
	 * @param string $template_slug - Raw template slug.
	 *
	 * @return string
	 */
	private function normalize_template_slug( $template_slug ) {
		$normalized_slug = strtolower( str_replace( '_', '-', (string) $template_slug ) );
		$normalized_slug = preg_replace( '/[^a-z0-9-]/', '-', $normalized_slug );
		$normalized_slug = preg_replace( '/-+/', '-', (string) $normalized_slug );

		return trim( (string) $normalized_slug, '-' );
	}

	/**
	 * Consume tracked get_template_part() template load for current path.
	 *
	 * @param string $normalized_template_path - Normalized absolute template path.
	 *
	 * @return bool
	 */
	private function consume_tracked_get_template_part_template( $normalized_template_path ) {
		if ( empty( $this->get_template_part_template_loads[ $normalized_template_path ] ) ) {
			return false;
		}

		--$this->get_template_part_template_loads[ $normalized_template_path ];

		if ( $this->get_template_part_template_loads[ $normalized_template_path ] <= 0 ) {
			unset( $this->get_template_part_template_loads[ $normalized_template_path ] );
		}

		return true;
	}

	/**
	 * Inject class is-module-loading into the first HTML element.
	 *
	 * @param string $markup - Template HTML markup.
	 *
	 * @return string
	 */
	private function inject_module_loading_class_to_markup( $markup ) {
		if ( ! is_string( $markup ) || '' === trim( $markup ) ) {
			return $markup;
		}

		return preg_replace_callback(
			'/<([a-zA-Z][a-zA-Z0-9:-]*)(\s[^>]*)?>/',
			function ( $matches ) {
				$tag_name = $matches[1];
				$attrs    = isset( $matches[2] ) ? $matches[2] : '';

				if ( preg_match( '/\bclass\s*=\s*(["\'])(.*?)\1/', $attrs, $class_match ) ) {
					$class_value = $class_match[2];

					if ( false === strpos( $class_value, 'is-module-loading' ) ) {
						$new_class_attr = 'class=' . $class_match[1] . trim( $class_value . ' is-module-loading' ) . $class_match[1];
						$attrs          = preg_replace( '/\bclass\s*=\s*(["\'])(.*?)\1/', $new_class_attr, $attrs, 1 );
					}
				} else {
					$attrs .= ' class="is-module-loading"';
				}

				return '<' . $tag_name . $attrs . '>';
			},
			$markup,
			1
		);
	}

	/**
	 * Add type="module" to script tag for module handles.
	 *
	 * @param string $tag    - Script tag.
	 * @param string $handle - Script handle.
	 * @param string $src    - Script source.
	 *
	 * @return string
	 */
	public function add_module_script_attribute( $tag, $handle, $src ) {
		unset( $src );

		if ( empty( $this->module_script_handles[ $handle ] ) ) {
			return $tag;
		}

		if ( false !== strpos( $tag, 'type="text/javascript"' ) ) {
			return str_replace( 'type="text/javascript"', 'type="module"', $tag );
		}

		if ( false !== strpos( $tag, "type='text/javascript'" ) ) {
			return str_replace( "type='text/javascript'", "type='module'", $tag );
		}

		if ( false !== strpos( $tag, ' type=' ) ) {
			return $tag;
		}

		return str_replace( '<script ', '<script type="module" ', $tag );
	}

	/**
	 * Add type="module" to inline script tags for registered inline module handles.
	 *
	 * @param array  $attributes - Inline script tag attributes.
	 * @param string $data       - Inline script content.
	 *
	 * @return array
	 */
	public function add_module_inline_script_attribute( $attributes, $data ) {
		unset( $data );

		if ( empty( $attributes['id'] ) || ! is_string( $attributes['id'] ) ) {
			return $attributes;
		}

		if ( ! preg_match( '/^(?<handle>.+)-js-(?:before|after|extra)$/', $attributes['id'], $matches ) ) {
			return $attributes;
		}

		$handle = $matches['handle'];

		if ( empty( $this->inline_module_script_handles[ $handle ] ) ) {
			return $attributes;
		}

		$attributes['type'] = 'module';

		return $attributes;
	}

	/**
	 * Get Vite dev server URL.
	 *
	 * @return string
	 */
	private function get_vite_dev_server_url() {
		if ( defined( 'RRTV_VITE_DEV_SERVER_URL' ) ) {
			$vite_dev_server_url = constant( 'RRTV_VITE_DEV_SERVER_URL' );

			if ( ! empty( $vite_dev_server_url ) ) {
				return untrailingslashit( $vite_dev_server_url );
			}
		}

		return 'http://localhost:5173';
	}

	/**
	 * Check if Vite dev server is available.
	 *
	 * @return bool
	 */
	public function is_vite_dev_server_running() {
		if ( null !== $this->vite_dev_server_running ) {
			return $this->vite_dev_server_running;
		}

		$response = wp_remote_get(
			$this->get_vite_dev_server_url() . '/@vite/client',
			array(
				'timeout'   => 0.6,
				'sslverify' => false,
			)
		);

		$this->vite_dev_server_running = ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response );

		return $this->vite_dev_server_running;
	}

	/**
	 * Get Vite manifest data.
	 *
	 * @return array
	 */
	private function get_vite_manifest() {
		if ( null !== $this->vite_manifest ) {
			return $this->vite_manifest;
		}

		$manifest_path = trailingslashit( RRTV_THEME_DIR_PATH ) . $this->get_vite_build_base_path() . '.vite/manifest.json';

		if ( ! is_readable( $manifest_path ) ) {
			$this->vite_manifest = array();
			return $this->vite_manifest;
		}

		if ( function_exists( 'wp_json_file_decode' ) ) {
			$manifest            = wp_json_file_decode( $manifest_path, array( 'associative' => true ) );
			$this->vite_manifest = is_array( $manifest ) ? $manifest : array();
			return $this->vite_manifest;
		}

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();

		global $wp_filesystem;

		if ( ! $wp_filesystem || ! $wp_filesystem->exists( $manifest_path ) ) {
			$this->vite_manifest = array();
			return $this->vite_manifest;
		}

		$manifest_raw = $wp_filesystem->get_contents( $manifest_path );

		if ( false === $manifest_raw || '' === $manifest_raw ) {
			$this->vite_manifest = array();
			return $this->vite_manifest;
		}

		$manifest = json_decode( $manifest_raw, true );

		$this->vite_manifest = is_array( $manifest ) ? $manifest : array();

		return $this->vite_manifest;
	}

	/**
	 * Get Vite build base path.
	 *
	 * @return string
	 */
	private static function get_vite_build_base_path() {
		if ( null !== self::$vite_build_base_path ) {
			return self::$vite_build_base_path;
		}

		$theme_path = trailingslashit( RRTV_THEME_DIR_PATH );

		if ( is_readable( $theme_path . '.vite/manifest.json' ) ) {
			self::$vite_build_base_path = '';
			return self::$vite_build_base_path;
		}

		self::$vite_build_base_path = 'dist/';

		return self::$vite_build_base_path;
	}

	/**
	 * Get Vite asset relative path from theme root.
	 *
	 * @param string $relative_path - Relative asset path.
	 *
	 * @return string
	 */
	public static function get_vite_asset_relative_path( $relative_path ) {
		return self::get_vite_build_base_path() . ltrim( $relative_path, '/' );
	}

	/**
	 * Get Vite asset full URL.
	 *
	 * @param string $relative_path - Relative asset path.
	 *
	 * @return string
	 */
	public static function get_vite_asset_url( $relative_path ) {
		return trailingslashit( RRTV_THEME_DIR_URL ) . self::get_vite_asset_relative_path( $relative_path );
	}

	/**
	 * Collect CSS files for Vite manifest entry (including imported chunks).
	 *
	 * @param array  $manifest  - Vite manifest.
	 * @param string $entry_key - Entry key.
	 * @param array  $css_files - CSS files accumulator.
	 * @param array  $visited   - Visited entries.
	 *
	 * @return void
	 */
	private function collect_vite_css_from_manifest_entry( $manifest, $entry_key, &$css_files, &$visited ) {
		if ( isset( $visited[ $entry_key ] ) || empty( $manifest[ $entry_key ] ) || ! is_array( $manifest[ $entry_key ] ) ) {
			return;
		}

		$visited[ $entry_key ] = true;
		$entry_data            = $manifest[ $entry_key ];

		if ( ! empty( $entry_data['css'] ) && is_array( $entry_data['css'] ) ) {
			foreach ( $entry_data['css'] as $css_file ) {
				if ( ! in_array( $css_file, $css_files, true ) ) {
					$css_files[] = $css_file;
				}
			}
		}

		if ( ! empty( $entry_data['imports'] ) && is_array( $entry_data['imports'] ) ) {
			foreach ( $entry_data['imports'] as $import_entry_key ) {
				$this->collect_vite_css_from_manifest_entry( $manifest, $import_entry_key, $css_files, $visited );
			}
		}
	}

	/**
	 * Mark script handle as module.
	 *
	 * @param string $handle - Script handle.
	 *
	 * @return void
	 */
	private function mark_script_handle_as_module( $handle ) {
		$this->module_script_handles[ $handle ] = true;
		wp_script_add_data( $handle, 'type', 'module' );
	}

	/**
	 * Register a script handle to be rendered as type="module".
	 *
	 * @param string $handle - Script handle.
	 *
	 * @return void
	 */
	public function register_module_script_handle( $handle ) {
		if ( ! is_string( $handle ) || '' === $handle ) {
			return;
		}

		$this->mark_script_handle_as_module( $handle );
	}

	/**
	 * Register an inline script handle to be rendered as type="module".
	 *
	 * @param string $handle - Script handle.
	 *
	 * @return void
	 */
	public function register_inline_module_script_handle( $handle ) {
		if ( ! is_string( $handle ) || '' === $handle ) {
			return;
		}

		$this->inline_module_script_handles[ $handle ] = true;
	}

	/**
	 * Enqueue Vite entry for dev or build mode.
	 *
	 * @param string $handle       - Script handle.
	 * @param string $entry_key    - Vite entry key.
	 * @param array  $dependencies - Script dependencies.
	 * @param bool   $in_footer    - Load in footer.
	 *
	 * @return bool
	 */
	public function enqueue_vite_entry( $handle, $entry_key, $dependencies = array(), $in_footer = true ) {
		if ( $this->is_vite_dev_server_running() ) {
			$dev_client_handle = Riga_Revival_Tv::$theme_prefix_lowercase . '-vite-client';

			if ( ! wp_script_is( $dev_client_handle, 'enqueued' ) ) {
				wp_enqueue_script( $dev_client_handle, $this->get_vite_dev_server_url() . '/@vite/client', array(), RRTV_THEME_VERSION, false );
				$this->mark_script_handle_as_module( $dev_client_handle );
			}

			$dev_entry_url = $this->get_vite_dev_server_url() . '/' . ltrim( $entry_key, '/' );
			wp_enqueue_script( $handle, $dev_entry_url, array_merge( array( $dev_client_handle ), $dependencies ), RRTV_THEME_VERSION, $in_footer );
			$this->mark_script_handle_as_module( $handle );

			return true;
		}

		$manifest = $this->get_vite_manifest();

		if ( empty( $manifest[ $entry_key ] ) || ! is_array( $manifest[ $entry_key ] ) ) {
			return false;
		}

		$manifest_entry = $manifest[ $entry_key ];

		if ( empty( $manifest_entry['file'] ) ) {
			return false;
		}

		$css_files = array();
		$visited   = array();
		$this->collect_vite_css_from_manifest_entry( $manifest, $entry_key, $css_files, $visited );

		foreach ( $css_files as $css_index => $css_file ) {
			$css_handle = $handle . '-style-' . $css_index;
			$css_url    = self::get_vite_asset_url( $css_file );
			wp_enqueue_style( $css_handle, $css_url, array(), RRTV_THEME_VERSION );
		}

		$script_url = self::get_vite_asset_url( $manifest_entry['file'] );
		wp_enqueue_script( $handle, $script_url, $dependencies, RRTV_THEME_VERSION, $in_footer );
		$this->mark_script_handle_as_module( $handle );

		return true;
	}

	/**
	 * Get CSS files for a Vite manifest entry, including imported chunks.
	 *
	 * @param string $entry_key - Vite manifest entry key.
	 *
	 * @return array
	 */
	public function get_vite_entry_css_files( $entry_key ) {
		if ( ! is_string( $entry_key ) || '' === $entry_key ) {
			return array();
		}

		$manifest = $this->get_vite_manifest();

		if ( empty( $manifest[ $entry_key ] ) || ! is_array( $manifest[ $entry_key ] ) ) {
			return array();
		}

		$css_files = array();
		$visited   = array();
		$this->collect_vite_css_from_manifest_entry( $manifest, $entry_key, $css_files, $visited );

		return $css_files;
	}

	/**
	 * Enqueue a single script file (dev server or built asset).
	 *
	 * @param string $handle        - Script handle.
	 * @param string $relative_path - Relative path to the script.
	 * @param array  $dependencies  - Script dependencies.
	 * @param bool   $in_footer     - Load in footer.
	 *
	 * @return bool
	 */
	public function enqueue_script_file( $handle, $relative_path, $dependencies = array(), $in_footer = true ) {
		if ( $this->is_vite_dev_server_running() ) {
			$script_url = $this->get_vite_dev_server_url() . '/' . ltrim( $relative_path, '/' );
			wp_enqueue_script( $handle, $script_url, $dependencies, RRTV_THEME_VERSION, $in_footer );
			$this->mark_script_handle_as_module( $handle );
			return true;
		}

		$manifest = $this->get_vite_manifest();

		if ( empty( $manifest[ $relative_path ] ) || ! is_array( $manifest[ $relative_path ] ) ) {
			return false;
		}

		$manifest_entry = $manifest[ $relative_path ];

		if ( empty( $manifest_entry['file'] ) ) {
			return false;
		}

		$script_url = self::get_vite_asset_url( $manifest_entry['file'] );
		wp_enqueue_script( $handle, $script_url, $dependencies, RRTV_THEME_VERSION, $in_footer );
		$this->mark_script_handle_as_module( $handle );

		return true;
	}

	/**
	 * Enqueue a single stylesheet file (dev server or built asset).
	 *
	 * @param string $handle        - Style handle.
	 * @param string $relative_path - Relative path to the stylesheet.
	 * @param array  $dependencies  - Style dependencies.
	 * @param string $media         - Media attribute.
	 *
	 * @return bool
	 */
	public function enqueue_style_file( $handle, $relative_path, $dependencies = array(), $media = 'all' ) {
		if ( $this->is_vite_dev_server_running() ) {
			$style_url = $this->get_vite_dev_server_url() . '/' . ltrim( $relative_path, '/' );
			wp_enqueue_style( $handle, $style_url, $dependencies, RRTV_THEME_VERSION, $media );
			return true;
		}

		$manifest = $this->get_vite_manifest();

		if ( empty( $manifest[ $relative_path ] ) || ! is_array( $manifest[ $relative_path ] ) ) {
			return false;
		}

		$manifest_entry = $manifest[ $relative_path ];

		if ( empty( $manifest_entry['file'] ) ) {
			return false;
		}

		$style_url = self::get_vite_asset_url( $manifest_entry['file'] );
		wp_enqueue_style( $handle, $style_url, $dependencies, RRTV_THEME_VERSION, $media );
		return true;
	}
}
