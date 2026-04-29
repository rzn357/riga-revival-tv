<?php
/**
 * Template part for displaying "About Company" section.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;

$rrtv_description = get_field( 'about_company_description' );
$rrtv_image_id    = get_field( 'about_company_image' );
$rrtv_sections    = get_field( 'about_company_sections' );
?>

<?php if ( $rrtv_description || $rrtv_image_id || $rrtv_sections ) : ?>
	<section class="about-company">

		<div class="container">

			<div class="about-company__row">

				<div class="about-company__texts">

					<?php if ( $rrtv_description ) : ?>
						<div class="about-company__description"><?php echo wp_kses_post( $rrtv_description ); ?></div>
					<?php endif; ?>		

					<?php if ( $rrtv_sections ) : ?>
						<div class="about-company__sections">
							<?php foreach ( $rrtv_sections as $rrtv_section ) : ?>
								<div class="about-company__section">
									<?php if ( $rrtv_section['title'] ) : ?>
										<div class="about-company__title"><?php echo wp_kses_post( $rrtv_section['title'] ); ?></div>
									<?php endif; ?>
									
									<div class="about-company__subsections">
										<?php foreach ( $rrtv_section['subsections'] as $rrtv_subsection ) : ?>
											<div class="about-company__subsection">
												<div class="about-company__subsection-title"><?php echo wp_kses_post( $rrtv_subsection['title'] ); ?></div>
												<div class="about-company__subsection-description"><?php echo wp_kses_post( $rrtv_subsection['description'] ); ?></div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

				</div>

				<div class="about-company__image-wrapper">
					<?php if ( $rrtv_image_id ) : ?>
						<?php echo wp_get_attachment_image( $rrtv_image_id, 'full', false, array( 'class' => 'about-company__image' ) ); ?>
					<?php endif; ?>	
				</div>

			</div>

		</div>
		
	</section>
<?php endif; ?>