<?php
/**
 * The template for displaying videos/ID route.
 *
 * @package RRTV
 */

defined( 'ABSPATH' ) || exit;

$rrtv_background_image_id = get_field( 'videos_single_background_image', 'option' );
$rrtv_video_id            = get_query_var( 'rrtv_id' );
?>

<?php get_header(); ?>
<main id="main" tabindex="-1" class="main-content" aria-label="<?php the_title(); ?>">
	<?php
	echo wp_get_attachment_image(
		$rrtv_background_image_id,
		'full',
		false,
		array(
			'class'         => 'page-background-image skip-lazy',
			'fetchpriority' => 'high',
		)
	);
	?>

	<?php get_template_part( 'components/back-button' ); ?>

	<?php get_template_part( 'components/video-player', null, array( 'iframe_html' => '<iframe class="video-player__iframe" src="https://www.youtube.com/embed/' . esc_attr( $rrtv_video_id ) . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" style="width: 100%; height: 100%;" allowfullscreen></iframe>' ) ); ?>
</main>
<?php get_footer(); ?>