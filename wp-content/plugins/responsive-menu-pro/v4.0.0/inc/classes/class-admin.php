<?php
/**
 * Admin class.
 * This is core class which is responsible for admin functionality.
 *
 * @version 4.0.0
 * @author  Expresstech System
 *
 * @package responsive-menu-pro
 */

namespace RMP\Features\Inc;

use RMP\Features\Inc\License\Check;
use RMP\Features\Inc\Traits\Singleton;
use RMP\Features\Inc\RMP_Menu;
use RMP\Features\Inc\Theme_Manager;
use RMP\Features\Inc\Option_Manager;

// Disable the direct access to this class.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin
 */
class Admin {

	use Singleton;

	/**
	 * Instance of Option Manager class.
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      object.
	 */
	protected static $option_manager;

	/**
	 * Construct method.
	 */
	protected function __construct() {
		$this->setup_hooks();
	}

	/**
	 * To setup action/filter.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		add_action( 'wp_ajax_rmp_save_global_settings', array( $this, 'save_menu_global_settings' ) );
		add_action( 'wp_ajax_rmp_rollback_version', array( $this, 'rollback_version' ) );
		add_action( 'wp_ajax_rmp_license_key_validation', array( $this, 'check_license_key_validation' ) );
		add_action( 'wp_ajax_rmp_create_new_menu', array( $this, 'create_new_menu' ) );
		add_action( 'wp_ajax_rmp_export_menu', array( $this, 'rmp_export_menu' ) );
		add_action( 'wp_ajax_rmp_import_menu', array( $this, 'rmp_import_menu' ) );

		add_action( 'plugins_loaded', array( $this, 'check_plugin_update' ) );
		add_shortcode( 'rmp_menu', array( $this, 'register_menu_shortcode' ) );

		add_action( 'init', array( $this, 'rmp_menu_cpt' ), 0 );

		add_filter( 'post_row_actions', array( $this, 'rmp_menu_row_actions' ), 10, 2 );
		add_filter( 'get_edit_post_link', array( $this, 'rmp_edit_post_link' ), 10, 2 );

		add_filter( 'manage_rmp_menu_posts_columns', array( $this, 'set_custom_edit_menu_columns' ) );
		add_action( 'manage_rmp_menu_posts_custom_column', array( $this, 'add_custom_columns' ), 10, 2 );
		add_action( 'admin_footer', array( $this, 'add_new_menu_widget' ) );
		add_action( 'admin_menu', array( $this, 'rmp_register_submenu_page' ) );
		add_action( 'admin_menu', array( $this, 'remove_default_add_cpt_page' ) );
		add_action( 'rmp_create_new_menu', array( $this, 'set_global_options' ), 10, 0 );
	}

	/**
	 * Check the plugin update and add notification on plugin page.
	 *
	 * @version 4.0.0
	 *
	 * @return void
	 */
	public function check_plugin_update() {

		if ( ! is_admin() ) {
			return;
		}

		require_once RMP_PLUGIN_PATH_V4 . '/inc/license/Check.php';

		$updater = new Check(
			'https://responsive.menu',
			RMP_PLUGIN_FILE,
			array(
				'version' => RMP_PLUGIN_VERSION,
				'license' => trim( get_option( 'responsive_menu_pro_license_key' ) ),
				'item_id' => get_option( 'rmp_license_item_id' ),
				'author'  => 'Responsive Menu',
				'url'     => home_url(),
			)
		);
	}

	/**
	 * Function to save the global settings of setting page.
	 *
	 * @return json
	 */
	public function save_menu_global_settings() {

		check_ajax_referer( 'rmp_nonce', 'ajax_nonce' );
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( __( 'You can\'t edit global settings!', 'responsive-menu-pro' ) );
		}

		$options = array();
		wp_parse_str( $_POST['form'], $options );

		foreach ( $options as $key => $value ) {
			$options[ $key ] = sanitize_text_field( $value );
		}

