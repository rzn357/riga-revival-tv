<?php
/**
 * Template part for displaying section of the video.
 *
 * @package RRTV\TemplateParts
 */

use RRTV\Utils\Helpers;

defined( 'ABSPATH' ) || exit;

$rrtv_data            = $args['data'] ?? array();
$rrtv_page_id         = $args['page_id'] ?? 0;
$rrtv_youtube_service = $args['youtube_service'] ?? null;

if ( isset( $rrtv_data['is_top_10'][0] ) && 'yes' === $rrtv_data['is_top_10'][0] ) {
	$rrtv_is_top_10 = true;
} else {
	$rrtv_is_top_10 = false;
}

?>

<?php if ( $rrtv_data && $rrtv_youtube_service ) : ?>
	<section class="video-section">
		<div class="container">
			
			<?php if ( $rrtv_data['title'] ) : ?>
				<div class="video-section__title"><?php echo wp_kses_post( $rrtv_data['title'] ); ?></div>
			<?php endif; ?>

			<?php if ( $rrtv_data['programs'] ) : ?>
				<div class="video-section__programs runtime-clampify-disable-full">
					<div class="splide">
						<div class="splide__track">
							<div class="splide__list">
								<?php foreach ( $rrtv_data['programs'] as $rrtv_index => $rrtv_program ) : ?>
									<div class="splide__slide video-section__program__link-wrapper">
										<a href="<?php echo esc_url( Helpers::get_program_link( $rrtv_data['title'], $rrtv_program['title'], get_permalink( $rrtv_page_id ) ) ); ?>" class="video-section__program__link">
											<div class="video-section__program__image-wrapper <?php echo ( $rrtv_is_top_10 ) ? 'video-section__program__image-wrapper--with-index' : ''; ?>">
												
												<?php if ( $rrtv_is_top_10 ) : ?>

													<div class="video-section__program__index-wrapper <?php echo ( $rrtv_is_top_10 && ( $rrtv_index + 1 ) > 9 ) ? 'video-section__program__index-wrapper--with-two-digits' : ''; ?>"><div  class="video-section__program__index"><?php echo esc_html( $rrtv_index + 1 ); ?></div></div>

												<?php endif; ?>
												
												<div class="video-section__program__image-box" >
													<?php
													if ( 1 === $args['section_index'] && 1 === $rrtv_index + 1 ) {
														$rrtv_fetchpriority   = 'high';
														$rrtv_skip_lazy_class = 'skip-lazy';
													} else {
														$rrtv_fetchpriority   = 'low';
														$rrtv_skip_lazy_class = '';
													}
													?>
													<?php if ( $rrtv_program['thumbnail'] ) : ?>

														<?php
														echo wp_get_attachment_image(
															$rrtv_program['thumbnail'],
															'full',
															false,
															array(
																'class'         => 'video-section__program__image ' . $rrtv_skip_lazy_class,
																'fetchpriority' => $rrtv_fetchpriority,
															)
														);
														?>

													<?php else : ?>
														<img alt="Placeholder" src="<?php echo esc_url( RRTV_THEME_DIR_URL . '/assets/img/placeholder.png' ); ?>" class="video-section__program__image <?php echo esc_attr( $rrtv_skip_lazy_class ); ?>" fetchpriority="<?php echo esc_attr( $rrtv_fetchpriority ); ?>" />
													<?php endif; ?>
												</div>
												
											</div>
											<h3 class="video-section__program__title"><?php echo esc_html( wp_strip_all_tags( $rrtv_program['title'] ) ); ?></h3>
										</a>
									</div>
								<?php endforeach; ?>
							</div>
						</div>					
					</div>
				</div>
			<?php endif; ?>	

		</div>		
	</section>
<?php endif; ?>