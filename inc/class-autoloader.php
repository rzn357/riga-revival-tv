<?php
/**
 * Classes Autoloader.
 *
 * @package RRTV
 * @version 1.0.0
 */

namespace RRTV;

defined( 'ABSPATH' ) || exit;

/**
 * Autoloader class.
 */
class Autoloader {


	/**
	 * Theme main namespace.
	 *
	 * @var string
	 */
	private $theme_namespace = 'RRTV';

	/**
	 * Path to the inc directory.
	 *
	 * @var string
	 */
	private $inc_path = '';

	/**
	 * The Constructor.
	 */
	public function __construct() {

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->inc_path = untrailingslashit( get_template_directory() ) . '/inc';
	}

	/**
	 * Take a class name and turn it into a file name.
	 *
	 * @param  string $class_name - Class name.
	 * @param  int    $len        - Length of the main namespace.
	 *
	 * @return string
	 */
	private function get_file_path_from_class_name( $class_name, $len ) {
		$class_name = strtolower( $class_name );

		$relative_class      = substr( $class_name, $len );
		$relative_class_path = str_replace( '\\', '/', $relative_class );
		$relative_class_name = basename( $relative_class_path );

		if ( str_contains( $class_name, '\\abstracts\\' ) ) {
			$folder_path = '/abstracts/';
			$file_prefix = 'abstract';
		} elseif ( str_contains( $class_name, '\\interfaces\\' ) ) {
			$folder_path = '/interfaces/';
			$file_prefix = 'interface';
		} elseif ( str_contains( $class_name, '\\traits\\' ) ) {
			$folder_path = '/traits/';
			$file_prefix = 'trait';
		} else {
			$folder_path = dirname( $relative_class_path ) . '/';
			$folder_path = str_replace( '.', '', $folder_path );
			$folder_path = str_replace( '\\', '', $folder_path );
			$folder_path = str_replace( '_', '-', $folder_path );
			$file_prefix = 'class';
		}

		return $folder_path . $file_prefix . '-' . str_replace( '_', '-', $relative_class_name ) . '.php';
	}

	/**
	 * Include a class file.
	 *
	 * @param  string $path - File path.
	 *
	 * @return bool Successful or not.
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once $path;
			return true;
		}
		return false;
	}

	/**
	 * Normalize incoming class name for autoload resolution.
	 *
	 * @param  string $class_name - Raw class name.
	 *
	 * @return string
	 */
	private function normalize_class_name( $class_name ) {
		$class_name = ltrim( (string) $class_name, '\\' );

		if ( str_ends_with( $class_name, '[]' ) ) {
			$class_name = substr( $class_name, 0, -2 );
		}

		return $class_name;
	}

	/**
	 * Get possible relative file paths for mixed underscore/hyphen directory names.
	 *
	 * @param  string $file_path - Relative file path.
	 *
	 * @return array
	 */
	private function get_candidate_file_paths( $file_path ) {
		$directory = dirname( $file_path );
		$file_name = basename( $file_path );

		if ( '.' === $directory || '/' === $directory ) {
			return array( $file_path );
		}

		$directory = trim( str_replace( '\\', '/', $directory ), '/' );
		$segments  = array_filter( explode( '/', $directory ) );
		$variants  = array( '' );

		foreach ( $segments as $segment ) {
			$options = array( $segment );

			if ( str_contains( $segment, '_' ) ) {
				$options[] = str_replace( '_', '-', $segment );
			}

			if ( str_contains( $segment, '-' ) ) {
				$options[] = str_replace( '-', '_', $segment );
			}

			$next_variants = array();
			foreach ( $variants as $variant ) {
				foreach ( $options as $option ) {
					$next_variants[] = '' === $variant ? $option : $variant . '/' . $option;
				}
			}

			$variants = $next_variants;
		}

		$candidate_paths = array();
		foreach ( $variants as $variant ) {
			$candidate_paths[] = '/' . trim( $variant, '/' ) . '/' . $file_name;
		}

		return array_values( array_unique( $candidate_paths ) );
	}

	/**
	 * Auto-load classes.
	 *
	 * @param string $class_name - Class name.
	 */
	public function autoload( $class_name ) {
		$class_name = $this->normalize_class_name( $class_name );

		if ( '' === $class_name || str_contains( $class_name, '[' ) || str_contains( $class_name, ']' ) ) {
			return;
		}

		// Checking if a class belongs to our namespace.
		$len = strlen( $this->theme_namespace );
		if ( strncmp( $this->theme_namespace, $class_name, $len ) !== 0 ) {
			return;
		}

		$file_path       = $this->get_file_path_from_class_name( $class_name, $len );
		$candidate_paths = $this->get_candidate_file_paths( $file_path );

		foreach ( $candidate_paths as $candidate_path ) {
			if ( $this->load_file( $this->inc_path . $candidate_path ) ) {
				return;
			}
		}
	}
}

new Autoloader();
