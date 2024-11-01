<?php
/**
 * Description: Creating a structural menu in the form of an array, for further transfer of this array by ajax.
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

class HMI_Structure_Menu {
	/**
	 * Array of objects of all saved menus in the database.
	 */
	public $nav_menus = [];

	/**
	 * Registered navigation menu locations.
	 * If none are registered, an empty array.
	 */
	private $locations = [];

	/**
	 * Array of registered menu areas (menu locations) and menu IDs attached to each area.
	 */
	private $menu_locations = [];

	/**
	 * All items of seleceted menu.
	 */
	private $menu_items = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->nav_menus      = wp_get_nav_menus();
		// $this->locations      = get_registered_nav_menus();
		// $this->menu_locations = get_nav_menu_locations();
	}

	/**
	 * Get all nav menus.
	 * @return object $this->nav_menus Menu items object.
	 */
	public function get_all_items_menu(): array {
		// Retrieve menu locations.
		if ( current_theme_supports( 'menus' ) ) {
			$this->locations      = get_registered_nav_menus();
			$this->menu_locations = get_nav_menu_locations();
		}
		// Generate truncated menu names.
		foreach ( (array) $this->nav_menus as $key => $_nav_menu ) {
			$this->nav_menus[ $key ]->truncated_name = wp_html_excerpt( $_nav_menu->name, 40, '&hellip;' );
		}

		foreach ( (array) $this->nav_menus as $_nav_menu ) {

			if ( ! empty( $this->menu_locations ) && in_array( $_nav_menu->term_id, $this->menu_locations ) ) {
				$locations_assigned_to_this_menu = array();
				foreach ( array_keys( $this->menu_locations, $_nav_menu->term_id ) as $menu_location_key ) {
					if ( isset( $this->locations[ $menu_location_key ] ) ) {
						$locations_assigned_to_this_menu[] = $this->locations[ $menu_location_key ];
					}
				}
				/**
				 * Filters the number of locations listed per menu in the drop-down select.
				 *
				 * @since 3.6.0
				 *
				 * @param int $locations Number of menu locations to list. Default 3.
				 */
				$assigned_locations = array_slice( $locations_assigned_to_this_menu, 0, absint( apply_filters( 'wp_nav_locations_listed_per_menu', 3 ) ) );

				// Adds ellipses following the number of locations defined in $assigned_locations.
				if ( ! empty( $assigned_locations ) ) {
					$a = implode( ', ', $assigned_locations );
					$b = count( $locations_assigned_to_this_menu ) > count( $assigned_locations ) ? ' &hellip;' : '';
					$_nav_menu->truncated_name.= ' ('.$a.$b.')';
				}
			}
		}

		return $this->nav_menus;
	}
	/**
	 * Get items selected menu.
	 *
	 * @param string $term_id           Menu item post.
	 * @return object $this->menu_items Menu items object.
	 */
	public function get_items_selected_menu( $term_id ): array {

		$this->menu_items = wp_get_nav_menu_items( $term_id );

		foreach ($this->menu_items as $items) {
			$metas = get_post( $items->object_id, ARRAY_A  );
			$items->post_password = $metas['post_password'];
		}

		return $this->menu_items;
	}
}
