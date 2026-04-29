<?php
/**
 * Template part for displaying playlists section.
 *
 * @package RRTV\TemplateParts
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

$rrtv_current_program_data = $args['current_program_data'] ?? array();
$rrtv_youtube_service      = $args['youtube_service'] ?? null;

if ( empty( $rrtv_current_program_data ) || ! $rrtv_youtube_service ) {
	return;
}
?>

<section class="playlists">
	<div class="container">
		<?php if ( $rrtv_current_program_data['playlists'] ) : ?>
			<?php foreach ( $rrtv_current_program_data['playlists'] as $rrtv_playlist ) : ?>
				<?php
				get_template_part(
					'components/playlist',
					null,
					array(
						'rrtv_playlist'        => $rrtv_playlist,
						'rrtv_youtube_service' => $rrtv_youtube_service,
					)
				);
				?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</section>
