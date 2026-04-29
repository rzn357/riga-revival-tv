<?php
/**
 * Template part for displaying contacts map section.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;

$rrtv_map_data = get_field( 'map' );
?>

<?php if ( isset( $rrtv_map_data['lat'] ) && ! empty( $rrtv_map_data['lat'] ) && isset( $rrtv_map_data['lng'] ) && ! empty( $rrtv_map_data['lng'] ) ) : ?>
	<section class="contacts-map">
		<div class="contacts-map__wrapper">
			<div id="contacts-map"></div>
		</div>
	</section>
<?php endif; ?>