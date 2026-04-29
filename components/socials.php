<?php
/**
 * Template part for displaying socials links.
 *
 * @package RRTV\TemplateParts
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

$rrtv_socials = get_field( 'site_socials', 'option' );
?>

<?php if ( $rrtv_socials ) : ?>
	<div class="socials">
		<?php foreach ( $rrtv_socials as $rrtv_social ) : ?>

			<?php
			$rrtv_icon_id  = $rrtv_social['icon'];
			$rrtv_icon_alt = get_post_meta( (int) $rrtv_icon_id, '_wp_attachment_image_alt', true );

			if ( false !== $rrtv_icon_alt && '' === trim( $rrtv_icon_alt ) ) {
				$rrtv_aria_label = 'aria-label="' . esc_attr( $rrtv_social['name'] . ' link' ) . '"';
			} else {
				$rrtv_aria_label = '';
			}
			?>

			<a
				class="socials__link"
				href="<?php echo esc_url( $rrtv_social['link'] ); ?>"
				target="_blank"
				rel="noopener"
				<?php echo $rrtv_aria_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			>
				<?php
				if ( ! empty( $rrtv_icon_id ) ) {
					echo wp_get_attachment_image( $rrtv_icon_id, 'full', false, array( 'class' => 'socials__icon' ) );
				} else {
					echo esc_html( $rrtv_social['name'] );
				}
				?>
			</a>
		<?php endforeach; ?>
	</div>
<?php endif; ?>