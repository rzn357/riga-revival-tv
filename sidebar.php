<?php
/**
 * The sidebar containing the main widget area.
 *
 * @package RRTV
 */

defined( 'ABSPATH' ) || exit;
?>

<aside>
	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	<?php else : ?>
	<p>Add widgets through the admin panel.</p>
	<?php endif; ?>
</aside>