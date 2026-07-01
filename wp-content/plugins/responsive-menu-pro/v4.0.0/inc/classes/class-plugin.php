<?php
/**
 * Plugin manifest class.
 *
 * @package responsive-menu-pro
 */

namespace RMP\Features\Inc;

use RMP\Features\Inc\Elementor\Elementor_Manager;
use RMP\Features\Inc\Traits\Singleton;

/**
 * Class Plugin
 */
class Plugin {

	use Singleton;

	/**
	 * Construct method.
	 */
	protected function __construct() {
		// Load plugin classes.
		Admin::get_instance();
		Assets::get_instance();
		Editor::get_instance();
		Editor_Manager::get_instance();
		Preview::get_instance();
		Control_Manager::get_instance();
		Theme_Manager::get_instance();
		Option_Manager::get_instance();
		Style_Manager::get_instance();
		UI_Manager::get_instance();
		Widget_Manager::get_instance();
		RMP_Migration::get_instance();
		Elementor_Manager::get_instance();

		$this->setup_hooks();
	}

	/**
	 * To setup action/filter.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		add_action( 'plugins_loaded', [ $this, 'rmp_load_plugin_text_domain' ] );
		add_action( 'admin_notices', [ $this, 'rmp_deactivate_free_version_notice' ] );
		add_action( 'admin_notices', [ $this, 'no_menu_admin_notice' ] );

		add_action( 'admin_notices', [ $this, 'rmp_license_upgrade_admin_notice' ] );
		add_action( 'plugin_action_links_' . plugin_basename( RMP_PLUGIN_FILE ) , [ $this, 'rmp_license_upgrade_link' ] );
		add_action( "wp_ajax_rmp_license_admin_notice_dismiss", [ $this, 'rmp_license_admin_notice_dismiss' ] );

		// Check current config and environment support wp_body_open or not.
		if ( $this->has_support( 'wp_body_open' ) ) {
			add_action( 'wp_body_open' , [ $this, 'menu_render_on_frontend' ] );
		} else {
			add_action( 'wp_footer' , [ $this, 'menu_render_on_frontend' ] );
		}
	}

	/**
	 * Function to show the admin notice when no menu exist.
	 *
	 * @since 4.1.0
	 *
	 * @return void
	 */
	public function no_menu_admin_notice() {

		//Check post type.
		$post_type = get_post_type();
		if ( empty( $post_type ) && ! empty( $_GET['post_type'] ) ) {
			$post_type = $_GET['post_type'];
		}

		if ( 'rmp_menu' !== $post_type || ! empty( $_GET['page'] ) ) {
			return;
		}

		// Count all post which are in list except trash.
		$post_count = 0;
		foreach ( wp_count_posts( 'rmp_menu' ) as $status => $count ) {

			if ( 'trash' == $status ) {
				continue;
			}

			$post_count += $count;
		}

		if ( $post_count >= 1 ) {
			return;
		}

		printf(
			'<div class="notice notice-error">
				<p> %s <a href="%s" target="_blank"> documentation </a> </p>
			</div>',
			__( 'Responsive menu list is empty. Create a menu by clicking the <b>Create New Menu</b> button. For more details visit ', 'responsive-menu-pro' ),
			esc_url( 'https://responsive.menu/knowledgebase/responsive-menu-4-0-overview/' )
		);
	}

	/**
	 * Function to show the admin notice if plugin deactivate.
	 *
	 * @return void
	 */
	public function rmp_deactivate_free_version_notice() {

		if ( get_transient('og-admin-notice-activation') ) {
			printf(
				'<div class="notice notice-error is-dismissible">
				<p>%s</p>
				</div>',
				__('Responsive Menu has been deactivated','responsive-menu-pro' )
			);

			delete_transient('og-admin-notice-activation');
		}
	}

	/**
	 * Load plugin text domain.
	 *
	 * @version 4.0.0
	 *
	 * @return void
	 */
	public function rmp_load_plugin_text_domain() {
		load_plugin_textdomain( 'responsive-menu-pro', false, RMP_PLUGIN_DIR_NAME . '/v4.0.0/languages' );
	}

	/**
	 * Add plugin upgrade link.
	 *
	 * Add a link to the settings page on the responsive menu page.
	 *
	 * @param  array  $links List of existing plugin action links.
	 * @return array         List of modified plugin action links.
	 */
	function rmp_license_upgrade_link( $links ) {

		$rmp_license_item_id = get_option('rmp_license_item_id');

		if ( ! empty( $rmp_license_item_id ) ) {
			return $links;
		}

		$links = array_merge(
			$links,
			array( '<a class="responsive-menu-license-upgrade-link" href="' . esc_url( admin_url( 'edit.php?post_type=rmp_menu&page=settings' ) ) . '">' . __( 'Connect & Upgrade', 'responsive-menu-pro') . '</a>' )
		);

		return $links;
	}

