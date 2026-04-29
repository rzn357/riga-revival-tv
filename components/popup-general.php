<?php
/**
 * PopUp "General" template part.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="popup-general">
	<button class="popup-general__close" type="button" aria-label="<?php esc_attr( function_exists( 'pll__' ) ? pll__( 'Close' ) : __( 'Close', 'riga-revival-tv' ) ); ?>">
		
		<svg viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M28 0.5C43.1878 0.5 55.5 12.8122 55.5 28C55.5 43.1878 43.1878 55.5 28 55.5C12.8122 55.5 0.5 43.1878 0.5 28C0.5 12.8122 12.8122 0.5 28 0.5Z" stroke="#314868"/>
		<path d="M22 34L34 22M22 22L34 34" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
	</button>
	<div class="popup-general__overlay"></div>
	<div class="popup-general__content"></div>
</div>