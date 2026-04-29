<?php
/**
 * Template part for displaying contacts info section.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;

$rrtv_contacts_info_title = get_field( 'contact_info_title' );
$rrtv_contacts_info_info  = get_field( 'contact_info_info' );
$rrtv_form_title          = get_field( 'form_title' );
$rrtv_form_description    = get_field( 'form_description' );
$rrtv_form_shortcode      = get_field( 'form_shortcode' );
?>

<?php if ( $rrtv_contacts_info_info || $rrtv_form_shortcode ) : ?>
	<section class="contacts-info">
		<div class="container">	
			<div class="contacts-info__row">
				<?php if ( $rrtv_contacts_info_title || $rrtv_contacts_info_info ) : ?>
					<div class="contacts-info__info">
						<?php if ( $rrtv_contacts_info_title ) : ?>
							<div class="contacts-info__info__title"><?php echo wp_kses_post( $rrtv_contacts_info_title ); ?></div>
						<?php endif; ?>

						<?php if ( $rrtv_contacts_info_info ) : ?>
							<div class="contacts-info__info__content">
								<?php foreach ( $rrtv_contacts_info_info as $rrtv_info ) : ?>
									<div class="contacts-info__info__item">
										<?php if ( ! empty( $rrtv_info['icon'] ) ) : ?>
											<?php echo wp_get_attachment_image( $rrtv_info['icon'], 'full', false, array( 'class' => 'contacts-info__info__item-icon' ) ); ?>
										<?php endif; ?>

										<?php if ( ! empty( $rrtv_info['text'] ) ) : ?>
											<div class="contacts-info__info__item-text"><?php echo wp_kses_post( $rrtv_info['text'] ); ?></div>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $rrtv_form_shortcode ) : ?>
					<div class="contacts-info__form-wrapper">
						<?php if ( $rrtv_form_title ) : ?>
							<div class="contacts-info__form__title"><?php echo wp_kses_post( $rrtv_form_title ); ?></div>
						<?php endif; ?>

						<?php if ( $rrtv_form_description ) : ?>
							<div class="contacts-info__form__description">
								<?php echo wp_kses_post( $rrtv_form_description ); ?>
							</div>
						<?php endif; ?>

						<div class="contacts-info__form">
							<?php echo do_shortcode( $rrtv_form_shortcode ); ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endif; ?>