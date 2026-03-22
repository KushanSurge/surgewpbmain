<?php
/**
 * Plugin Name: SurgeWP Boilerplate
 * Plugin URI:  https://surge.global
 * Description: A modular, shortcode-based WordPress plugin boilerplate.
 * Version:     1.0.0
 * Author:      Surge Global
 * Text Domain: surgewpb
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

/**
** Plugin Constants
**/
define( 'SURGEWPB_VERSION',    '1.0.0' );
define( 'SURGEWPB_DIR',        plugin_dir_path( __FILE__ ) );
define( 'SURGEWPB_URL',        plugin_dir_url( __FILE__ ) );
define( 'SURGEWPB_TEXTDOMAIN', 'surgewpb' );

/**
** Dev Mode — set to true to enable the SurgeWP Dev admin pages
** and disable asset caching (versions become time()).
**/
define( 'SURGEWPBP_IS_DEV', false );

/**
** Load Core Files
**/
require_once SURGEWPB_DIR . 'includes/core/lib-loader.php';
require_once SURGEWPB_DIR . 'includes/core/asset-loader.php';
require_once SURGEWPB_DIR . 'includes/core/loader.php';
require_once SURGEWPB_DIR . 'includes/core/dev-admin.php';

/**
** Boot Plugin
**/
add_action( 'plugins_loaded', 'surgewpb_init' );

function surgewpb_init() {
	Surgewpb_Loader::get_instance()->boot();
}

/**
** Enqueue Common Assets
**/
add_action( 'wp_enqueue_scripts', 'surgewpb_enqueue_common_assets' );

function surgewpb_enqueue_common_assets() {
	$ver = SURGEWPBP_IS_DEV ? time() : SURGEWPB_VERSION;

	wp_enqueue_style(
		'surgewpb-common-css',
		SURGEWPB_URL . 'includes/common/surgewpb-common.css',
		[],
		$ver
	);

	wp_enqueue_script(
		'surgewpb-common-js',
		SURGEWPB_URL . 'includes/common/surgewpb-common.js',
		[ 'jquery' ],
		$ver,
		true
	);

	wp_localize_script( 'surgewpb-common-js', 'surgewpb_data', [
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'surgewpb_nonce' ),
	] );
}
