<?php
/**
 * Template part for displaying "our team" section.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;

$rrtv_title    = get_field( 'our_team_title' );
$rrtv_subtitle = get_field( 'our_team_subtitle' );
$rrtv_members  = get_field( 'our_team_members' );

?>

<?php if ( $rrtv_title || $rrtv_subtitle || $rrtv_members ) : ?>
	<section class="our-team">

			<div class="our-team__header">
				<div class="container">
					<?php if ( $rrtv_title ) : ?>
						<div class="our-team__title"><?php echo wp_kses_post( $rrtv_title ); ?></div>
					<?php endif; ?>

					<?php if ( $rrtv_subtitle ) : ?>
						<div class="our-team__subtitle"><?php echo wp_kses_post( $rrtv_subtitle ); ?></div>
					<?php endif; ?>
				</div>		
			</div>

			<div class="our-team__content">
				<?php if ( $rrtv_members ) : ?>
					<div class="our-team__members">
						<div class="container">
							<div class="our-team__members__grid">
								<?php
								foreach ( $rrtv_members as $rrtv_index => $rrtv_member ) {
									if ( $rrtv_index + 1 > 4 ) {
										break;
									}

									get_template_part( 'components/member-card', null, array( 'member' => $rrtv_member ) );
								}
								?>
							</div>

							<?php if ( count( $rrtv_members ) > 4 ) : ?>
								<div class="our-team__members__grid our-team__members__grid--all">
									<?php
									foreach ( $rrtv_members as $rrtv_index => $rrtv_member ) {
										if ( $rrtv_index + 1 < 5 ) {
											continue;
										}

										get_template_part( 'components/member-card', null, array( 'member' => $rrtv_member ) );
									}
									?>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<?php if ( count( $rrtv_members ) > 4 ) : ?>
						<div class="our-team__view-all">
							<div class="container">
								<button class="our-team__view-all-link">All Team</button>
							</div>
						</div>
					<?php endif; ?>

				<?php endif; ?>
			</div>
	</section>
<?php endif; ?>