<?php
/**
 * Accessibility optimizer class.
 *
 * @package RRTV\Optimizers\Classes
 */

namespace RRTV\Optimizers;

defined( 'ABSPATH' ) || exit;

/**
 * This class is used to handle accessibility optimizations.
 */
class Accessibility_Optimizer {

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
		add_filter( 'wpcf7_form_elements', array( $this, 'add_aria_label_to_select' ) );
	}

	/**
	 * Add aria-label to the select field of a specific Contact Form 7 form.
	 *
	 * @param string $form_html - The form HTML.
	 *
	 * @return string Modified form HTML.
	 */
	public function add_aria_label_to_select( $form_html ) {
		// Add aria-label only to the <select> with name="topic".
		$form_html = preg_replace(
			'/(<select[^>]*name="topic"[^>]*)>/i',
			'$1 aria-label="Select topic">',
			$form_html
		);

		return $form_html;
	}
}
