<?php
/**
 * Template part for displaying member card.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;

$rrtv_member = $args['member'];
?>

<?php if ( $rrtv_member ) : ?>
	<div class="member-card">
		
			<div class="member-card__photo-wrapper">
				<?php if ( $rrtv_member['photo'] ) : ?>
					<?php echo wp_get_attachment_image( $rrtv_member['photo'], 'full', false, array( 'class' => 'member-card__photo' ) ); ?>
				<?php else : ?>
					<img src="<?php echo esc_url( RRTV_THEME_DIR_URL . '/assets/img/placeholder.png' ); ?>" class="member-card__photo" alt="<?php echo esc_attr( $rrtv_member['name'] ); ?>">
				<?php endif; ?>
			</div>

		<div class="member-card__overlay">
			<div class="member-card__info">
				<?php if ( $rrtv_member['name'] ) : ?>
					<div class="member-card__name"><?php echo wp_kses_post( $rrtv_member['name'] ); ?></div>
				<?php endif; ?>

				<?php if ( $rrtv_member['position'] ) : ?>
					<div class="member-card__position"><?php echo wp_kses_post( $rrtv_member['position'] ); ?></div>
				<?php endif; ?>
			</div>
			<div class="member-card__socials">
				<?php if ( $rrtv_member['socials'] ) : ?>
					<?php foreach ( $rrtv_member['socials'] as $rrtv_social ) : ?>
						<?php if ( $rrtv_social['url'] ) : ?>

							<?php

							if ( strpos( $rrtv_social['url'], 'facebook' ) !== false ) {
								$rrtv_icon_class = 'facebook';
							} elseif ( strpos( $rrtv_social['url'], 'x.com' ) !== false ) {
								$rrtv_icon_class = 'xcom';
							} elseif ( strpos( $rrtv_social['url'], 'instagram' ) !== false ) {
								$rrtv_icon_class = 'instagram';
							} elseif ( strpos( $rrtv_social['url'], 'youtube' ) !== false ) {
								$rrtv_icon_class = 'youtube';
							} else {
								$rrtv_icon_class = 'website';
							}

							?>

							<a href="<?php echo esc_url( $rrtv_social['url'] ); ?>" class="member-card__social-link" target="_blank" rel="noopener">

								<?php if ( 'facebook' === $rrtv_icon_class ) : ?>

									<svg aria-hidden="true" class="member-card__social-icon <?php echo esc_attr( $rrtv_icon_class ); ?>" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg"><path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"></path></svg>
								<?php elseif ( 'xcom' === $rrtv_icon_class ) : ?>
									
									<svg aria-hidden="true" class="member-card__social-icon <?php echo esc_attr( $rrtv_icon_class ); ?>" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg>
									
								<?php elseif ( 'instagram' === $rrtv_icon_class ) : ?>
									
									<svg aria-hidden="true" class="member-card__social-icon <?php echo esc_attr( $rrtv_icon_class ); ?>" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"></path></svg>
								<?php elseif ( 'youtube' === $rrtv_icon_class ) : ?>
									
									<svg aria-hidden="true" class="member-card__social-icon <?php echo esc_attr( $rrtv_icon_class ); ?>" viewBox="0 0 576 512" xmlns="http://www.w3.org/2000/svg"><path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"></path></svg>

								<?php else : ?>
									
									<svg aria-hidden="true" class="member-card__social-icon <?php echo esc_attr( $rrtv_icon_class ); ?>" viewBox="0 0 490 490" xmlns="http://www.w3.org/2000/svg"><path d="M245,0C109.69,0,0,109.69,0,245s109.69,245,245,245s245-109.69,245-245S380.31,0,245,0z M31.401,260.313h52.542
									c1.169,25.423,5.011,48.683,10.978,69.572H48.232C38.883,308.299,33.148,284.858,31.401,260.313z M320.58,229.688
									c-1.152-24.613-4.07-47.927-8.02-69.572h50.192c6.681,20.544,11.267,43.71,12.65,69.572H320.58z M206.38,329.885
									c-4.322-23.863-6.443-47.156-6.836-69.572h90.913c-0.392,22.416-2.514,45.709-6.837,69.572H206.38z M276.948,360.51
									c-7.18,27.563-17.573,55.66-31.951,83.818c-14.376-28.158-24.767-56.255-31.946-83.818H276.948z M199.961,229.688
									c1.213-24.754,4.343-48.08,8.499-69.572h73.08c4.157,21.492,7.286,44.818,8.5,69.572H199.961z M215.342,129.492
									c9.57-37.359,21.394-66.835,29.656-84.983c8.263,18.148,20.088,47.624,29.66,84.983H215.342z M306.07,129.492
									c-9.77-40.487-22.315-73.01-31.627-94.03c11.573,8.235,50.022,38.673,76.25,94.03H306.07z M215.553,35.46
									c-9.312,21.02-21.855,53.544-31.624,94.032h-44.628C165.532,74.13,203.984,43.692,215.553,35.46z M177.44,160.117
									c-3.95,21.645-6.867,44.959-8.019,69.572h-54.828c1.383-25.861,5.968-49.028,12.65-69.572H177.44z M83.976,229.688H31.401
									c1.747-24.545,7.481-47.984,16.83-69.572h46.902C89.122,181.002,85.204,204.246,83.976,229.688z M114.577,260.313h54.424
									c0.348,22.454,2.237,45.716,6.241,69.572h-47.983C120.521,309.288,115.92,286.115,114.577,260.313z M181.584,360.51
									c7.512,31.183,18.67,63.054,34.744,95.053c-10.847-7.766-50.278-38.782-77.013-95.053H181.584z M273.635,455.632
									c16.094-32.022,27.262-63.916,34.781-95.122h42.575C324.336,417.068,284.736,447.827,273.635,455.632z M314.759,329.885
									c4.005-23.856,5.894-47.118,6.241-69.572h54.434c-1.317,25.849-5.844,49.016-12.483,69.572H314.759z M406.051,260.313h52.548
									c-1.748,24.545-7.482,47.985-16.831,69.572h-46.694C401.041,308.996,404.882,285.736,406.051,260.313z M406.019,229.688
									c-1.228-25.443-5.146-48.686-11.157-69.572h46.908c9.35,21.587,15.083,45.026,16.83,69.572H406.019z M425.309,129.492h-41.242
									c-13.689-32.974-31.535-59.058-48.329-78.436C372.475,68.316,403.518,95.596,425.309,129.492z M154.252,51.06
									c-16.792,19.378-34.636,45.461-48.324,78.432H64.691C86.48,95.598,117.52,68.321,154.252,51.06z M64.692,360.51h40.987
									c13.482,32.637,31.076,58.634,47.752,78.034C117.059,421.262,86.318,394.148,64.692,360.51z M336.576,438.54
									c16.672-19.398,34.263-45.395,47.742-78.03h40.99C403.684,394.146,372.945,421.258,336.576,438.54z"/>
								</svg>

								<?php endif; ?>

							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif; ?>