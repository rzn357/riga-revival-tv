<?php
/**
 * Template part for displaying "Our Videos" section.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;

$rrtv_title           = get_field( 'our_videos_title' );
$rrtv_featured_videos = get_field( 'our_videos_featured_videos' );
$rrtv_button          = get_field( 'our_videos_button' );
?>

<?php if ( $rrtv_featured_videos ) : ?>
	<section class="our-videos">

		<div class="container">
			<?php if ( $rrtv_title ) : ?>
				<div class="our-videos__title"><?php echo wp_kses_post( $rrtv_title ); ?></div>
			<?php endif; ?>
		</div>

		<div class="our-videos__featured-videos">
			<?php foreach ( $rrtv_featured_videos as $rrtv_video ) : ?>
				<div class="our-videos__card">
					<div class="our-videos__card-inner">
						<div class="our-videos__card-front">
							<?php
							if ( $rrtv_video['image'] ) {
								echo wp_get_attachment_image( $rrtv_video['image'], 'full', false, array( 'class' => 'our-videos__card-image' ) );
							}
							?>
							<a href="<?php echo esc_url( $rrtv_video['link'] ); ?>" class="our-videos__card-front__link" aria-label="<?php echo esc_attr( wp_strip_all_tags( $rrtv_video['title'] ) ); ?>"></a>
						</div>
						<div class="our-videos__card-back">

							<a href="<?php echo esc_url( $rrtv_video['link'] ); ?>" class="our-videos__card-back__content">
								<?php if ( $rrtv_video['title'] ) : ?>
									<div class="our-videos__card-title"><?php echo wp_kses_post( $rrtv_video['title'] ); ?></div>
								<?php endif; ?>
								
								<?php if ( $rrtv_video['description'] ) : ?>
									<div class="our-videos__card-description"><?php echo wp_kses_post( $rrtv_video['description'] ); ?></div>
								<?php endif; ?>
							</a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $rrtv_button ) : ?>
			<div class="our-videos__button-wrapper">
				<a href="<?php echo esc_url( $rrtv_button['url'] ); ?>" class="our-videos__button btn btn--primary" <?php echo esc_html( $rrtv_button['target'] ? 'target="' . esc_attr( $rrtv_button['target'] ) . '"' : '' ); ?>>
					<?php echo esc_html( $rrtv_button['title'] ); ?>
				</a>
			</div>
		<?php endif; ?>
		
	</section>
<?php endif; ?>