	/**
	 * Function to hide the admin notice permanent.
	 */
	function rmp_license_admin_notice_dismiss() {
		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'responsive_menu_admin_notice', true );
	}


	/**
	 * Function to add the admin notice template.
	 *
	 * @version 4.0.0
	 *
	 */
	public function rmp_license_upgrade_admin_notice() {
		$rmp_license_item_id = get_option('rmp_license_item_id');

		if ( ! empty( $rmp_license_item_id ) ) {
			return;
		}

		$post_type = get_post_type();
		if ( empty( $post_type ) && ! empty( $_GET['post_type'] ) ) {
			$post_type = $_GET['post_type'];
		}

		if ( 'rmp_menu' !== $post_type ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! empty( get_user_meta( $user_id, 'responsive_menu_admin_notice') ) ) {
			return;
		}

		include_once RMP_PLUGIN_PATH_V4 . '/templates/admin-notices.php';
	}

	/**
	 * Function to render tthe nenu on frontend.
	 *
	 * @version 4.0.0
	 */
	function menu_render_on_frontend() {
		$responsive_menu_id = '';
		if ( isset($_REQUEST['rmp_preview_mode']) ) {
			$responsive_menu_id = $_REQUEST['menu_id'];
		}
		$option_manager  = Option_Manager::get_instance();
		$menu_ids   = get_all_rmp_menu_ids();

		if ( empty( $menu_ids ) ) {
			return;
		}

		foreach ( $menu_ids as $menu_id ) {
			$menu_show_on = $option_manager->get_option( $menu_id, 'menu_display_on' );
			$page_ids     = $option_manager->get_option( $menu_id, 'menu_show_on_pages' );
			$menu_show_by_user = $option_manager->get_option( $menu_id, 'menu_display_by_users' );

			if ( ! empty( $menu_show_on ) &&
				( 'shortcode' === $menu_show_on ||
				( 'exclude-pages' === $menu_show_on && in_array( get_queried_object_id(), $page_ids ) ) ||
				( 'include-pages' === $menu_show_on && ! in_array( get_queried_object_id(), $page_ids ) ) ||
				( 'parent-and-children' === $menu_show_on && ! in_array( get_queried_object_id(), $page_ids ) && ! in_array( wp_get_post_parent_id(get_the_ID()), $page_ids ) ) ||
				( ('logged-in-users' === $menu_show_by_user && ! is_user_logged_in() ) || ( $responsive_menu_id != '' && $responsive_menu_id != $menu_id ) ) ||
				( 'guest-users' === $menu_show_by_user && is_user_logged_in() && $responsive_menu_id == '' )
				) ) {
				continue;
			}

			//If WPML is activated
			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$menu_to_use = $option_manager->get_option( $menu_id, 'menu_to_use' );
				$menu_wpml_id = $this->get_wpml_menu_id( $menu_to_use );

				if ( $menu_to_use != $menu_wpml_id ) {
					continue;
				}           
}

			$menu = new \RMP\Features\Inc\RMP_Menu( $menu_id );
			$menu->build_new_menu();
		}
	}

	/**
	 * Check support of wp_body_open for plugins and themes.
	 *
	 * @since 4.0.0
	 *
	 * @param string $hook Name of hook.
	 * @return boolean
	 */
	public function has_support( $hook ) {

		// Check wp footer option is enabled.
		$option_manager  = Option_Manager::get_instance();
		if ( 'wp_body_open' == $hook && 'on' == $option_manager->get_global_option( 'rmp_wp_footer_hook' ) ) {
			return false;
		}

		// Check wp core support wp_body_open hook or not.
		if ( ! has_action( $hook ) ) {
			return false;
		}

		// If is_plugin_active function not exist then add plugin.php file from core.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		// List of unsupported themes and plugins.
		$unsupported_extensions = [
			'oxygen/functions.php',
		];

		foreach ( $unsupported_extensions as $extension ) {
			if ( is_plugin_active( $extension ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Function to return WPML menu id if exists.
	 *
	 * @param int $menu_id
	 *
	 * @return int
	 */
	public function get_wpml_menu_id( $menu_id ) {
		$menu_item_id = apply_filters( 'wpml_object_id', $menu_id, 'nav_menu', TRUE , ICL_LANGUAGE_CODE );
		return $menu_item_id;
	}

}
