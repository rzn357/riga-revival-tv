<?php
/**
 * Template part for displaying video player section.
 *
 * @package RRTV\TemplateParts
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

$rrtv_iframe_html = $args['iframe_html'] ?? '';

if ( empty( $rrtv_iframe_html ) ) {
	return;
}
?>

<section class="video-player">
	<div class="container video-player__container">
		<div class="video-player__inner">
			<?php
			echo wp_kses(
				$rrtv_iframe_html,
				array(
					'iframe' => array(
						'src'             => true,
						'width'           => true,
						'height'          => true,
						'class'           => true,
						'id'              => true,
						'title'           => true,
						'frameborder'     => true,
						'allow'           => true,
						'allowfullscreen' => true,
						'loading'         => true,
						'referrerpolicy'  => true,
						'name'            => true,
						'sandbox'         => true,
						'style'           => true,
						'data-*'          => true,
						'aria-*'          => true,
						'role'            => true,
					),
				)
			);
			?>
		</div>
	</div>
</section>
