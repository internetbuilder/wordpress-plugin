<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Plugin Name: MailChimp Forms by Optin.Guru
 * Version: 1.0.0
 * Plugin URI: https://optin.guru/
 * Description: Optin forms builder, compatible with MailChimp and 25+ other popular integrations.
 * Author: CodeLights
 * Author URI: https://optin.guru/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: optinguru
 */

// Global variables for plugin usage (global declaration is needed here for WP CLI compatibility)
global $ogr_file, $ogr_dir, $ogr_uri, $ogr_version;
$ogr_file = __FILE__;
$ogr_dir = plugin_dir_path( __FILE__ );
$ogr_uri = plugins_url( '', __FILE__ );
$ogr_version = preg_match( '~Version\: ([^\n]+)~', file_get_contents( __FILE__, NULL, NULL, 82, 150 ), $ogr_matches ) ? $ogr_matches[1] : FALSE;
unset( $ogr_matches );

add_action( 'init', 'ogr_init' );
function ogr_init() {
	$owner_id = get_option( 'optinguru_owner_id' );
	if ( ! is_admin() AND $owner_id !== FALSE ) {
		add_action( 'wp_enqueue_scripts', 'ogr_enqueue_scripts' );
		add_filter( 'script_loader_tag', 'ogr_script_loader_tag', 10, 2 );
	}
}

if ( is_admin() ) {
	require $ogr_dir . 'functions/admin_pages.php';
}

function ogr_enqueue_scripts() {
	global $ogr_version;
	wp_enqueue_script( 'optinguru-api', 'https://dev.optin.guru/OptinGuru.js', array(), $ogr_version, TRUE );
}

function ogr_script_loader_tag( $tag, $handle ) {
	if ( $handle !== 'optinguru-api' ) {
		return $tag;
	}
	
	return '<script type="text/javascript" id="optinguru-api" src="https://dev.optin.guru/OptinGuru.js" data-owner="' . get_option( 'optinguru_owner_id' ) . '" async="async"></script>';
}

add_action( 'admin_enqueue_scripts', 'ogr_admin_enqueue_scripts' );
function ogr_admin_enqueue_scripts( $hook ) {
	if ( $hook !== 'tools_page_og-settings' AND $hook !== 'admin_page_og-connect' ) {
		return;
	}

	global $ogr_uri, $ogr_version;
	wp_enqueue_style( 'ogr-main', $ogr_uri . '/css/main.css', array(), $ogr_version );
	wp_enqueue_script( 'ogr-main', $ogr_uri . '/js/main.js', array( 'jquery' ), $ogr_version );
}

register_activation_hook( $ogr_file, 'ogr_activate' );
function ogr_activate() {
	//	header( 'Location: ' . admin_url( 'tools.php?page=og-settings' ) );
}

register_uninstall_hook( $ogr_file, 'ogr_uninstall' );
function ogr_uninstall() {
	// Options cleanup
	foreach ( array( 'owner_id', 'website_id', 'token' ) as $option_name ) {
		delete_option( 'optinguru_' . $option_name );
	}
}