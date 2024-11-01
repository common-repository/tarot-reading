<?php
/**
 * @package TarotReading
 */
/*
Plugin Name: Tarot reading
Plugin URI: 
Description: タロット占いコンテンツを設置できるプラグインです。タロット占いアニメーションが表示され、設定した結果ページを表示することが出来ます。
Version: 1.0.0
Author: cory Inc.
Author URI: https://amory.jp/
License: GPLv2
Text Domain: tarot-reading
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define('TAROTREADING__PLUGIN_DIR', plugin_dir_path( __FILE__ ));

require_once(TAROTREADING__PLUGIN_DIR . 'class.tarot_reading.php');
add_action('init', array('TarotReading', 'init'));


if (is_admin()) {
	require_once(TAROTREADING__PLUGIN_DIR . 'class.tarot_reading_admin.php' );
	add_action('init', array('TarotReadingAdmin', 'init'));
}