		update_option( 'rmp_global_setting_options', $options );

		/**
		 * Fires after global settings is saved.
		 *
		 * @since 4.0.0
		 *
		 * @param array $option List of global settings.
		 */
		do_action( 'rmp_save_global_settings', $options );

		wp_send_json_success( 'Saved' );
	}

	/**
	 * Rollback to older version from setting page.
	 *
	 * @since   4.0.0
	 *
	 * @return void
	 */
	public function rollback_version() {

		if ( empty( update_option( 'is_rmp_new_version', 0 ) ) ) {
			add_option( 'is_rmp_new_version', 0 );
		}

		wp_send_json_success( array( 'redirect' => admin_url( 'admin.php?page=responsive-menu-pro' ) ) );
	}

	/**
	 * Function to validated the license key in setting page.
	 *
	 * @return json
	 */
	public function check_license_key_validation() {

		check_ajax_referer( 'rmp_nonce', 'ajax_nonce' );

		$rmp_license_key = sanitize_text_field( $_POST['rmp_license_key'] );

		$this->license( $rmp_license_key );
	}

	/**
	 * Check an entered license key and apply it.
	 *
	 * This route is called when the Add License Key button is pressed inside
	 * the admin area. It checks the provided license key for validity and
	 * updates the license status for the account.
	 *
	 * @author Expresstech System
	 *
	 * @since 4.0.0
	 *
	 * @param string $license_key   Provided license key.
	 *
	 * @return string                   Output HTML from rendered view.
	 */
	public function license( $license_key ) {

		$license_key = trim( $license_key );
		$alert       = array();

		if ( empty( $license_key ) ) {
			$alert = array( 'danger' => 'No license key added' );
			update_option( 'rmp_license_item_id', '' );
			update_option( 'responsive_menu_pro_license_key', '' );
		} else {

			$item_id = '77643'; // This product id.

			// First product checking.
			$response = wp_remote_get(
				'https://responsive.menu/?' . http_build_query(
					array(
						'edd_action' => 'activate_license',
						'license'    => $license_key,
						'item_id'    => $item_id,
						'url'        => home_url(),
					)
				),
				array( 'decompress' => false )
			);

			if ( is_wp_error( $response ) ) {
				$alert = array( 'danger' => $response->get_error_message() . ' - Please <a href="https://responsive.menu/faq/license-activation-issues" target="_blank"> click here</a> for more information.' );
			} else {
				$response = json_decode( $response['body'] );
			}

			/* Parse Result */
			if ( empty( $response->success ) ) {

				$item_id = '72872'; // This product id.

				// Second product checking.
				$response = wp_remote_get(
					'https://responsive.menu/?' . http_build_query(
						array(
							'edd_action' => 'activate_license',
							'license'    => $license_key,
							'item_id'    => $item_id,
							'url'        => home_url(),
						)
					),
					array( 'decompress' => false )
				);

				if ( is_wp_error( $response ) ) {
					$alert = array( 'danger' => $response->get_error_message() . ' - Please <a href="https://responsive.menu/faq/license-activation-issues" target="_blank"> click here</a> for more information.' );
				} else {
					$response = json_decode( $response['body'] );
				}
			}

			if ( ! empty( $response->success ) ) {
				update_option( 'rmp_license_item_id', $item_id );
				$alert = array( 'success' => 'License key updated' );
			} else {
				update_option( 'rmp_license_item_id', '' );
				if ( ! is_wp_error( $response ) ) {
					$alert = array( 'danger' => 'License key invalid' . ' - Please <a href="https://responsive.menu/knowledgebase/license-activation-issues/" target="_blank"> click here</a> for more information.' );
				}
			}

			update_option( 'responsive_menu_pro_license_key', $license_key );
		}

		return wp_send_json_success( array( 'alert' => $alert ) );
	}

	/**
	 * Function to create a new theme.
	 *
	 * @since   4.0.0
	 *
	 * @return json
	 */
	public function create_new_menu() {

		check_ajax_referer( 'rmp_nonce', 'ajax_nonce' );
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( array( 'message' => __( 'You can not create menu !', 'responsive-menu-pro' ) ) );
		}

		$menu_name = sanitize_text_field( $_POST['menu_name'] );
		if ( empty( $menu_name ) ) {
			wp_send_json_error( array( 'message' => __( 'Enter the Menu name !', 'responsive-menu-pro' ) ) );
		}

		$menu_to_use = sanitize_text_field( $_POST['menu_to_use'] );
		if ( empty( $menu_to_use ) ) {
			wp_send_json_error( array( 'message' => __( 'Select menu to use !', 'responsive-menu-pro' ) ) );
		}

		$menu_to_hide = sanitize_text_field( $_POST['menu_to_hide'] );

		$menu_theme = '';
		if ( ! empty( $_POST['menu_theme'] ) ) {
			$menu_theme = sanitize_text_field( $_POST['menu_theme'] );
		}

		$theme_type = '';
		if ( ! empty( $_POST['theme_type'] ) ) {
			$theme_type = sanitize_text_field( $_POST['theme_type'] );
		}

		$use_in_desktop = sanitize_text_field( $_POST['use_in_desktop'] );
		$use_in_tablet  = sanitize_text_field( $_POST['use_in_tablet'] );
		$use_in_mobile  = sanitize_text_field( $_POST['use_in_mobile'] );
		$menu_show_on   = sanitize_text_field( $_POST['menu_show_on'] );

		$menu_show_on_pages = array();
		if ( ! empty( $_POST['menu_show_on_pages'] ) && is_array( $_POST['menu_show_on_pages'] ) ) {
			foreach ( $_POST['menu_show_on_pages']  as $key => $val ) {
				$menu_show_on_pages[ $key ] = sanitize_text_field( $val );
			}
		}

		$theme_options = array();

		// Get appropriate theme as per theme type and theme name.
		if ( ! empty( $theme_type ) && 'downloaded' == $theme_type ) {
			$theme_manager = Theme_Manager::get_instance();
			$theme_options = $theme_manager->get_available_theme_settings( $menu_theme );
		} elseif ( ! empty( $theme_type ) && 'template' == $theme_type ) {
			$theme_manager = Theme_Manager::get_instance();
			$theme_options = $theme_manager->get_saved_theme_options( $menu_theme );
		} else {
			$theme_options = rmp_get_default_options();
		}

		// Create menu as post with rmp_menu cpt.
		$new_menu = array(
			'post_title'  => wp_strip_all_tags( $menu_name ),
			'post_author' => get_current_user_id(),
			'post_status' => 'publish',
			'post_type'   => 'rmp_menu',
		);

		$menu_id = wp_insert_post( $new_menu );

		$new_options = array(
			'menu_name'          => $menu_name,
			'menu_to_use'        => $menu_to_use,
			'menu_theme'         => $menu_theme,
			'theme_type'         => $theme_type,
			'menu_display_on'    => $menu_show_on,
			'menu_show_on_pages' => $menu_show_on_pages,
			'use_desktop_menu'   => $use_in_desktop,
			'use_tablet_menu'    => $use_in_tablet,
			'use_mobile_menu'    => $use_in_mobile,
			'menu_id'            => $menu_id,
			'menu_to_hide'       => $menu_to_hide,
		);

		$new_options = array_merge( $theme_options, $new_options );

		if ( ! empty( $menu_id ) ) {

			// Update options.
			if ( ! empty( $new_options['mobile_options'] ) ) {
				update_post_meta( $menu_id, '_mobile', $new_options['mobile_options'] );
			}

			if ( ! empty( $new_options['desktop_options'] ) ) {
				update_post_meta( $menu_id, '_desktop', $new_options['desktop_options'] );
			}

			if ( ! empty( $new_options['tablet_options'] ) ) {
				update_post_meta( $menu_id, '_tablet', $new_options['tablet_options'] );
			}

			update_post_meta( $menu_id, 'rmp_menu_meta', $new_options );

			/**
			 * Fires when menu is created and options is saved.
			 *
			 * @param int $menu_id Menu ID.
			 */
			do_action( 'rmp_create_new_menu', $menu_id );

			wp_send_json_success(
				array(
					'message'       => __( 'Menu is created successfully', 'responsive-menu-pro' ),
					'customize_url' => sprintf(
						'%spost.php?post=%s&action=edit&editor=true',
						get_admin_url(),
						$menu_id
					),
				)
			);

		} else {
			wp_send_json_error( array( 'message' => __( 'Unable to create new Menu !', 'responsive-menu-pro' ) ) );
		}

	}

	/**
	 * This function register the shortcode for menu.
	 *
	 * @since  4.0.0
	 *
	 * @param  Array  $atts    Attributes List.
	 * @param  string $content It contain text from shortcode.
	 *
	 * @return HTML   $output  Menu contents.
	 */
	public function register_menu_shortcode( $attrs = array() ) {

		$attrs = shortcode_atts( array( 'id' => '' ), $attrs );

		$attrs = array_change_key_case( (array) $attrs, CASE_LOWER );

		$styleObj = Style_Manager::get_instance();

		// Check given id is valid.
		if ( empty( $attrs['id'] ) ) {
			return __( 'Please pass menu id as attribute.', 'responsive-menu-pro' );
		}

		$menu_id = $attrs['id'];
		if ( 'publish' !== get_post_status( $menu_id ) ) {
			return __( "Shortcode with menu id $menu_id is not published.", 'responsive-menu-pro' );
		}

		// Check shortcode option is activated or not.
		$option_manager    = Option_Manager::get_instance();
		$option            = $option_manager->get_option( $menu_id, 'menu_display_on' );
		$menu_show_by_user = $option_manager->get_option( $menu_id, 'menu_display_by_users' );

		if ( 'shortcode' !== $option ) {
			return __( 'Shortcode deactivated', 'responsive-menu-pro' );
		}

		// If WPML is activated
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$menu_to_use  = $option_manager->get_option( $menu_id, 'menu_to_use' );
			$menu_wpml_id = apply_filters( 'wpml_object_id', $menu_to_use, 'nav_menu', true, ICL_LANGUAGE_CODE );

			if ( $menu_to_use != $menu_wpml_id ) {
				return;
			}
		}

		$responsive_menu_id = '';
		if ( isset( $_REQUEST['rmp_preview_mode'] ) ) {
			$responsive_menu_id = $_REQUEST['menu_id'];
		}

		if ( ( ( 'logged-in-users' === $menu_show_by_user && ! is_user_logged_in() ) ||
			( $responsive_menu_id != '' && $responsive_menu_id != $menu_id ) ) ||
			( 'guest-users' === $menu_show_by_user && is_user_logged_in() && $responsive_menu_id == '' )
			) {
			return;
		}

		$styleObj->add_rmp_menu_frontend_scripts_files();

		if ( 'on' != $option_manager->get_global_option( 'rmp_external_files' ) ) {
			$css = $styleObj->get_menus_scss_to_css();
			printf(
				'<style id="rmp-inline-menu-styles">%s</style>',
				$css
			);
		}

		ob_start();

		$menu = new RMP_Menu( $menu_id );
		$menu->build_new_menu();

		return ob_get_clean();
	}

	/**
	 * Function to update the global options.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function set_global_options() {

		$global_settings = get_option( 'rmp_global_setting_options' );
		if ( empty( $global_settings ) ) {
			$default_options = rmp_global_default_setting_options();
			update_option( 'rmp_global_setting_options', $default_options );
		}
	}

	/**
	 * Add sub menu pages in responsive menu admin.
	 *
	 * @since   4.0.0
	 */
	public function rmp_register_submenu_page() {

		add_submenu_page(
			'edit.php?post_type=rmp_menu',
			__( 'Settings', 'responsive-menu-pro' ),
			__( 'Settings', 'responsive-menu-pro' ),
			'manage_options',
			'settings',
			array( $this, 'rmp_global_settings_page' )
		);

		add_submenu_page(
			'edit.php?post_type=rmp_menu',
			__( 'Themes', 'responsive-menu-pro' ),
			__( 'Themes', 'responsive-menu-pro' ),
			'manage_options',
			'themes',
			array( $this, 'rmp_theme_admin_page' )
		);

		add_submenu_page(
			'edit.php?post_type=rmp_menu',
			__( 'What\'s Next', 'responsive-menu-pro' ),
			__( 'What\'s Next', 'responsive-menu-pro' ),
			'manage_options',
			'whats-next',
			array( $this, 'rmp_roadmap_admin_page' )
		);

	}

	/**
	 * Add template for roadmap page.
	 *
	 * @since   4.0.1
	 */
	public function rmp_roadmap_admin_page() {

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		include_once RMP_PLUGIN_PATH_V4 . '/templates/rmp-roadmap.php';
	}

	/**
	 * Add template to the themes page.
	 *
	 * @since   4.0.0
	 */
	public function rmp_theme_admin_page() {

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		include_once RMP_PLUGIN_PATH_V4 . '/templates/rmp-themes.php';
	}

	/**
	 * Add template to the setting page.
	 *
	 * @since   4.0.0
	 *
	 * @return void
	 */
	public function rmp_global_settings_page() {

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		include_once RMP_PLUGIN_PATH_V4 . '/templates/rmp-settings.php';
	}

	/**
	 * Remove create new menu default link of rmp_menu post type.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	function remove_default_add_cpt_page() {
		remove_submenu_page( 'edit.php?post_type=rmp_menu', 'post-new.php?post_type=rmp_menu' );
	}

	/**
	 * Function to add the new menu wizard template.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function add_new_menu_widget() {
		$screen = get_current_screen();
		if ( $screen->id === 'edit-rmp_menu' ) {
			include_once RMP_PLUGIN_PATH_V4 . '/templates/new-menu-wizard.php';
		}
	}

	/**
	 * Function to change the edit label and url.
	 *
	 * @since 4.0.0
	 *
	 * @param array  $actions List of post row actions.
	 * @param Object $post Post object
	 *
	 * @return array $actions
	 */
	public function rmp_menu_row_actions( $actions, $post ) {

		if ( 'rmp_menu' == $post->post_type ) {
			$actions['edit'] = sprintf(
				'<a href="%s" aria-label="Edit">%s</a>',
				esc_url( get_edit_post_link( $post->ID ) ),
				__( 'Customize', 'responsive-menu-pro' )
			);
		}

		return $actions;
	}

	/**
	 * Function to add the custom column.
	 *
	 * @since 4.0.0
	 *
	 * @param array $columns List of columns.
	 *
	 * @return array $columns Edited columns list.
	 */
	public function set_custom_edit_menu_columns( $columns ) {

		unset( $columns['date'] );
		$columns['shortcode_place'] = __( 'Shortcode', 'responsive-menu-pro' );
		$columns['actions']         = __( 'Actions', 'responsive-menu-pro' );
		$columns['date']            = __( 'Date', 'responsive-menu-pro' );

		return $columns;
	}

	/**
	 * Function to change the edit url of post type rmp_menu
	 *
	 * @since 4.0.0
	 *
	 * @param string $url     Post edit URL.
	 * @param int    $post_id Post ID
	 *
	 * @return string $url    Edited post url URL
	 */
	public function rmp_edit_post_link( $url, $post_id ) {

		if ( 'rmp_menu' === get_post_type() && current_user_can( 'edit_post', $post_id ) ) {
			$url = get_admin_url() . 'post.php?post=' . $post_id . '&action=edit&editor=true';
		}

		return $url;
	}

	/**
	 * Function to add the data to the custom columns for the rmp_menu post type.
	 *
	 * @since 4.0.0
	 *
	 * @param string $column  Column Name
	 * @param int    $post_id Post ID
	 *
	 * @return void
	 */
	function add_custom_columns( $column, $post_id ) {
		$option_manager = Option_Manager::get_instance();

		switch ( $column ) {

			case 'actions':
				echo sprintf(
					'<a href="%s" class="button" aria-label="Customize">%s</a>',
					esc_url( get_edit_post_link( $post_id ) ),
					__( 'Customize', 'responsive-menu-pro' )
				);
				break;
			case 'shortcode_place':
				$option = $option_manager->get_option( $post_id, 'menu_display_on' );
				if ( 'shortcode' === $option ) {
					echo sprintf( '<code>[rmp_menu id="%s"]</code>', $post_id );
				} else {
					esc_html_e( 'Shortcode deactivated', 'responsive-menu-pro' );
				}

				break;

		}
	}

	/**
	 * Register rmp_menu custom post type.
	 *
	 * @since 4.0.0
	 */
	public function rmp_menu_cpt() {

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$labels = array(
			'name'               => __( 'Responsive Menu', 'responsive-menu-pro' ),
			'singular_name'      => __( 'Responsive Menu', 'responsive-menu-pro' ),
			'menu_name'          => __( 'Responsive Menu', 'responsive-menu-pro' ),
			'parent_item_colon'  => __( 'Parent Menu', 'responsive-menu-pro' ),
			'all_items'          => __( 'Menus', 'responsive-menu-pro' ),
			'view_item'          => __( 'View Menu', 'responsive-menu-pro' ),
			'add_new_item'       => __( 'Add New Menu', 'responsive-menu-pro' ),
			'add_new'            => __( 'Create New Menu', 'responsive-menu-pro' ),
			'edit_item'          => __( 'Edit Menu', 'responsive-menu-pro' ),
			'update_item'        => __( 'Update Menu', 'responsive-menu-pro' ),
			'search_items'       => __( 'Search Menu', 'responsive-menu-pro' ),
			'not_found'          => __( 'Not Found', 'responsive-menu-pro' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'responsive-menu-pro' ),
		);

		$args = array(
			'label'               => __( 'Responsive Menu', 'responsive-menu-pro' ),
			'description'         => __( 'Responsive Menu', 'responsive-menu-pro' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'author' ),
			'public'              => false,
			'hierarchical'        => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'has_archive'         => false,
			'can_export'          => false,
			'exclude_from_search' => true,
			'taxonomies'          => array(),
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'menu_icon'           => RMP_PLUGIN_URL_V4 . '/assets/images/rmp-logo.png',
		);

		register_post_type( 'rmp_menu', $args );

		/**
		 * This action will be useful when need hooks after cpt register.
		 *
		 * @param CPT rmp_menu
		 */
		do_action( 'rmp_after_cpt_registered', 'rmp_menu' );
	}

	/**
	 * Function to export the menu
	 *
	 * @since   4.0.0
	 *
	 * @return json
	 */
	public function rmp_export_menu() {

		check_ajax_referer( 'rmp_nonce', 'ajax_nonce' );

		$menu_id = sanitize_text_field( $_POST['menu_id'] );
		if ( empty( $menu_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Select menu !', 'responsive-menu-pro' ) ) );
		}

		if ( ! current_user_can( 'edit_post', $menu_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You can not export menu !', 'responsive-menu-pro' ) ) );
		}

		$option_manager = Option_Manager::get_instance();
		$option         = $option_manager->get_options( $menu_id );

		$option['mobile_options']  = $option_manager->get_mobile_options( $menu_id );
		$option['tablet_options']  = $option_manager->get_tablet_options( $menu_id );
		$option['desktop_options'] = $option_manager->get_desktop_options( $menu_id );

		// Add widgets in menu setting export file.
		if ( ! empty( $option['mega_menu'] ) ) {
			foreach ( $option['mega_menu'] as $id => $status ) {
				$mega_menu_item = get_post_meta( $menu_id, '_rmp_mega_menu_' . $id );
				if ( ! empty( $mega_menu_item[0]['rows'] ) ) {
					foreach ( $mega_menu_item[0]['rows'] as $row_id => $row ) {
						foreach ( $row['columns'] as $col_id => $col ) {
							foreach ( $col['menu_items'] as $widget_id => $item ) {
								if ( ! empty( $item['item_type'] ) && 'widget' === $item['item_type'] ) {
									$widget_manager = Widget_Manager::get_instance();
									$widget_setting = $widget_manager->rmp_get_widget_setting( $item['item_id'] );
									$mega_menu_item[0]['rows'][ $row_id ]['columns'][ $col_id ]['menu_items'][ $widget_id ]['widget_settings'] = $widget_setting;
								}
							}
						}
					}
				}

				$option['mega_menu_settings'][ $id ] = $mega_menu_item;
			}
		}

		wp_send_json_success( json_encode( $option ) );
	}

	/**
	 * Function to import the menu settings.
	 *
	 * @since   4.0.0
	 *
	 * @return json
	 */
	public function rmp_import_menu() {

		check_ajax_referer( 'rmp_nonce', 'ajax_nonce' );

		if ( empty( $_FILES['file']['name'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Please add file !', 'responsive-menu-pro' ) ) );
		}

		$file_type = pathinfo( basename( $_FILES['file']['name'] ), PATHINFO_EXTENSION );

		if ( empty( $_FILES['file']['tmp_name'] ) || 'json' != $file_type ) {
			wp_send_json_error( array( 'message' => __( 'Please add json file !', 'responsive-menu-pro' ) ) );
		}

		$menu_id = sanitize_text_field( $_POST['menu_id'] );
		if ( empty( $menu_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Select menu !', 'responsive-menu-pro' ) ) );
		}

		if ( ! current_user_can( 'edit_post', $menu_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You can not import menu !', 'responsive-menu-pro' ) ) );
		}

		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();

		$file_contents  = isset( $_FILES['file']['tmp_name'] ) ? $wp_filesystem->get_contents( wp_unslash( $_FILES['file']['tmp_name'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$import_options = json_decode( $file_contents, true );

		$option_manager = Option_Manager::get_instance();
		$exist_option   = $option_manager->get_options( $menu_id );

		// Some required options replced in imported settings with existing menu settings.
		$import_options['menu_name']             = $exist_option['menu_name'];
		$import_options['theme_type']            = 'default';
		$import_options['menu_theme']            = null;
		$import_options['menu_to_use']           = $exist_option['menu_to_use'];
		$import_options['menu_to_use_in_mobile'] = $exist_option['menu_to_use_in_mobile'];

		// Update options.
		if ( ! empty( $import_options['mobile_options'] ) ) {
			update_post_meta( $menu_id, '_mobile', $import_options['mobile_options'] );
		}

		if ( ! empty( $import_options['desktop_options'] ) ) {
			update_post_meta( $menu_id, '_desktop', $import_options['desktop_options'] );
		}

		if ( ! empty( $import_options['tablet_options'] ) ) {
			update_post_meta( $menu_id, '_tablet', $import_options['tablet_options'] );
		}

		// Get new menu item ids and mega menu options.
		$menu_items        = rmp_get_wp_nav_menu_items( $import_options );
		$mega_item_ids     = array();
		$mega_item_options = array();
		if ( ! empty( $menu_items ) && is_array( $menu_items ) ) {
			foreach ( $menu_items as $item ) {

				if ( ! empty( $item->menu_item_parent ) ) {
					continue;
				}

				$mega_item_options[ $item->ID ] = 'off';
				$mega_item_ids[]                = $item->ID;
			}
		}

		$mega_menu_settings = $mega_menu_item = array();
		if ( ! empty( $import_options['mega_menu_settings'] ) ) {
			$mega_menu_settings = $import_options['mega_menu_settings'];
		}

		if ( ! empty( $import_options['mega_menu'] ) ) {
			// Start from first menu item.
			$item_id = 0;
			foreach ( $import_options['mega_menu'] as $id => $status ) {
				$mega_menu_item = $mega_menu_settings[ $id ];

				// Add widgets in sidebar.
				if ( ! empty( $mega_menu_item[0]['rows'] ) ) {
					foreach ( $mega_menu_item[0]['rows'] as $row_id => $row ) {
						foreach ( $row['columns'] as $col_id => $col ) {
							foreach ( $col['menu_items'] as $widget_id => $item ) {
								if ( ! empty( $item['item_type'] ) && 'widget' === $item['item_type'] ) {
									$widget_type = substr( $item['item_id'], 0, strrpos( $item['item_id'], '-' ) );

									// Create new widgets with imported widget settings.
									$widget_number = $this->insert_widget_in_sidebar(
										$widget_type,
										$item['widget_settings'],
										'rmp-sidebar'
									);

									// Update widget number( item_id) in mega menu item setting.
									if ( ! empty( $widget_number ) ) {
										$mega_menu_item[0]['rows'][ $row_id ]['columns'][ $col_id ]['menu_items'][ $widget_id ]['item_id'] = $widget_number;
										unset( $mega_menu_item[0]['rows'][ $row_id ]['columns'][ $col_id ]['menu_items'][ $widget_id ]['widget_settings'] );
									}
								}
							}
						}
					}
				}

				// Check the item is enable for mega menu.
				if ( 'on' == $status ) {
					$mega_item_options[ $mega_item_ids[ $item_id ] ] = 'on';
				}

				// Update the mega menu item settings.
				if ( ! empty( $mega_menu_item[0] ) ) {
					update_post_meta( $menu_id, '_rmp_mega_menu_' . $mega_item_ids[ $item_id ], $mega_menu_item[0] );
				}

				$item_id++;
			}
		}

		// Update the mega menu item ids with new menu items.
		$import_options['mega_menu'] = $mega_item_options;

		update_post_meta( $menu_id, 'rmp_menu_meta', $import_options );
		/**
		 * Fires when menu is imported.
		 *
		 * @since 4.0.0
		 *
		 * @param int $menu_id
		 */
		do_action( 'rmp_import_menu', $menu_id );

		wp_send_json_success( array( 'message' => __( 'Menu settings imported successfully!', 'responsive-menu-pro' ) ) );
	}

	/**
	 * Insert a widget in a sidebar.
	 *
	 * @param string $widget_id   ID of the widget (search, recent-posts, etc.)
	 * @param array  $widget_data  Widget settings.
	 * @param string $sidebar     ID of the sidebar.
	 */
	public function insert_widget_in_sidebar( $widget_id, $widget_data, $sidebar ) {
		// Retrieve sidebars, widgets and their instances
		$sidebars_widgets = get_option( 'sidebars_widgets', array() );
		$widget_instances = get_option( 'widget_' . $widget_id, array() );

		// Retrieve the key of the next widget instance
		$numeric_keys = array_filter( array_keys( $widget_instances ), 'is_int' );
		$next_key     = $numeric_keys ? max( $numeric_keys ) + 1 : 2;

		// Add this widget to the sidebar
		if ( ! isset( $sidebars_widgets[ $sidebar ] ) ) {
			$sidebars_widgets[ $sidebar ] = array();
		}
		$sidebars_widgets[ $sidebar ][] = $widget_id . '-' . $next_key;

		// Add the new widget instance
		$widget_instances[ $next_key ] = $widget_data;

		// Store updated sidebars, widgets and their instances
		update_option( 'sidebars_widgets', $sidebars_widgets );
		update_option( 'widget_' . $widget_id, $widget_instances );

		return $widget_id . '-' . $next_key;
	}

}
