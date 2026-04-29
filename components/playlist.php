<?php
/**
 * Template part for displaying playlist section.
 *
 * @package RRTV\TemplateParts
 */

use RRTV\Utils\Helpers;

defined( 'ABSPATH' ) || exit;

$rrtv_youtube_service = $args['rrtv_youtube_service'] ?? null;
$rrtv_playlist        = $args['rrtv_playlist'] ?? null;

if ( ! $rrtv_youtube_service || ! $rrtv_playlist || ! isset( $rrtv_playlist['link'] ) || empty( $rrtv_playlist['link'] ) ) {
	return;
}

$rrtv_error = null;

try {
	$rrtv_playlist_id = Helpers::get_youtube_playlist_id( $rrtv_playlist['link'] );
	// Fetch playlist details.
	$rrtv_query_params = array(
		'id' => $rrtv_playlist_id,
	);

	$rrtv_response = $rrtv_youtube_service->playlists->listPlaylists( 'snippet,status', $rrtv_query_params );

	$rrtv_playlist_title = $rrtv_response->items[0]->snippet->title;

	// Fetch playlist items.
	$rrtv_query_params = array(
		'playlistId' => $rrtv_playlist_id,
		'maxResults' => 10,
	);

	$rrtv_playlist_items_response = $rrtv_youtube_service->playlistItems->listPlaylistItems( 'snippet,status', $rrtv_query_params ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

	$rrtv_videos          = isset( $rrtv_playlist_items_response->items ) ? $rrtv_playlist_items_response->items : array();
	$rrtv_next_page_token = isset( $rrtv_playlist_items_response->nextPageToken ) ? $rrtv_playlist_items_response->nextPageToken : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
} catch ( Exception $e ) {
	$rrtv_error = 'Error fetching playlist data: ' . $e->getMessage();
}
?>

			
<div class="playlist">

	<?php if ( $rrtv_error ) : ?>
		<div class="playlist__header">
			<h2 class="playlist__title"><?php echo wp_kses_post( $rrtv_error ); ?></h2>
		</div>
	<?php else : ?>

		<div class="playlist__header">
			<h2 class="playlist__title"><?php echo wp_kses_post( $rrtv_playlist_title ); ?></h2>
		</div>

		<?php if ( $rrtv_videos ) : ?>
			<div class="playlist__list splide runtime-clampify-disable-full" data-playlist-id="<?php echo esc_attr( $rrtv_playlist_id ); ?>" data-next-page-token="<?php echo esc_attr( $rrtv_next_page_token ); ?>">
				<div class="splide__track">
					<div class="splide__list">
						<?php foreach ( $rrtv_videos as $rrtv_video ) : ?>
							<?php
							$rrtv_video_id     = $rrtv_video->snippet->resourceId->videoId;
							$rrtv_video_title  = $rrtv_video->snippet->title;
							$rrtv_video_thumb  = Helpers::get_youtube_thumb_url( $rrtv_video );
							$rrtv_video_status = $rrtv_video->status->privacyStatus;

							if ( 'public' !== $rrtv_video_status ) {
								continue;
							}
							?>
							<div class="splide__slide playlist__item">
								<a href="<?php echo esc_url( Helpers::get_single_video_url( $rrtv_video_id ) ); ?>" class="playlist__item-linkt">
									<div class="playlist__item__thumb-wrapper">
										<img src="<?php echo esc_url( $rrtv_video_thumb ); ?>" alt="" class="playlist__item__thumb" />
									</div>
									<h3 class="playlist__item__title"><?php echo wp_kses_post( $rrtv_video_title ); ?></h3>
								</a>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'No videos found in this playlist.', 'riga-revival-tv' ); ?></p>
		<?php endif; ?>

	<?php endif; ?>
</div>