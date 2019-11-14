<?php
/**
 * Plugin Name: FlutterApp
 * Plugin URI: https://github.com/AlastorReach
 * Description: An Wordpress toolkit to implement configurations to a Flutter App
 * Version: 1.0.0
 * Author: Josué Mora González
 * Author URI: https://github.com/AlastorReach
 * Text Domain: flutterapp
 *
 * @package FlutterApp
 */
 
 // Define WC_PLUGIN_FILE.
if ( ! defined( 'FA_PLUGIN_FILE' ) ) {
	define( 'FA_PLUGIN_FILE', __FILE__ );
}
 
 // Include the main Flutter App class.
if ( ! class_exists( 'FlutterApp' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-flutterapp.php';
}

function FA() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return FlutterApp::instance();
}

// Global for backwards compatibility.
$GLOBALS['flutterapp'] = FA();

?>
