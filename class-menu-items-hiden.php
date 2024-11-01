<?php
/**
 * Description: Used to create an administrative control panel for the plugin.
 *
 * PHP version 8.0.1
 *
 * @category    Class
 * @package     WP-HidenMenuItems
 * @author      Oleg Klenitsky <klenitskiy.oleg@mail.ru>
 * @version     1.0.0
 * @license     GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class HMI_Main {
	/**
	 * Objects of all menus and this structures.
	 *
	 * @var object
	 */
	public $hmi_menus;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->hmi_menus = new HMI_Structure_Menu();
		if ( is_admin() ) {
			// admin actions.
			add_action( 'admin_menu', array( $this, 'hmi_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'hmi_register_mysettings' ) );
			add_action( 'admin_head', array( $this, 'hmi_screen_options' ) );
			add_filter( 'plugin_row_meta', array( $this, 'hmi_plugin_meta' ), 10, 2 );
		} else {
			// non-admin enqueues, actions, and filters.
			add_action( 'pre_get_posts', array( $this,'hmi_page_post_hiden' ) );
			add_filter( 'wp_get_nav_menu_items', array( $this, 'hmi_menu_items_hiden' ), 10, 3 );
		}
	}

	/**
	 * Forbide the display of a hidden page or post.
	 *
	 * @param object Object WP_Query.
	 */
	public function hmi_page_post_hiden( $query ) {
		$hmi_options = get_option('hmi_setting_fields');

		if ( !$hmi_options ) return( $query );

		if( ! is_admin() && $query->queried_object ) {
			if( !empty( $query->queried_object->post_password ) ) {
				if ( is_user_logged_in() ) {
					$current_user_id = get_current_user_id();
					foreach ( $hmi_options as $hmi_option ) {
						$index = array_search( $query->queried_object->ID, $hmi_option->pages_id);
						if ( false !== $index ) {
							$allowed = array_search($current_user_id, $hmi_option->users_id[$index]);
							if ( false === $allowed ) {
								$query->is_404 = true;
							}
						}
					}
				} else {
					$query->is_404 = true;
				}
			}
		}
	}

	/**
	 * Hide items menu.
	 * used only $items[object_id]
	 *
	 * @param string $items An array of menu item post objects.
	 * @param string $menu  The menu object.
	 * @param string $args  An array of arguments used to retrieve menu item objects.
	 * @return object $filtered_items Мenu items object.
	 */
	public function hmi_menu_items_hiden( $items, $menu, $args ) {
		$hmi_options = get_option('hmi_setting_fields');

		$filtered_items = [];
		if ( !$hmi_options ) return( $items );

		foreach ( $hmi_options as $hmi_option ) {
			foreach ( $items as $item ) {
				$index = array_search( $item->object_id, $hmi_option->pages_id);
				if ( false !== $index ) {
					if ( empty( $hmi_option->pages_pwd[$index] ) ) {
						// not pwd.
						$filtered_items[] = $item;
					} else {
						if ( is_user_logged_in() ) {
							$current_user_id = get_current_user_id();
							$allowed = array_search($current_user_id, $hmi_option->users_id[$index]);
							if ( false !== $allowed ) {
								$filtered_items[] = $item;
							}
						}
					}
				}
			}
		}
		return $filtered_items;
	}

	/**
	 * Adds the metadata displayed for the plugin to the plugins table.
	 *
	 * @param string $items The setting item will be displayed in the plugin data.
	 * @param string $file  The path to the plugin file relative to the plugin directory.
	 * @return object $meta Items displayed in plugin data.
	 */
	public function hmi_plugin_meta( $meta, $file ) {

		if ( 'wp-hiden-menu-items/wp-menu-items-hiden.php' == $file ) {
			$hmi_url_setting = get_admin_url( null, 'themes.php?page=hiden-menu-items', 'https' );
			$row_meta["Setting"] =  "<a href=". esc_url( $hmi_url_setting )."  target='_blank'>Setting</a>";

			$meta = array_merge( $meta, $row_meta );
		}
		return $meta;
	}

	/**
	 * Create help on page of plugin.
	 */
	public function hmi_screen_options() {
		// execute only on hiden-menu-items pages, otherwise return null.
		$_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		if ( 'hiden-menu-items' !== $_page ) {
			return;
		}
			get_current_screen()->add_help_tab(
				array(
					'id'      => 'hmi-tab-1',
					'title'   => esc_html( '1. Select menu', 'hmi' ),
					'content' => '<p>' . esc_html( 'Select the menu in which, in step 2 of the settings, the pages, posts for hiding them will be defined.', 'hmi' ) . '</p>',
				)
			);
			get_current_screen()->add_help_tab(
				array(
					'id'      => 'hmi-tab-2',
					'title'   => esc_html( '2. Select menu items to hide', 'hmi' ),
					'content' => '<p>' . esc_html( 'Select items menu that will be hidden to all site visitors. Hidden pages, posts will not be accessible even at the direct URL, as well as for search robots.', 'hmi' ) . '</p>',
				)
			);
			get_current_screen()->add_help_tab(
				array(
					'id'      => 'hmi-tab-3',
					'title'   => esc_html( '3. Select users who are allowed access to hidden items menu', 'hmi' ),
					'content' => '<p>' . esc_html( 'Select from the list of registered visitors who will be allowed access to the hidden pages, posts of the site.', 'hmi' ) . '</p>',
				)
			);
			get_current_screen()->add_help_tab(
				array(
					'id'      => 'hmi-tab-4',
					'title'   => esc_html( '4. structure of option hmi_setting_fields (for example)', 'hmi' ),
					'content' => '<pre>' . esc_html( 'O:8:"stdClass":2:{
	s:9:"menu_id_2";O:8:"stdClass":3:{
		s:8:"pages_id";a:7:{
			i:0;s:2:"17";i:1;s:2:"10";i:2;s:2:"12";i:3;s:1:"3";i:4;s:2:"26";i:5;s:2:"89";i:6;s:2:"87";
		}s:9:"pages_pwd";a:7:{
			i:0;s:3:"123";i:1;s:0:"";i:2;s:3:"456";i:3;s:3:"789";i:4;s:0:"";i:5;s:0:"";i:6;s:0:"";
		}s:8:"users_id";a:7:{
			i:0;a:2:{i:0;s:1:"5";i:1;s:1:"6";}
			i:1;a:0:{}
			i:2;a:2:{i:0;s:1:"2";i:1;s:1:"4";}
			i:3;a:2:{i:0;s:1:"3";i:1;s:1:"5";}
			i:4;a:0:{}
			i:5;a:0:{}
			i:6;a:0:{}
		}
	}s:9:"menu_id_3";O:8:"stdClass":3:{
		s:8:"pages_id";a:5:{
			i:0;s:2:"59";i:1;s:2:"62";i:2;s:2:"65";i:3;s:2:"72";i:4;s:2:"75";
		}s:9:"pages_pwd";a:5:{
			i:0;s:3:"rty";i:1;s:0:"";i:2;s:0:"";i:3;s:3:"asd";i:4;s:0:"";
		}s:8:"users_id";a:5:{
			i:0;a:3:{i:0;s:1:"1";i:1;s:1:"3";i:2;s:1:"6";}
			i:1;a:0:{}
			i:2;a:0:{}
			i:3;a:2:{i:0;s:1:"2";i:1;s:1:"4";}
			i:4;a:0:{}
		}
	}
}', 'hmi' ) . '</pre>',
				)
			);
			// Help sidebars are optional.
			get_current_screen()->set_help_sidebar(
				'<p><strong>' . esc_html( 'Additional information:', 'hmi' ) . '</strong></p>' .
				'<p><a href="https://wordpress.org/plugins/wp-hidenmenuitems/" target="_blank">' . esc_html( 'page the WordPress repository', 'hmi' ) . '</a></p>' .
				'<p><a href="https://adminkov.bcr.by/contact/" target="_blank">' . esc_html( 'home page support plugin', 'hmi' ) . '</a></p>'
			);
	}

	/**
	 * Create custom plugin settings menu.
	 */
	function hmi_admin_menu() {
		//create new custom menu
		add_theme_page('Control of hiden pages and post',
						'Hiden menu items',
						'edit_theme_options',
						'hiden-menu-items',
						array( $this, 'hiden_menu_items') );
	}

	/**
	 * Create register settings function.
	 */
	function hmi_register_mysettings() {
		//register custom settings
		register_setting( 'hmi-settings-group', 'hmi_setting_field1' );
		register_setting( 'hmi-settings-group', 'hmi_setting_field2' );
		register_setting( 'hmi-settings-group', 'hmi_setting_field3' );
	}

	/**
	 * Create page settings of plugin.
	 */
	function hiden_menu_items() {
		$plugine_info = get_plugin_data( __DIR__ . '/wp-menu-items-hiden.php' );
		?>
		<span class="dashicons dashicons-hidden" style="float: left;"></span>
		<div class="wrap">
		<h2 id="hmi-head"><?php echo esc_attr( $plugine_info['Name'] ) . ': ' . esc_html( 'settings', 'hmi' ); ?></h2>
		<form method="post" action="options.php">
		    <?php settings_fields( 'hmi-settings-group' ); ?>
		    <table class="form-table">
		        <tr valign="top">
		        <th scope="row">1. Select menu</th>
		        <td><?php $this->hmi_setting_field1(); ?></td>
		        </tr>

		        <tr valign="top">
		        <th scope="row">2. Select menu items to hide</th>
		        <td><?php $this->hmi_setting_field2(); ?></td>
		        </tr>

		        <tr valign="top">
		        <th scope="row">3. Select users who are allowed access to hidden items menu</th>
		        <td><?php $this->hmi_setting_field3(); ?></td>
		        </tr>
		    </table>
		    <input type="button" class="button-primary" value="<?php echo __("Save", "hmi"); ?>" onClick="hmi_save_settings()"/>
		</form>
		</div>
		<?php
	}

	/**
	 * Filling option1 (Select a menu to edit).
	 */
	public function hmi_setting_field1() {
		// Get all nav menus.
		$nav_menus  = $this->hmi_menus->get_all_items_menu();
		?>
		<select name="menu" id="all-menus">
			<?php
			foreach ( (array) $nav_menus as $_nav_menu ) { ?>
				<option value="<?php echo esc_attr( $_nav_menu->term_id ); ?>">
					<?php echo esc_attr( $_nav_menu->truncated_name );?>
				</option>
			<?php
			}
			?>
		</select>
		<input type="button" class="button" value="Select" onClick="hmi_menu_select()" />
		<?php
	}

	/**
	 * Filling option2 (Select an item to hide the page for all users).
	 */
	public function hmi_setting_field2() {
		// Receive items of premier menu from array.
		$menu_items  = $this->hmi_menus->get_items_selected_menu($this->hmi_menus->nav_menus[0]->term_id);

		if ($menu_items) {
			$nav_menus  = $this->hmi_menus->get_all_items_menu();
			$first_item = array_shift($nav_menus);
			$report = "Selected menu: " . $first_item->truncated_name . ", Menu items: (" . count($menu_items).")";
			?>
			<p id="report1"><?php echo esc_attr( $report ); ?></p>
			<div id="hmi_menu_items">
				<table id="table_menu_items">
					<thead>
						<tr>
							<th style="width:15%;">Status</th>
							<th style="width:50%;">Menu items</th>
							<th>Password</th>
						</tr>
					</thead>
					<tbody  id="body_menu_items">
					<?php
					foreach ( $menu_items as $key => $menu_item ) {
					?>
					<tr>
						<?php
						If ( '0' === $menu_item->menu_item_parent ) {
							$padding_left = "margin-left: 0;";
						} else {
							$padding_left = "margin-left: 15px;";
						}
						if ( 0 === $key ) {
							$checked = "checked";
						} else {
							$checked = "";
						}
						?>
						<td style="width:15%;">
							<input type="radio" id="<?php echo esc_attr( $menu_item->object_id ); ?>" name="hmi_items_menu" <?php echo esc_attr( $checked ); ?> title="<?php echo esc_attr( $menu_item->title ); ?>" onClick="hmi_item_menu_select( id, title )">
						</td>
						<td style="width:50%;">
							<a href="<?php echo esc_url( $menu_item->url ); ?>" style="<?php echo esc_attr( $padding_left ); ?>" target="_blank"><?php echo esc_attr( $menu_item->title ); ?></a>
						</td>
						<td>
							<input type="text" id="pwd_<?php echo esc_attr( $menu_item->object_id ); ?>" class="hmi_items_pwd" name="hmi_items_pwd" value="<?php echo esc_attr( $menu_item->post_password ); ?>" onchange="hmi_items_pwd_change(id,'<?php echo esc_attr( $menu_item->title ); ?>')" onclick="hmi_item_pwd_click(<?php echo esc_attr( $menu_item->object_id );?>,'<?php echo esc_attr( $menu_item->title ); ?>')">
						</td>
					</tr>
					<?php
					}
					?>
					</tbody>
				</table>
			</div>
			<?php
		}
	}

	/**
	 * Filling option3 (Select users to access of the page hiden).
	 */
	public function hmi_setting_field3() {
		WP_Filesystem();
		global $wp_filesystem;

		$hmi_options = get_option('hmi_setting_fields');
		if ( $hmi_options ) {
			foreach( $hmi_options as $hmi_option) {
				break;
			}
		}
		// Receive items of premier menu from array.
		$report = "Selected menu item: , Selected users:";
		?>
		<p id="report2"><?php echo esc_attr( $report ); ?></p>
		<div id="hmi_menu_users">
			<table id="table_menu_users">
				<thead>
					<tr>
						<th style="width:17%;">Status</th>
						<th style="width:35%;">User name</th>
						<th style="width:33%;">Role</th>
						<th>Country</th>
					</tr>
				</thead>
				<tbody id="body_menu_users">
				<?php
				$users = get_users();
				foreach ( $users as $user ) {
					$user_meta    = get_user_meta( $user->ID );
					$user_country = isset($user_meta["user_country"]) ? array_shift( $user_meta["user_country"] ) : "";
					$user_city    = isset($user_meta["user_city"]) ? array_shift( $user_meta["user_city"] ) : "";
					$img_flag     = "";
					$user_country = str_replace( array( "[", "]" ), "", $user_country );
					if ( is_dir( WP_PLUGIN_DIR . "/watchman-site7/" ) ) {
						$dir  = $wp_filesystem->find_folder(WP_PLUGIN_DIR . "/watchman-site7/images/flags/");
						$file = trailingslashit($dir) . $user_country . ".gif";

						if ($wp_filesystem->exists($file)) {
							$path_img = set_url_scheme( WP_PLUGIN_URL . "/watchman-site7/", "https" ) . "images/flags/" . $user_country . ".gif";
							$img_flag = "<image src='$path_img' >";
						}else{
							$img_flag = "";
							$user_country = "-";
						}
					}

					$avatar    = get_avatar( $user->ID, 20 );
					$user_role = array_shift( $user->roles );
					if ( isset( $hmi_option ) ) {
						$checked = ( false !== array_search($user->ID, $hmi_option->users_id[0]) ) ? "checked" : "";
					} else {
						$checked = "";
					}
				?>
					<tr>
						<td style="width:18%;"><input type="checkbox" name="hmi_users_site" id="<?php echo esc_attr( $user->ID ); ?>" onClick="hmi_items_users_select( id )" <?php echo esc_attr( $checked ); ?>/></td>
						<td style="width:36%;"><?php echo( $avatar ); echo esc_attr( ' ' . $user->user_login ); ?></td>
						<td style="width:35%;"><?php echo esc_attr( $user_role ); ?></td>
						<td title="City:<?php echo __( $user_city ); ?>"><?php echo ( $img_flag ); echo __( $user_country ); ?></td>
					</tr>
				<?php
				}
				?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
