<?php
/**
 * The template for displaying videos/playlist/ID route.
 *
 * @package RRTV
 */

defined( 'ABSPATH' ) || exit;

// Get current program data.
$rrtv_videos_page = get_page_by_path( 'videos', OBJECT, 'page' );

// If WPML is active, map the page to the current language.
if ( $rrtv_videos_page && has_filter( 'wpml_object_id' ) ) {
	$rrtv_videos_page_id = apply_filters( 'wpml_object_id', $rrtv_videos_page->ID, 'page', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	if ( $rrtv_videos_page_id ) {
		$rrtv_videos_page = get_post( $rrtv_videos_page_id );
	}
}

$rrtv_videos_page_sections       = $rrtv_videos_page ? get_field( 'video_sections', $rrtv_videos_page->ID ) : array();
$rrtv_current_video_section_slug = get_query_var( 'rrtv_video_section' );
$rrtv_current_video_program_slug = get_query_var( 'rrtv_video_program' );
$rrtv_current_program_data       = array();

foreach ( $rrtv_videos_page_sections as $rrtv_videos_page_section ) {
	if ( sanitize_title( $rrtv_videos_page_section['title'] ) === $rrtv_current_video_section_slug ) {

		foreach ( $rrtv_videos_page_section['programs'] as $rrtv_program ) {
			if ( sanitize_title( $rrtv_program['title'] ) === $rrtv_current_video_program_slug ) {
				$rrtv_current_program_data = $rrtv_program;
				break 2;
			}
		}

		break;
	}
}

// YouTube API client setup.
$rrtv_youtube_error = null;
try {
	$rrtv_google_api_key = get_field( 'google_api_key', 'option' );
	$rrtv_google_client  = new Google_Client();
	$rrtv_google_client->setApplicationName( 'Riga Revival TV' );
	$rrtv_google_client->setDeveloperKey( $rrtv_google_api_key );

	$rrtv_youtube_service = new Google_Service_YouTube( $rrtv_google_client );
} catch ( Exception $e ) {
	$rrtv_youtube_error = 'Error initializing YouTube API client: ' . $e->getMessage();
}

?>

<?php get_header(); ?>
<main id="main" tabindex="-1" class="main-content">

	<?php if ( $rrtv_youtube_error ) : ?>
		<div class="container">
			<?php echo esc_html( $rrtv_youtube_error ); ?>
		</div>
	<?php else : ?>

		<?php get_template_part( 'components/back-button' ); ?>

		<?php get_template_part( 'components/program-banner', null, array( 'current_program_data' => $rrtv_current_program_data ) ); ?>

		<?php
		get_template_part(
			'components/playlists',
			null,
			array(
				'current_program_data' => $rrtv_current_program_data,
				'youtube_service'      => $rrtv_youtube_service,
			)
		);
		?>

	<?php endif; ?>
	
</main>
<?php get_footer(); ?>