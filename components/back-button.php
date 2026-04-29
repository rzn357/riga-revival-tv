<?php
/**
 * Template part for displaying back button.
 *
 * @package RRTV\TemplateParts
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

?>

<div class="back-button">
	<button
		class="back-button__link"
		type="button"
		data-back-button
		data-home-url="<?php echo esc_url( home_url( '/' ) ); ?>"
		aria-label="<?php echo esc_attr__( 'Go back to the previous page', 'riga-revival-tv' ); ?>"
	>
		<svg xmlns="http://www.w3.org/2000/svg"
			viewBox="0 0 24 24" fill="none" stroke="#fff"
			stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
			aria-hidden="true">
		<path d="M10 6L4 12l6 6"/>
		<path d="M4 12h16"/>
		</svg>
	</button>
</div>