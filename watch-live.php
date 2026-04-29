<?php
/**
 * Template Name: Watch Live
 *
 * @package RRTV
 */

defined( 'ABSPATH' ) || exit;

$rrtv_background_image_id = get_field( 'background_image' );
?>
<?php get_header(); ?>

<main id="main" tabindex="-1" class="main-content">
	<?php
	echo wp_get_attachment_image(
		$rrtv_background_image_id,
		'full',
		false,
		array(
			'class'  => 'page-background-image skip-lazy',
			'width'  => '100%',
			'height' => '100%',
		)
	);
	?>

	<?php get_template_part( 'components/video-player', null, array( 'iframe_html' => '<iframe class="video-player__iframe" title="' . esc_attr( get_the_title() ) . '" src="https://player.restream.io/?token=e55a4c9a46e940f1ae1a41b897d11335&vwrs=1" allow="autoplay" allowfullscreen frameborder="0" style="position:absolute;top:0;left:0;width:100%;height:100%;"></iframe>' ) ); ?>

</main>

<?php get_footer(); ?>