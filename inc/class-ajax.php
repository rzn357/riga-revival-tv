<?php
/**
 * Ajax handler class.
 *
 * @package RRTV\Classes
 */

namespace RRTV;

use RRTV\Utils\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * This class is used to handle AJAX requests.
 */
class Ajax {

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
		add_action( 'wp_ajax_load_more_playlist_videos', array( $this, 'load_more_playlist_videos' ) );
		add_action( 'wp_ajax_nopriv_load_more_playlist_videos', array( $this, 'load_more_playlist_videos' ) );
	}

	/**
	 * Load more playlist videos.
	 *
	 * @return void
	 */
	public function load_more_playlist_videos() {
		check_ajax_referer( 'rrtv_theme_security_nonce', 'nonce' );

		$playlist_id = isset( $_POST['playlist_id'] ) ? sanitize_text_field( wp_unslash( $_POST['playlist_id'] ) ) : '';
		$page_token  = isset( $_POST['page_token'] ) ? sanitize_text_field( wp_unslash( $_POST['page_token'] ) ) : '';

		if ( empty( $playlist_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Playlist ID is required.', 'riga-revival-tv' ) ) );
		}

		$google_api_key = get_field( 'google_api_key', 'option' );
		$google_client  = new \Google_Client();
		$google_client->setApplicationName( 'Riga Revival TV' );
		$google_client->setDeveloperKey( $google_api_key );

		$youtube_service = new \Google_Service_YouTube( $google_client );

		$query_params = array(
			'playlistId' => $playlist_id,
			'maxResults' => 10,
		);

		if ( ! empty( $page_token ) ) {
			$query_params['pageToken'] = $page_token;
		}

		try {
			$playlist_items_response = $youtube_service->playlistItems->listPlaylistItems( 'snippet,status', $query_params ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			$videos          = isset( $playlist_items_response->items ) ? $playlist_items_response->items : array();
			$next_page_token = isset( $playlist_items_response->nextPageToken ) ? $playlist_items_response->nextPageToken : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			$html = '';

			if ( ! empty( $videos ) ) {
				foreach ( $videos as $video ) {
					$video_id     = $video->snippet->resourceId->videoId;
					$video_title  = $video->snippet->title;
					$video_thumb  = Helpers::get_youtube_thumb_url( $video );
					$video_status = $video->status->privacyStatus;

					if ( 'public' !== $video_status ) {
						continue;
					}

					ob_start();
					?>
					<a href="<?php echo esc_url( Helpers::get_single_video_url( $video_id ) ); ?>" class="playlist__item splide__slide">
						<div class="playlist__item__thumb-wrapper">
							<img src="<?php echo esc_url( $video_thumb ); ?>" alt="<?php echo esc_attr( $video_title ); ?>" class="playlist__item__thumb" />
						</div>
						<h2 class="playlist__item__title"><?php echo wp_kses_post( $video_title ); ?></h2>
					</a>
					<?php
					$html .= ob_get_clean();
				}
			}

			wp_send_json_success(
				array(
					'html'          => $html,
					'nextPageToken' => $next_page_token,
				)
			);
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}
}
