<?php
/**
 * Header template.
 *
 * @package RRTV
 */

use RRTV\Utils\Helpers;

defined( 'ABSPATH' ) || exit;
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?> class="<?php echo is_admin_bar_showing() ? 'admin-bar-showed' : ''; ?> runtime-clampify-disable">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

	<?php
	$rrtv_route = get_query_var( 'rrtv_route' );

	if ( is_page_template( 'watch-live.php' ) || 'videos_program' === $rrtv_route || 'videos_single' === $rrtv_route ) {
		$rrtv_mobile_view_class = 'header--mobile-view';
	} else {
		$rrtv_mobile_view_class = '';
	}
	?>

	<header class="header runtime-clampify-disable <?php echo esc_attr( $rrtv_mobile_view_class ); ?>">
		<a class="skip-link" href="#main">Skip to main content.</a>

		<div class="container">

			<?php Helpers::print_logo_link_html(); ?>

			<div class="header__menu">
				<?php
				wp_nav_menu(
					array(
						'theme_location'       => 'primary-menu',
						'container'            => 'nav',
						'container_class'      => 'nav-menu primary-menu',
						'container_aria_label' => esc_attr__( 'Primary navigation', 'riga-revival-tv' ),
						'menu_class'           => 'primary-menu__list',
					)
				);
				?>

				<button class="header__mobile-menu-toggle" aria-label="Toggle menu">
					<span class="header__mobile-menu-toggle-bar"></span>
					<span class="header__mobile-menu-toggle-bar"></span>
					<span class="header__mobile-menu-toggle-bar"></span>
				</button>
			</div>

			<div class="header__extras">
				<?php get_template_part( 'components/socials' ); ?>
			</div>
		</div>	
	</header>