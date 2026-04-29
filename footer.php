<?php
/**
 * The template for displaying the footer.
 *
 * @package RRTV
 */

defined( 'ABSPATH' ) || exit;

$rrtv_background_image_id = get_field( 'footer_background_image', 'option' );
$rrtv_footer_logo_id      = get_field( 'footer_logo', 'option' );
?>

	<footer class="footer">

		<?php
		if ( $rrtv_background_image_id ) {
			echo wp_get_attachment_image( $rrtv_background_image_id, 'full', false, array( 'class' => 'footer__background-image' ) );
		}
		?>

		<div class="container">
			<div class="footer__section footer__section--one">

				<?php
				if ( $rrtv_footer_logo_id ) {
					echo wp_get_attachment_image( $rrtv_footer_logo_id, 'full', false, array( 'class' => 'footer__logo' ) );
				}
				?>

			</div>

			<div class="footer__section footer__section--two">
				<?php
				wp_nav_menu(
					array(
						'theme_location'       => 'footer-menu',
						'container'            => 'nav',
						'container_class'      => 'nav-menu footer-menu',
						'menu_class'           => 'footer-menu__list',
						'container_aria_label' => esc_attr__( 'Footer navigation', 'riga-revival-tv' ),
						'depth'                => 1,
					)
				);
				?>

				<?php get_template_part( 'components/socials' ); ?>
			</div>

		</div>
	</footer>

	<?php wp_footer(); ?>
</body>
</html>