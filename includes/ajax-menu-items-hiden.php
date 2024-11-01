<?php
/**
 * Description:
 *
 * PHP version 8.0.1
 *
 * @category   ajax-menu-items-hiden.php
 * @package    WP-HidenMenuItems
 * @author     Oleg Klenitsky <klenitskiy.oleg@mail.ru>
 * @version    1.0.0
 * @license    GPLv2 or later
 */

/**
 * Save $arr[0][$hmi_selectMenuTermID] = $hmi_items_selected_menu  to option( 'hmi_setting_fields', $arr ).
 * Save $arr[1][$hmi_selectMenuTermID] = $hmi_items_users_site  to option( 'hmi_setting_fields', $arr ).
 * Save post_password to wp_posts table of db.
 * Send $items_selected_menu, $hmi_chkbox_items, $hmi_items_users_site, $arrPwd of selected menu from server.
 */
function hmi_menu_items_ajax() {
	$_hmi_all_menus     = filter_input( INPUT_POST, 'all_menus', FILTER_SANITIZE_STRING );
	$_hmi_selected_menu = filter_input( INPUT_POST, 'selected_menu', FILTER_SANITIZE_STRING );
	$_hmi_nonce         = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );

	if ( !isset( $_hmi_nonce ) ) {
		wp_die( 'ajax-menu-items-hiden: nonce is empty.' );
	}
	if ( $_hmi_nonce !== wp_create_nonce( 'hmi' ) ) {
		wp_die( 'ajax-menu-items-hiden: nonce is incorrect.' );
	}
	// Save settings of plugin to server.
	if ( $_hmi_all_menus ) {

		$hmi_all_menus = json_decode( htmlspecialchars_decode( $_hmi_all_menus ) );

		//Save post_password.
		$my_post = array();
		foreach ( $hmi_all_menus as $hmi_all_menu ) {
			$pages_id  = $hmi_all_menu->pages_id;
			$pages_pwd = $hmi_all_menu->pages_pwd;
			foreach ( $pages_id as $key => $page_id ) {
				$my_post['ID'] = $page_id;
				$my_post['post_password'] = $pages_pwd[$key];
				if ( ! wp_is_post_revision( $page_id ) ) {
					wp_update_post( wp_slash($my_post) );
				}
			}
		}
		// Save settings to option( 'hmi_setting_fields' )
		update_option( 'hmi_setting_fields', $hmi_all_menus );

		echo("ok");
	}
	// Send items_selected_menu of selected menu from server.
	if ( $_hmi_selected_menu ) {
		$hmi_menus         	 = new HMI_Structure_Menu();
		$hmi_selected_menu   = json_decode( htmlspecialchars_decode( $_hmi_selected_menu ) );
		$items_selected_menu = $hmi_menus->get_items_selected_menu( $hmi_selected_menu );

		$hmi_options = get_option( 'hmi_setting_fields' );
		if ( $hmi_options ) {
			foreach( $hmi_options as $key => $value ) {
				$menu_id = "menu_id_" . $hmi_selected_menu;
				if ( $menu_id == $key ) {
					$options_selected_menu = $value;
					break;
				} else {
					foreach( $items_selected_menu as $key => $item_selected_menu) {
						$options_selected_menu["pages_id"][] = $item_selected_menu->object_id;
						$options_selected_menu["pages_pwd"][] = $item_selected_menu->post_password;
						$options_selected_menu["users_id"][] = [];
					}
				}
			}
		} else {
			foreach( $items_selected_menu as $key => $item_selected_menu) {
				$options_selected_menu["pages_id"][] = $item_selected_menu->object_id;
				$options_selected_menu["pages_pwd"][] = $item_selected_menu->post_password;
				$options_selected_menu["users_id"][] = [];
			}
		}
		echo( json_encode( [$items_selected_menu, $options_selected_menu] ) );
	}

	wp_die();
}
