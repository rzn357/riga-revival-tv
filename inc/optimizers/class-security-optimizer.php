<?php
/**
 * Security optimizer class.
 *
 * @package RRTV\Optimizers\Classes
 */

namespace RRTV\Optimizers;

defined( 'ABSPATH' ) || exit;

/**
 * This class is used to handle security optimizations.
 */
class Security_Optimizer {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_filter( 'script_loader_tag', array( $this, 'add_additional_attributes' ), 10, 2 );
		add_filter( 'validate_username', array( $this, 'validate_username' ), 10, 2 );
		add_action( 'user_profile_update_errors', array( $this, 'user_profile_update_errors' ), 10, 3 );
	}

	/**
	 * Validate username during profile update and add error if it's forbidden.
	 *
	 * @param WP_Error $errors - The WP_Error object containing any validation errors.
	 * @param bool     $update - Whether the user is being updated or created.
	 * @param WP_User  $user   - The WP_User object of the user being updated or created.
	 *
	 * @return void
	 */
	public function user_profile_update_errors( $errors, $update, $user ) {
		$forbidden_names = array( 'admin', 'administrator', 'webmaster' );

		if ( ! $update && in_array( strtolower( $user->user_login ), $forbidden_names, true ) ) {
			if ( isset( $errors->errors['user_login'] ) ) {
				unset( $errors->errors['user_login'] );
			}

			$errors->add( 'username_forbidden', __( '<strong>Error</strong>: This username is forbidden for security reasons.', 'riga-revival-tv' ) );
		}
	}

	/**
	 * Validate username during registration.
	 *
	 * @param bool   $valid    - Whether the username is valid.
	 * @param string $username - The username being validated.
	 *
	 * @return bool Whether the username is valid after checking against forbidden names.
	 */
	public function validate_username( $valid, $username ) {
		// Array of forbidden usernames.
		$forbidden = array( 'admin', 'administrator', 'webmaster' );

		if ( in_array( strtolower( $username ), $forbidden, true ) ) {
			return false;
		}

		return $valid;
	}

	/**
	 * Add additional attributes to script tags.
	 *
	 * @param string $tag    - The script tag.
	 * @param string $handle - The script handle.
	 *
	 * @return string The modified script tag.
	 */
	public function add_additional_attributes( $tag, $handle ) {
		if ( 'google-maps' === $handle ) {
			return str_replace( ' src', ' crossorigin="anonymous" src', $tag );
		}
		return $tag;
	}
}
