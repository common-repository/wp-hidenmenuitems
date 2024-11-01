<?php
/**
 * Description: Deletes the plugin settings from the database of the website.
 *
 * PHP version 8.0.1
 *
 * @category    uninstall.php
 * @package     WP-MenuItemsHiden
 * @author      Oleg Klenitsky <klenitskiy.oleg@mail.ru>
 * @version     1.0.0
 * @license     GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}
// Delete pwd of pages setting of this plugin.
$hmi_options = get_option( 'hmi_setting_fields', [] );
$my_post = array();
foreach ( $hmi_options[0] as $arr ) {
	foreach ( $arr as $key => $value ) {
		if ( $value ) {
			$my_post['ID'] = $key;
			$my_post['post_password'] = "";
			if ( ! wp_is_post_revision( $key ) ){
				wp_update_post( wp_slash($my_post) );
			}
		}
	}
}
// Delete options.
delete_option( 'hmi_setting_fields' );
