<?php
/**
 * Template Name: Videos
 *
 * @package RRTV
 */

defined( 'ABSPATH' ) || exit;

$rrtv_video_sections = get_field( 'video_sections' );

$rrtv_google_api_key = get_field( 'google_api_key', 'option' );
$rrtv_google_client  = new Google_Client();
$rrtv_google_client->setApplicationName( 'Riga Revival TV' );
$rrtv_google_client->setDeveloperKey( $rrtv_google_api_key );

$rrtv_youtube_service = new Google_Service_YouTube( $rrtv_google_client );

?>
<?php get_header(); ?>

<main id="main" tabindex="-1" class="main-content">
	<section class="page-header">
		<div class="container">
			<h1 class="page-header__title">Categories</h1>
		</div>
	</section>
	
	<?php if ( $rrtv_video_sections ) : ?>
		<?php foreach ( $rrtv_video_sections as $rrtv_section_index => $rrtv_section ) : ?>

			<?php
			get_template_part(
				'components/video-section',
				null,
				array(
					'section_index'   => $rrtv_section_index + 1,
					'data'            => $rrtv_section,
					'page_id'         => get_the_ID(),
					'youtube_service' => $rrtv_youtube_service,
				)
			);
			?>

		<?php endforeach; ?>
	<?php endif; ?>
</main>

<?php get_footer(); ?>