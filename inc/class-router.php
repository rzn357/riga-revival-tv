<?php
/**
 * Router handler class.
 *
 * @package RRTV\Classes
 */

namespace RRTV;

defined( 'ABSPATH' ) || exit;

/**
 * This class is used to handle Router requests.
 */
class Router {

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
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
		add_filter( 'template_include', array( $this, 'load_template' ) );
		add_filter( 'body_class', array( $this, 'add_custom_body_class' ) );
	}

	/**
	 * Add custom body class based on the route.
	 *
	 * @param array $classes - Existing body classes.
	 *
	 * @return array
	 */
	public function add_custom_body_class( array $classes ) {
		$route = get_query_var( 'rrtv_route' );

		if ( $route ) {
			$classes[] = 'page-template-' . sanitize_html_class( $route );
		}

		return $classes;
	}

	/**
	 * Add rewrite rules.
	 *
	 * @return void
	 */
	public function add_rewrite_rules() {

		$page = get_page_by_path( 'videos', OBJECT, 'page' );
		if ( $page ) {
			add_rewrite_rule(
				'^videos/([A-Za-z0-9_-]+)/([A-Za-z0-9_-]+)/?$',
				'index.php?rrtv_route=videos_program&rrtv_video_section=$matches[1]&rrtv_video_program=$matches[2]',
				'top'
			);

			add_rewrite_rule(
				'^videos/([A-Za-z0-9_-]{11})/?$',
				'index.php?rrtv_route=videos_single&rrtv_id=$matches[1]',
				'top'
			);
		}
	}

	/**
	 * Register query variables.
	 *
	 * @param array $vars - Existing query vars.
	 *
	 * @return array
	 */
	public function register_query_vars( array $vars ) {
		$vars[] = 'rrtv_route';
		$vars[] = 'rrtv_id';
		$vars[] = 'rrtv_video_section';
		$vars[] = 'rrtv_video_program';
		return $vars;
	}

	/**
	 * Load custom templates based on the route.
	 *
	 * @param string $template - Current template.
	 *
	 * @return string
	 */
	public function load_template( string $template ) {
		$route = get_query_var( 'rrtv_route' );

		if ( 'videos_program' === $route ) {
			return get_template_directory() . '/templates/videos-program.php';
		}

		if ( 'videos_single' === $route ) {
			return get_template_directory() . '/templates/videos-single.php';
		}

		return $template;
	}
}
