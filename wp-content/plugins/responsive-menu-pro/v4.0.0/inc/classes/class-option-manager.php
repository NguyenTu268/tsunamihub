<?php
/**
 * This file contain the Option_Manager class and it's functionalities.
 *
 * @version 4.0.0
 * @author  Expresstech System
 *
 * @package responsive-menu-pro
 */

namespace RMP\Features\Inc;

use RMP\Features\Inc\Traits\Singleton;
use responsive_menu_pro\frontend\RMP_Menu;

// Disable the direct access to this class.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Option_Manager
 * This class is responsible for provide the options for menu that
 * maybe global or specific menu options.
 *
 * @version 4.0.0
 */
class Option_Manager {

	use Singleton;

	/**
	 * Construct method.
	 */
	protected function __construct() {
		$this->setup_hooks();
	}

	/**
	 * To setup action/filter.
	 *
	 * @version 4.0.0
	 *
	 * @return void
	 */
	protected function setup_hooks() {
	}

	/**
	 * This function get all options stored in rmp_menu post meta for menu.
	 *
	 * @access public
	 * @param int $menu_id    Menu id
	 *
	 * @return array $options Array of backend setting options.
	 */
	public function get_options( $menu_id ) {

		$options            = get_post_meta( $menu_id, 'rmp_menu_meta', true );
		$options            = is_array( $options ) ? $options : array();
		$options['menu_id'] = $menu_id;
		$default_options    = rmp_get_default_options();
		$options            = array_replace( $default_options, $options );
		if ( ! empty( $default_options['items_order'] ) ) {
			$default_items_order  = $default_options['items_order'];
			$existing_items_order = ! empty( $options['items_order'] ) && is_array( $options['items_order'] ) ? $options['items_order'] : array();

			if ( ! empty( $existing_items_order ) ) {
				$existing_items_order = array_filter(
					$existing_items_order,
					function ( $value, $key ) use ( $default_items_order ) {
						return array_key_exists( $key, $default_items_order );
					},
					ARRAY_FILTER_USE_BOTH
				);
				$missing_items           = array_diff_key( $default_items_order, $existing_items_order );
				$options['items_order']   = $existing_items_order + $missing_items;
			} else {
				$options['items_order'] = $default_items_order;
			}
		}
		return $options;

	}

	/**
	 * This function get all options stored in table for responsive menu.
	 *
	 * @access public
	 * @param int $menu_id    Menu id
	 *
	 * @return array $options Array of backend setting options.
	 */
	public function get_option( $menu_id, $key ) {

		$options         = $this->get_options( $menu_id );
		$default_options = rmp_get_default_options();

		if ( ! empty( $options[ $key ] ) ) {
			return $options[ $key ];
		} elseif ( ! empty( $default_options[ $key ] ) ) {
			return $default_options[ $key ];
		}

	}

	/**
	 * Return the global setting options.
	 *
	 * @version 4.0.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_global_options() {

		$global_settings = get_option( 'rmp_global_setting_options' );

		if ( ! empty( $global_settings ) ) {
			return $global_settings;
		}

		return array();
	}

	/**
	 * Return global option
	 *
	 * @version 4.0.0
	 *
	 * @access public
	 * @param string $key Option name.
	 *
	 * @return string|null
	 */
	public function get_global_option( $key ) {

		$global_options = $this->get_global_options();

		if ( ! empty( $global_options[ $key ] ) ) {
			return $global_options[ $key ];
		}

		return;
	}

	/**
	 * Returns the mega menu options for an single item.
	 *
	 * @version 4.0.0
	 * @access public
	 *
	 * @param int $menu_id Menu ID.
	 * @param int $item_id Menu item ID.
	 *
	 * @return array
	 */
	public function mega_menu_options( $menu_id, $item_id ) {

		$option_meta = get_post_meta( $menu_id, '_rmp_mega_menu_' . $item_id );

		if ( empty( $option_meta[0] ) ) {
			return array();
		}

		return $option_meta[0];
	}

	/**
	 * Return the options of mobile device.
	 *
	 * @version 4.0.0
	 *
	 * @return array
	 */
	public function get_mobile_options( $menu_id ) {

		if ( empty( $menu_id ) ) {
			return;
		}

		$options = get_post_meta( $menu_id, '_mobile' );
		if ( ! empty( $options ) ) {
			return $options[0];
		}

		return array();
	}

	/**
	 * Return the options of desktop device.
	 *
	 * @version 4.0.0
	 *
	 * @return array
	 */
	public function get_desktop_options( $menu_id ) {

		if ( empty( $menu_id ) ) {
			return;
		}

		$options = get_post_meta( $menu_id, '_desktop' );
		if ( ! empty( $options ) ) {
			return $options[0];
		}

		return array();
	}

	/**
	 * Return the options of tablet device.
	 *
	 * @version 4.0.0
	 *
	 * @return array
	 */
	public function get_tablet_options( $menu_id ) {

		if ( empty( $menu_id ) ) {
			return;
		}

		$options = get_post_meta( $menu_id, '_tablet' );
		if ( ! empty( $options ) ) {
			return $options[0];
		}

		return array();
	}

}
