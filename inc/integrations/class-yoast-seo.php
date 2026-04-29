<?php
/**
 * Yoast SEO integration.
 *
 * @package RRTV\Integrations\Classes
 */

namespace RRTV\Integrations;

use RRTV\Utils\Helpers;
use Google_Client;
use Google_Service_YouTube;

defined( 'ABSPATH' ) || exit;

/**
 * Yoast SEO Class.
 */
class Yoast_SEO {

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
		add_filter( 'wpseo_sitemap_entry', array( $this, 'exclude_urls_from_sitemap' ), 10, 1 );
		add_filter( 'wpseo_sitemap_index', array( $this, 'add_custom_sitemap_to_index' ), 10, 1 );
		add_action( 'wpseo_do_sitemap_videos-programs', array( $this, 'generate_videos_programs_sitemap_content' ) );
		add_action( 'wpseo_do_sitemap_videos', array( $this, 'generate_videos_sitemap_content' ) );
		add_action( 'init', array( $this, 'register_custom_sitemap_rewrite' ), 20 );
	}

	/**
	 * Register rewrite rule for custom sitemap so Yoast routes the URL correctly.
	 *
	 * @return void
	 */
	public function register_custom_sitemap_rewrite() {
		global $wpseo_sitemaps;

		if ( isset( $wpseo_sitemaps ) && method_exists( $wpseo_sitemaps, 'register_sitemap' ) ) {
			// Register sitemap and add rewrite so /videos-programs-sitemap.xml is routed to Yoast.
			$wpseo_sitemaps->register_sitemap( 'videos-programs', array( $this, 'generate_videos_programs_sitemap_content' ), '^videos-programs-sitemap\\.xml$' );

			// Register sitemap and add rewrite so /videos-sitemap.xml is routed to Yoast.
			$wpseo_sitemaps->register_sitemap( 'videos', array( $this, 'generate_videos_sitemap_content' ), '^videos-sitemap\\.xml$' );
		}
	}

	/**
	 * Generate content for the custom videos sitemap.
	 *
	 * @return void
	 */
	public function generate_videos_sitemap_content() {
		$page = get_page_by_path( 'videos' );

		if ( $page ) {
			$rrtv_video_sections = get_field( 'video_sections', $page->ID );

			// Video links.
			if ( $rrtv_video_sections ) {
				$video_link = '';

				// YouTube API client setup.
				$rrtv_youtube_error = null;
				try {
					$rrtv_google_api_key = get_field( 'google_api_key', 'option' );
					$rrtv_google_client  = new Google_Client();
					$rrtv_google_client->setApplicationName( 'Riga Revival TV' );
					$rrtv_google_client->setDeveloperKey( $rrtv_google_api_key );

					$rrtv_youtube_service = new Google_Service_YouTube( $rrtv_google_client );
				} catch ( \Exception $e ) {
					$rrtv_youtube_error = 'Error initializing YouTube API client: ' . $e->getMessage();
				}

				if ( empty( $rrtv_youtube_error ) ) {
					foreach ( $rrtv_video_sections as $rrtv_section ) {

						foreach ( $rrtv_section['programs'] as $rrtv_index => $rrtv_program ) {

							if ( $rrtv_program['playlists'] ) {
								foreach ( $rrtv_program['playlists'] as $rrtv_playlist ) {
									$rrtv_error = null;

									try {
										$rrtv_playlist_id = Helpers::get_youtube_playlist_id( $rrtv_playlist['link'] );

										// Fetch playlist items.
										$rrtv_query_params = array(
											'playlistId' => $rrtv_playlist_id,
											'maxResults' => 10,
										);

										$rrtv_playlist_items_response = $rrtv_youtube_service->playlistItems->listPlaylistItems( 'snippet,status', $rrtv_query_params ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

										$rrtv_videos = isset( $rrtv_playlist_items_response->items ) ? $rrtv_playlist_items_response->items : array();
									} catch ( \Exception $e ) {
										$rrtv_error = 'Error fetching playlist data: ' . $e->getMessage();
									}

									if ( empty( $rrtv_error ) && ! empty( $rrtv_videos ) ) {
										foreach ( $rrtv_videos as $rrtv_video ) {
											$rrtv_video_id     = $rrtv_video->snippet->resourceId->videoId;
											$rrtv_video_status = $rrtv_video->status->privacyStatus;

											if ( 'public' === $rrtv_video_status ) {
												$video_link = Helpers::get_single_video_url( $rrtv_video_id );

												if ( $video_link ) {
													$links[] = array(
														'loc' => esc_url( $video_link ),
														'mod' => gmdate( 'c' ),
														'chf' => 'weekly',
														'pri' => 0.7,
													);
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		global $wpseo_sitemaps;

		$output = Helpers::render_wpseo_sitemap( $links, 'videos', 1 );

		if ( isset( $wpseo_sitemaps ) && method_exists( $wpseo_sitemaps, 'set_sitemap' ) ) {
			$wpseo_sitemaps->set_sitemap( $output );
		} else {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Generate content for the custom videos-programs sitemap.
	 *
	 * @return void
	 */
	public function generate_videos_programs_sitemap_content() {

		$page = get_page_by_path( 'videos' );

		if ( $page ) {
			$rrtv_video_sections = get_field( 'video_sections', $page->ID );

			// Program links.
			if ( $rrtv_video_sections ) {
				$program_link = '';

				foreach ( $rrtv_video_sections as $rrtv_section ) {

					foreach ( $rrtv_section['programs'] as $rrtv_index => $rrtv_program ) {
						$program_link = Helpers::get_program_link( $rrtv_section['title'], $rrtv_program['title'], get_permalink( $page->ID ) );

						if ( $program_link ) {
							$links[] = array(
								'loc' => esc_url( $program_link ),
								'mod' => gmdate( 'c' ),
								'chf' => 'weekly',
								'pri' => 0.7,
							);
						}
					}
				}
			}
		}

		global $wpseo_sitemaps;

		$output = Helpers::render_wpseo_sitemap( $links, 'videos-programs', 1 );

		if ( isset( $wpseo_sitemaps ) && method_exists( $wpseo_sitemaps, 'set_sitemap' ) ) {
			$wpseo_sitemaps->set_sitemap( $output );
		} else {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Adds a custom sitemap entry to the Yoast SEO sitemap index.
	 *
	 * @param string $sitemap_index - The existing sitemap index content.
	 *
	 * @return string The modified sitemap index content with the custom sitemap entry.
	 */
	public function add_custom_sitemap_to_index( $sitemap_index ) {
		$now = gmdate( 'c' );

		$sitemap_index .= '<sitemap>' . "\n";
		$sitemap_index .= '<loc>' . esc_url( home_url( '/videos-programs-sitemap.xml' ) ) . '</loc>' . "\n";
		$sitemap_index .= '<lastmod>' . htmlspecialchars( $now ) . '</lastmod>' . "\n";
		$sitemap_index .= '</sitemap>' . "\n";

		$sitemap_index .= '<sitemap>' . "\n";
		$sitemap_index .= '<loc>' . esc_url( home_url( '/videos-sitemap.xml' ) ) . '</loc>' . "\n";
		$sitemap_index .= '<lastmod>' . htmlspecialchars( $now ) . '</lastmod>' . "\n";
		$sitemap_index .= '</sitemap>' . "\n";

		return $sitemap_index;
	}

	/**
	 * Excludes specific URLs from the Yoast SEO sitemap.
	 *
	 * @param array $url - URL data.
	 *
	 * @return false|array     - False to exclude, or the URL data array.
	 */
	public function exclude_urls_from_sitemap( $url ) {
		if ( isset( $url['loc'] ) ) {
			$excluded = array(
				'/donations/',
				'/donations/fundraising-campaign/',
			);
			foreach ( $excluded as $exclude ) {

				// Check if the URL ends with the excluded path.
				if ( str_ends_with( $url['loc'], $exclude ) ) {
					return false;
				}
			}
		}
		return $url;
	}
}
