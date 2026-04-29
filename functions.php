<?php
/**
 * Theme functions and definitions
 *
 * @package RRTV
 */

defined( 'ABSPATH' ) || exit;

define( 'RRTV_THEME_VERSION', '1.0.0' );
define( 'RRTV_TEXT_DOMAIN', 'riga-revival-tv' );
define( 'RRTV_THEME_DIR_PATH', get_template_directory() );
define( 'RRTV_THEME_DIR_URL', get_template_directory_uri() );

require_once RRTV_THEME_DIR_PATH . '/inc/class-autoloader.php';
require_once RRTV_THEME_DIR_PATH . '/vendor/autoload.php';

use RRTV\Autoloader;
use RRTV\Riga_Revival_Tv;

// Init classes autoloader.
new Autoloader();

// Run theme.
$GLOBALS[ Riga_Revival_Tv::$theme_prefix_lowercase ] = new Riga_Revival_Tv(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
