<?php
/**
 * Helpers methods.
 *
 * @package RRTV\Utils\Classes
 */

namespace RRTV\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Helper utilities for theme.
 */
class Helpers {

	/**
	 * Get YouTube video or playlist thumbnail URL.
	 *
	 * @param object|null $item - data object.
	 *
	 * @return string - YouTube video or playlist thumbnail URL.
	 */
	public static function get_youtube_thumb_url( $item = null ) {
		if ( ! $item ) {
			return '';
		}

		if ( isset( $item->snippet->thumbnails->maxres->url ) ) {
			return esc_url( $item->snippet->thumbnails->maxres->url );
		}

		if ( isset( $item->snippet->thumbnails->standard->url ) ) {
			return esc_url( $item->snippet->thumbnails->standard->url );
		}

		if ( isset( $item->snippet->thumbnails->high->url ) ) {
			return esc_url( $item->snippet->thumbnails->high->url );
		}

		if ( isset( $item->snippet->thumbnails->medium->url ) ) {
			return esc_url( $item->snippet->thumbnails->medium->url );
		}

		if ( isset( $item->snippet->thumbnails->default->url ) ) {
			return esc_url( $item->snippet->thumbnails->default->url );
		}

		return RRTV_THEME_DIR_URL . '/assets/img/placeholder.png';
	}
	/**
	 * Get single video URL.
	 *
	 * @param string $video_id - Video ID.
	 *
	 * @return string
	 */
	public static function get_single_video_url( $video_id = '' ) {
		if ( ! $video_id ) {
			return '';
		}

		$args  = array(
			'post_type'  => 'page',
			'meta_key'   => '_wp_page_template',
			'meta_value' => 'videos.php',
		);
		$pages = get_posts( $args );

		if ( empty( $pages ) ) {
			return '';
		}

		$page_url = get_permalink( $pages[0]->ID );

		return esc_url( $page_url . sanitize_text_field( $video_id ) . '/' );
	}

	/**
	 * Get YouTube playlist ID from YouTube playlist URL.
	 *
	 * @param string $youtube_playlist_url - YouTube playlist URL.
	 *
	 * @return string - YouTube playlist ID.
	 */
	public static function get_youtube_playlist_id( $youtube_playlist_url = '' ) {
		if ( ! $youtube_playlist_url ) {
			return '';
		}

		$parsed_url = wp_parse_url( $youtube_playlist_url );
		if ( ! isset( $parsed_url['query'] ) ) {
			return '';
		}

		parse_str( $parsed_url['query'], $query_params );

		return $query_params['list'] ? esc_html( $query_params['list'] ) : '';
	}

	/**
	 * Get playlist link from YouTube playlist URL.
	 *
	 * @param string $youtube_playlist_url - YouTube playlist URL.
	 * @param string $base_url             - Base URL to append the playlist ID.
	 *
	 * @return string - Playlist link.
	 */
	public static function get_playlist_link( $youtube_playlist_url = '', $base_url = '' ) {
		if ( ! $youtube_playlist_url || ! $base_url ) {
			return '';
		}

		$parsed_url = wp_parse_url( $youtube_playlist_url );
		if ( ! isset( $parsed_url['query'] ) ) {
			return '';
		}

		parse_str( $parsed_url['query'], $query_params );

		return $query_params['list'] ? $base_url . esc_html( $query_params['list'] ) : '';
	}

	/**
	 * Get program link.
	 *
	 * @param string $section_title - Section title.
	 * @param string $program_title - Program title.
	 * @param string $base_url      - Base URL to append the program slug.
	 *
	 * @return string - Program link.
	 */
	public static function get_program_link( $section_title = '', $program_title = '', $base_url = '' ) {
		if ( ! $section_title || ! $program_title || ! $base_url ) {
			return '';
		}

		$program_slug = sanitize_title( wp_strip_all_tags( $program_title ) );
		$section_slug = sanitize_title( wp_strip_all_tags( $section_title ) );

		return $base_url . $section_slug . '/' . $program_slug . '/';
	}

	/**
	 * Get the HTML for the logo link.
	 *
	 * @return void
	 */
	public static function print_logo_link_html() {
		$logo_id  = get_field( 'site_logo', 'option' );
		$logo_alt = get_post_meta( (int) $logo_id, '_wp_attachment_image_alt', true );

		if ( false !== $logo_alt && '' === trim( $logo_alt ) ) {
			$aria_label = 'aria-label="' . esc_attr( get_bloginfo( 'name' ) . ' homepage' ) . '"';
		} else {
			$aria_label = '';
		}

		echo '<a class="logo" href="' . esc_url( home_url( '/' ) ) . '" ' . $aria_label . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $logo_id ) {
			echo wp_get_attachment_image(
				$logo_id,
				'full',
				false,
				array(
					'class'         => 'logo__image',
					'fetchpriority' => 'high',
				)
			);
		} else {
			bloginfo( 'name' );
		}
		echo '</a>';
	}

	/**
	 * Render a set of sitemap links using Yoast's sitemap renderer when available,
	 * or produce raw <url> entries as a fallback.
	 *
	 * @param array  $links - Array of link arrays with keys: 'loc', 'mod', 'chf', 'pri'.
	 * @param string $name  - Sitemap name used by Yoast renderer (e.g. 'videos').
	 * @param int    $page  - Sitemap page number.
	 *
	 * @return string Sitemap XML fragment (either full sitemap from Yoast renderer or raw <url> entries).
	 */
	public static function render_wpseo_sitemap( $links = array(), $name = 'custom', $page = 1 ) {
		if ( empty( $links ) || ! is_array( $links ) ) {
			return '';
		}

		global $wpseo_sitemaps;

		if ( isset( $wpseo_sitemaps ) && isset( $wpseo_sitemaps->renderer ) && method_exists( $wpseo_sitemaps->renderer, 'get_sitemap' ) ) {
			return $wpseo_sitemaps->renderer->get_sitemap( $links, $name, (int) $page );
		}

		// Fallback: build raw <url> entries.
		$output = '';
		foreach ( $links as $link ) {
			$loc = isset( $link['loc'] ) ? htmlspecialchars( $link['loc'] ) : '';
			$mod = isset( $link['mod'] ) ? htmlspecialchars( $link['mod'] ) : '';
			$chf = isset( $link['chf'] ) ? htmlspecialchars( $link['chf'] ) : '';
			$pri = isset( $link['pri'] ) ? htmlspecialchars( (string) $link['pri'] ) : '';

			$output .= "\t<url>\n";
			$output .= "\t\t<loc>" . $loc . "</loc>\n";
			if ( $mod ) {
				$output .= "\t\t<lastmod>" . $mod . "</lastmod>\n";
			}
			if ( $chf ) {
				$output .= "\t\t<changefreq>" . $chf . "</changefreq>\n";
			}
			if ( '' !== $pri ) {
				$output .= "\t\t<priority>" . $pri . "</priority>\n";
			}
			$output .= "\t</url>\n";
		}

		return $output;
	}
}
