<?php
/**
 * WP performance optimizer class.
 *
 * @package RRTV\Classes
 */

namespace RRTV\Optimizers;

use RRTV\Vite;

defined( 'ABSPATH' ) || exit;

/**
 * This class is used to optimize WordPress performance.
 */
class WP_Performance_Optimizer {

	/**
	 * Vite instance.
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
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'unload_scripts_and_styles' ), 999 );
		add_action( 'script_loader_tag', array( $this, 'defer_scripts_handler' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'move_scripts_to_footer' ), 999 );
		add_filter( 'wp_resource_hints', array( $this, 'add_preconnect_resource_hints' ), 10, 2 );
		add_filter( 'style_loader_tag', array( $this, 'make_critical_styles_non_render_blocking' ), 10, 4 );
		add_filter( 'style_loader_tag', array( $this, 'make_noncritical_styles_non_render_blocking' ), 10, 4 );
		add_action( 'init', array( $this, 'remove_wp_emoji' ) );
		add_action( 'wp_head', array( $this, 'preload_images' ), 2 );
		add_action( 'wp_head', array( $this, 'preload_scripts' ), 3 );
		add_action( 'wp_print_footer_scripts', array( $this, 'late_unload_give_assets' ), 0 );
		add_filter( 'style_loader_tag', array( $this, 'block_give_styles_on_donate_page' ), 1, 2 );
	}

	/**
	 * Preload scripts provided via filter.
	 *
	 * Use the `brmj_preload_scripts` filter to return an array of scripts to preload.
	 * Each item can be:
	 *  - URL string (e.g. 'https://example.com/app.js')
	 *  - Handle string (e.g. 'jquery')
	 *  - Associative array with:
	 *      - 'href' (string) direct script URL, or
	 *      - 'handle' (string) registered script handle
	 *      - 'crossorigin' (string|null) optional
	 *      - 'integrity' (string) optional
	 *      - 'referrerpolicy' (string) optional
	 *
	 * @return void
	 */
	public function preload_scripts() {

		if ( is_admin() ) {
			return;
		}

		$preload_entries = array( 'jquery-core' );

		if ( empty( $preload_entries ) || ! is_array( $preload_entries ) ) {
			return;
		}

		$printed_hrefs = array();

		foreach ( $preload_entries as $entry ) {
			$href         = '';
			$attr_strings = array();

			if ( is_string( $entry ) ) {
				if ( wp_http_validate_url( $entry ) || 0 === strpos( $entry, '/' ) ) {
					$href = $entry;
				} else {
					$href = $this->get_script_src_by_handle( $entry );
				}
			} elseif ( is_array( $entry ) ) {
				if ( ! empty( $entry['href'] ) ) {
					$href = $entry['href'];
				} elseif ( ! empty( $entry['handle'] ) ) {
					$href = $this->get_script_src_by_handle( $entry['handle'] );
				}

				if ( array_key_exists( 'crossorigin', $entry ) && '' !== $entry['crossorigin'] ) {
					$attr_strings[] = 'crossorigin="' . esc_attr( $entry['crossorigin'] ) . '"';
				}

				if ( ! empty( $entry['integrity'] ) ) {
					$attr_strings[] = 'integrity="' . esc_attr( $entry['integrity'] ) . '"';
				}

				if ( ! empty( $entry['referrerpolicy'] ) ) {
					$attr_strings[] = 'referrerpolicy="' . esc_attr( $entry['referrerpolicy'] ) . '"';
				}
			}

			if ( empty( $href ) ) {
				continue;
			}

			$href = esc_url_raw( $href );

			if ( empty( $href ) || isset( $printed_hrefs[ $href ] ) ) {
				continue;
			}

			$printed_hrefs[ $href ] = true;

			$attrs = '';
			if ( ! empty( $attr_strings ) ) {
				$attrs = ' ' . implode( ' ', $attr_strings );
			}

			echo '<link rel="preload" as="script" href="' . esc_url( $href ) . '"' . $attrs . '>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Resolve script URL by script handle.
	 *
	 * @param string $handle - Script handle.
	 *
	 * @return string
	 */
	private function get_script_src_by_handle( $handle ) {
		$wp_scripts = wp_scripts();

		if ( ! $wp_scripts || empty( $wp_scripts->registered[ $handle ] ) ) {
			return '';
		}

		$registered_script = $wp_scripts->registered[ $handle ];

		if ( empty( $registered_script->src ) ) {
			return '';
		}

		$src = $registered_script->src;

		if ( 0 === strpos( $src, '//' ) ) {
			$src = ( is_ssl() ? 'https:' : 'http:' ) . $src;
		} elseif ( 0 === strpos( $src, '/' ) ) {
			$src = home_url( $src );
		} elseif ( ! preg_match( '#^(https?:)?//#', $src ) ) {
			$src = trailingslashit( $wp_scripts->base_url ) . ltrim( $src, '/' );
		}

		if ( isset( $registered_script->ver ) && null !== $registered_script->ver ) {
			$src = add_query_arg( 'ver', (string) $registered_script->ver, $src );
		}

		return $src;
	}

	/**
	 * Preload images provided via filter.
	 *
	 * Use the `brmj_preload_images` filter to return an array of images to preload.
	 * Each item can be either a string URL or an associative array with keys:
	 *  - 'href' (string) required when using the array form
	 *  - 'imagesrcset' (string) optional
	 *  - 'imagesizes' (string) optional
	 *  - 'crossorigin' (string|null) optional
	 *  - 'fetchpriority' (string) optional (e.g. 'high')
	 *
	 * @return void
	 */
	public function preload_images() {

		if ( is_admin() ) {
			return;
		}

		$images = array();

		if ( is_page_template( 'donate.php' ) ) {
			$rrtv_background_image_id = get_field( 'hero_banner_two_background_image' );

			if ( $rrtv_background_image_id ) {
				$images[] = array(
					'href'          => wp_get_attachment_url( $rrtv_background_image_id ),
					'imagesrcset'   => wp_get_attachment_image_srcset( $rrtv_background_image_id, 'full' ),
					'imagesizes'    => wp_get_attachment_image_sizes( $rrtv_background_image_id, 'full' ),
					'fetchpriority' => 'high',
				);
			}
		}

		if ( is_page_template( 'homepage.php' ) ) {
			$rrtv_background_image_id = get_field( 'hero_banner_background_image' );
			$rrtv_image_id            = get_field( 'hero_banner_image' );

			if ( $rrtv_background_image_id ) {
				$images[] = array(
					'href'          => wp_get_attachment_url( $rrtv_background_image_id ),
					'imagesrcset'   => wp_get_attachment_image_srcset( $rrtv_background_image_id, 'full' ),
					'imagesizes'    => wp_get_attachment_image_sizes( $rrtv_background_image_id, 'full' ),
					'fetchpriority' => 'high',
				);
			}

			if ( $rrtv_image_id ) {
				$images[] = array(
					'href'          => wp_get_attachment_url( $rrtv_image_id ),
					'fetchpriority' => 'high',
				);
			}
		}

		if ( is_page_template( 'watch-live.php' ) ) {
			$images[] = 'https://player-assets.restream.io/thumbnails/de/fa/85/f7/f854/4cf9/b9a5/8bc1babbc007.png';
		}

		if ( is_page_template( 'videos.php' ) ) {
			$rrtv_video_sections = get_field( 'video_sections' );

			foreach ( $rrtv_video_sections as $rrtv_section ) {

				if ( $rrtv_section['programs'] ) {
					foreach ( $rrtv_section['programs'] as $rrtv_index => $rrtv_program ) {
						if ( $rrtv_program['thumbnail'] ) {
							$images[] = array(
								'href'        => wp_get_attachment_url( $rrtv_program['thumbnail'] ),
								'imagesrcset' => wp_get_attachment_image_srcset( $rrtv_program['thumbnail'], 'full' ),
								'imagesizes'  => wp_get_attachment_image_sizes( $rrtv_program['thumbnail'], 'full' ),
							);
						}
						break;
					}
				}

				break;
			}
		}

		if ( empty( $images ) || ! is_array( $images ) ) {
			return;
		}

		foreach ( $images as $image ) {
			$href         = '';
			$attr_strings = array();

			if ( is_string( $image ) ) {
				$href = $image;
			} elseif ( is_array( $image ) && isset( $image['href'] ) ) {
				$href = $image['href'];

				if ( isset( $image['imagesrcset'] ) ) {
					$attr_strings[] = 'imagesrcset="' . esc_attr( $image['imagesrcset'] ) . '"';
				}

				if ( isset( $image['imagesizes'] ) ) {
					$attr_strings[] = 'imagesizes="' . esc_attr( $image['imagesizes'] ) . '"';
				}

				if ( array_key_exists( 'crossorigin', $image ) && '' !== $image['crossorigin'] ) {
					$attr_strings[] = 'crossorigin="' . esc_attr( $image['crossorigin'] ) . '"';
				}

				if ( isset( $image['fetchpriority'] ) ) {
					$attr_strings[] = 'fetchpriority="' . esc_attr( $image['fetchpriority'] ) . '"';
				}
			}

			if ( empty( $href ) ) {
				continue;
			}

			$attrs = '';
			if ( ! empty( $attr_strings ) ) {
				$attrs = ' ' . implode( ' ', $attr_strings );
			}

			echo '<link rel="preload" as="image" href="' . esc_url( $href ) . '"' . $attrs . '>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Remove WP Emoji scripts and styles.
	 *
	 * @return void
	 */
	public function remove_wp_emoji() {

		// Frontend.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}

	/**
	 * Preload specific styles (non-critical CSS).
	 *
	 * Download Priority: Low
	 * Browser Support: Almost All Browsers
	 *
	 * @param string $html   - The link tag HTML.
	 * @param string $handle - The style handle.
	 * @param string $href   - The style URL.
	 * @param string $media  - The media attribute.
	 *
	 * @return string Modified - link tag HTML.
	 */
	public function make_noncritical_styles_non_render_blocking( $html, $handle, $href, $media ) {

		if ( is_admin() ) {
			return $html;
		}

		$async_styles = array(
			'accessibility-onetap-fonts-readable',
			'accessibility-onetap',
			'contact-form-7',
		);

		if ( in_array( $handle, $async_styles, true ) ) {
			$html = '<link rel="stylesheet" id="' . esc_attr( $handle ) . '-css" href="' . esc_url( $href ) . '" media="print" onload="this.media=\'all\'">';  // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet

			// fallback if JS disabled.
			$html .= '<noscript><link rel="stylesheet" id="' . esc_attr( $handle ) . '-css-noscript" href="' . esc_url( $href ) . '" media="' . esc_attr( $media ) . '"></noscript>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		}

		return $html;
	}

	/**
	 * Preload specific styles (critical CSS).
	 *
	 * Download Priority: high
	 * Browser Support: Modern Browsers
	 *
	 * @param string $html   - The link tag HTML.
	 * @param string $handle - The style handle.
	 * @param string $href   - The style URL.
	 * @param string $media  - The media attribute.
	 *
	 * @return string Modified - link tag HTML.
	 */
	public function make_critical_styles_non_render_blocking( $html, $handle, $href, $media ) {

		if ( is_admin() ) {
			return $html;
		}

		$preload_styles = array(
			'rrtv-scripts-style-0',
		);

		if ( in_array( $handle, $preload_styles, true ) ) {

			$html = '<link rel="preload" id="' . esc_attr( $handle ) . '-css" href="' . esc_url( $href ) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" media="' . esc_attr( $media ) . '" fetchpriority="high">';

			// fallback if JS disabled.
			$html .= '<noscript><link rel="stylesheet" id="' . esc_attr( $handle ) . '-css-noscript" href="' . esc_url( $href ) . '" media="' . esc_attr( $media ) . '"></noscript>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		}

		return $html;
	}

	/**
	 * Add preconnect resource hints.
	 *
	 * Add URLs to the array below to output <link rel="preconnect"> tags.
	 * Strings are supported, and array items can be used when extra attributes
	 * such as crossorigin are needed.
	 *
	 * Example:
	 * $preconnect_urls = array(
	 *     'https://example.com',
	 *     array(
	 *         'href'        => 'https://fonts.gstatic.com',
	 *         'crossorigin' => 'anonymous',
	 *     ),
	 * );
	 *
	 * @param array  $urls          - Existing resource hints.
	 * @param string $relation_type - The relation type.
	 *
	 * @return array Modified resource hints.
	 */
	public function add_preconnect_resource_hints( $urls, $relation_type ) {

		if ( is_admin() || 'preconnect' !== $relation_type ) {
			return $urls;
		}

		$preconnect_urls = array();

		if ( is_page_template( 'watch-live.php' ) ) {
			$preconnect_urls[] = 'https://player.restream.io';
			$preconnect_urls[] = 'https://player-backend.restream.io';
			$preconnect_urls[] = 'https://player-assets.restream.io';
			$preconnect_urls[] = 'https://restream.io';
		}

		$route = get_query_var( 'rrtv_route' );
		if ( 'videos_single' === $route ) {
			$preconnect_urls[] = 'https://www.youtube.com';
		}

		foreach ( $preconnect_urls as $preconnect_url ) {
			if ( is_array( $preconnect_url ) && ! empty( $preconnect_url['href'] ) ) {
				$urls[] = $preconnect_url;
				continue;
			}

			if ( is_string( $preconnect_url ) && ! empty( $preconnect_url ) ) {
				$urls[] = $preconnect_url;
			}
		}

		return $urls;
	}

	/**
	 * Move specific scripts to the footer.
	 *
	 * @return void
	 */
	public function move_scripts_to_footer() {
		wp_script_add_data( 'jquery', 'group', 1 );
		wp_script_add_data( 'jquery-core', 'group', 1 );
		wp_script_add_data( 'jquery-migrate', 'group', 1 );
		wp_script_add_data( 'wp-hooks', 'group', 1 );
		wp_script_add_data( 'wp-i18n', 'group', 1 );
	}

	/**
	 * Handler to defer specific scripts.
	 *
	 * @param string $tag    - The script tag.
	 * @param string $handle - The script handle.
	 *
	 * @return string Modified - script tag.
	 */
	public function defer_scripts_handler( $tag, $handle ) {

		$general_defer_scripts = array( 'cookie-law-info', 'jquery-migrate', 'remove-weak-pw', 'accessibility-widget', 'swv', 'brmj-scripts', 'wp-statistics-tracker' );

		return $this->defer_scripts( $general_defer_scripts, $tag, $handle );
	}

	/**
	 * Defer specific scripts.
	 *
	 * @param array  $defer_scripts - Array of script handles to defer.
	 * @param string $tag           - The script tag HTML.
	 * @param string $handle        - The script handle.
	 *
	 * @return string Modified script tag HTML.
	 */
	public function defer_scripts( $defer_scripts, $tag, $handle ) {
		if ( in_array( $handle, $defer_scripts, true ) ) {
			return str_replace( '<script ', '<script defer ', $tag );
		}

		return $tag;
	}

	/**
	 * Unload unnecessary scripts and styles.
	 *
	 * @return void
	 */
	public function unload_scripts_and_styles() {

		if ( ! is_admin() ) {
			$this->unload_script( 'wp-blocks' );
			$this->unload_script( 'wp-block-editor' );
			$this->unload_script( 'wp-components' );
			$this->unload_script( 'wp-date' );
			$this->unload_script( 'give' );

			$this->unload_styles( 'givewp-design-system-foundation' );
			$this->unload_styles( 'give-styles' );
			$this->unload_styles( 'givewp-campaign-blocks-fonts' );
		}

		if ( ! is_admin() && ! is_page_template( 'contacts.php' ) ) {
			$this->unload_script( 'contact-form-7' );
			$this->unload_script( 'swv' );

			$this->unload_styles( 'contact-form-7' );
		}
	}

	/**
	 * Late-dequeue Give WP scripts that are re-enqueued by the shortcode during template rendering.
	 *
	 * The Give WP shortcode runs during template output (after wp_enqueue_scripts),
	 * and can re-register/re-enqueue scripts. This hook runs just before footer
	 * scripts are printed, catching anything the shortcode added.
	 *
	 * @return void
	 */
	public function late_unload_give_assets() {

		if ( is_admin() || ! is_page_template( 'donate.php' ) ) {
			return;
		}

		$give_script_handles = array(
			'give',
			'givewp-donation-form-embed-app',
			'givewp-entities-public',
		);

		$give_style_handles = array(
			'givewp-donation-form-embed-app',
			'givewp-design-system-foundation',
			'give-styles',
			'givewp-campaign-blocks-fonts',
		);

		foreach ( $give_script_handles as $handle ) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}

		foreach ( $give_style_handles as $handle ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	}

	/**
	 * Block Give WP styles on the donate page.
	 *
	 * Catches any Give WP styles that were enqueued after the early dequeue
	 * (e.g., by the shortcode during template rendering).
	 *
	 * @param string $html   - The link tag HTML.
	 * @param string $handle - The style handle.
	 *
	 * @return string Modified link tag HTML or empty string.
	 */
	public function block_give_styles_on_donate_page( $html, $handle ) {

		if ( is_admin() || ! is_page_template( 'donate.php' ) ) {
			return $html;
		}

		$blocked_styles = array(
			'givewp-donation-form-embed-app',
			'givewp-design-system-foundation',
			'give-styles',
			'givewp-campaign-blocks-fonts',
		);

		if ( in_array( $handle, $blocked_styles, true ) ) {
			return '';
		}

		return $html;
	}

	/**
	 * Unload a specific script on frontend.
	 *
	 * @param string $handle - The script handle.
	 *
	 * @return void
	 */
	private function unload_script( $handle ) {
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
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
