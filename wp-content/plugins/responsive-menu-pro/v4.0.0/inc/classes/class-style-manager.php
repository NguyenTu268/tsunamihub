<?php
/**
 * This file contain the Style_Manager class and it's functions.
 *
 * @version 4.0.0
 * @author  Expresstech System
 *
 * @package responsive-menu-pro
 */

namespace RMP\Features\Inc;

use RMP\Features\Inc\Option_Manager;
use RMP\Features\Inc\Theme_Manager;
use RMP\Features\Inc\Traits\Singleton;
use ScssPhp\ScssPhp\Compiler;

// Disable the direct access to this class.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Style_Manager
 *
 * This class is responsible for handle the styling from frontend.
 *
 * @version 4.0.0
 */
class Style_Manager {

	use Singleton;

	/**
	 * Instance of option manager class.
	 *
	 * @version 4.0.0
	 *
	 * @var array $option_manager.
	 */
	protected $option_manager;

	/**
	 * Construct method.
	 */
	protected function __construct() {
		$this->option_manager = Option_Manager::get_instance();
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

		add_action( 'wp_head', array( $this, 'enqueue_styles_as_inline' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_rmp_menu_frontend_scripts' ) );
		add_action( 'rmp_create_new_menu', array( $this, 'save_style_css_on_file' ), 10, 0 );
		add_action( 'rmp_save_menu', array( $this, 'save_style_css_on_file' ), 10, 0 );
		add_action( 'rmp_update_mega_menu_item', array( $this, 'save_style_css_on_file' ), 10, 0 );
		add_action( 'rmp_save_global_settings', array( $this, 'save_style_css_on_file' ), 10, 0 );
		add_action( 'rmp_theme_apply', array( $this, 'save_style_css_on_file' ), 10, 0 );
		add_action( 'rmp_migrate_menu_style', array( $this, 'save_style_css_on_file' ), 10, 0 );
		add_action( 'rmp_import_menu', array( $this, 'save_style_css_on_file' ), 10, 0 );
		add_action('after_setup_theme', array( $this, 'rmp_add_classic_menu_support' ) );
		// Hide adminbar.
		if ( 'hide' == $this->option_manager->get_global_option( 'menu_adjust_for_wp_admin_bar' ) ) {
			add_filter( 'show_admin_bar', '__return_false' );
		}
	}

	/**
	 * Function to call the css generate for all menu one by one.
	 *
	 * @return string $css
	 */
	public function get_menus_scss_to_css() {

		$menu_ids = get_all_rmp_menu_ids();

		if ( empty( $menu_ids ) ) {
			return;
		}

		$css = '';

		foreach ( $menu_ids as $menu_id ) {
			$css .= $this->get_css_for_menu( $menu_id );
		}

		$css .= $this->get_common_scss_to_css();

		// Add custom css from setting page.
		$css .= $this->option_manager->get_global_option( 'rmp_custom_css' );

		// If minify is enable from settings then minify the style css.
		if ( 'on' === $this->option_manager->get_global_option( 'rmp_minify_scripts' ) ) {
			$css = $this->minify( $css );
		}

		return $css;
	}


	/**
	 * Function to save the css in files.
	 */
	public function save_style_css_on_file() {
		global $wp_filesystem;
		update_option('rmp_dynamic_file_version', current_time('H.i.s') );
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();

		$upload_dir = wp_upload_dir();

		$dir = trailingslashit( $upload_dir['basedir'] ) . 'rmp-menu/css/';

		if ( ! $wp_filesystem->is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		$css = $this->get_menus_scss_to_css();

		if ( ! $wp_filesystem->put_contents( $dir . 'rmp-menu.css', $css, 0644 ) ) {
			return new \WP_Error( 'Notice: Unable to write css in file.' );
		}

	}

	/**
	 * Function to minify the css to reduce the file size.
	 *
	 * @param string $row_css
	 *
	 * @return string $minified
	 */
	public function minify( $row_css ) {

		// Remove comments.
		$minified = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $row_css );

		// Remove tabs, spaces, newlines, etc.
		$minified = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '     ' ), '', $minified );

		// Remove other spaces before/after.
		$minified = preg_replace( array( '(( )+{)', '({( )+)' ), '{', $minified );
		$minified = preg_replace( array( '(( )+})', '(}( )+)', '(;( )*})' ), '}', $minified );
		$minified = preg_replace( array( '(;( )+)', '(( )+;)' ), ';', $minified );

		return $minified;
	}

	/**
	 * Function to combine the options of all the menus.
	 *
	 * @version 4.0.0
	 *
	 * @return array $options;
	 */
	public function get_all_menu_options() {
		$menu_ids     = get_all_rmp_menu_ids();
		$menu_options = array();

		foreach ( $menu_ids as $menu_id ) {
			$options                             = $this->option_manager->get_options( $menu_id );
			$options['active_toggle_contents']   = $this->get_active_toggle_contents( $options );
			$options['inactive_toggle_contents'] = $this->get_inactive_toggle_contents( $options );

			$menu_options[] = $options;
		}

		return $menu_options;
	}

	/**
	 * Function get the active item toggle icon.
	 *
	 * @version 4.0.0
	 *
	 * @param array options
	 * @return HTML
	 */
	public function get_active_toggle_contents( $options ) {

		if ( ! empty( $options['active_arrow_font_icon'] ) ) {
			return $options['active_arrow_font_icon'];
		} elseif ( ! empty( $options['active_arrow_image'] ) ) {
			return sprintf(
				'<img alt="%s" src="%s" />',
				rmp_image_alt_by_url( $options['active_arrow_image'] ),
				esc_url( $options['active_arrow_image'] )
			);
		} else {
			return $options['active_arrow_shape'];
		}
	}

	/**
	 * Function get the inactive item toggle icon.
	 *
	 * @version 4.0.0
	 *
	 * @param array options
	 * @return HTML
	 */
	public function get_inactive_toggle_contents( $options ) {

		if ( ! empty( $options['inactive_arrow_font_icon'] ) ) {
			return $options['inactive_arrow_font_icon'];
		} elseif ( ! empty( $options['inactive_arrow_image'] ) ) {
			return sprintf(
				'<img alt="%s" src="%s" />',
				rmp_image_alt_by_url( $options['inactive_arrow_image'] ),
				esc_url( $options['inactive_arrow_image'] )
			);
		} else {
			return $options['inactive_arrow_shape'];
		}
	}

	/**
	 * Add menu scripts for frontend.
	 */
	public function add_rmp_menu_frontend_scripts() {

		$option_manager = Option_Manager::get_instance();
		$menu_ids       = get_all_rmp_menu_ids();

		if ( empty( $menu_ids ) ) {
			return;
		}
		$menu_exit = 0;
		foreach ( $menu_ids as $menu_id ) {

			$menu_show_on       = $option_manager->get_option( $menu_id, 'menu_display_on' );
			$page_ids           = $option_manager->get_option( $menu_id, 'menu_show_on_pages' );
			$menu_show_by_user  = $option_manager->get_option( $menu_id, 'menu_display_by_users' );
			$responsive_menu_id = '';
			if ( isset( $_REQUEST['rmp_preview_mode'] ) ) {
				$responsive_menu_id = $_REQUEST['menu_id'];
			}
			if ( ! empty( $menu_show_on ) &&
				( 'shortcode' === $menu_show_on ||
				( 'exclude-pages' === $menu_show_on && in_array( get_queried_object_id(), $page_ids ) ) ||
				( 'include-pages' === $menu_show_on && ! in_array( get_queried_object_id(), $page_ids ) ) ||
				( ( 'logged-in-users' === $menu_show_by_user && ! is_user_logged_in() ) || ( $responsive_menu_id != '' && $responsive_menu_id != $menu_id ) ) ||
				( 'guest-users' === $menu_show_by_user && is_user_logged_in() && $responsive_menu_id == '' )
				) ) {
				continue;
			}
			$menu_exit = 1;

		}

		if ( ! $menu_exit ) {
			return;
		}

		$this->add_rmp_menu_frontend_scripts_files();

	}

	/**
	 * Add menu scripts files for frontend.
	 */
	public function add_rmp_menu_frontend_scripts_files() {

		$in_footer = false;
		if ( 'on' === $this->option_manager->get_global_option( 'rmp_scripts_in_footer' ) ) {
			$in_footer = true;
		}

		$font_display = 'auto';
		if ( ! empty( $this->option_manager->get_global_option( 'font_display_property' ) ) ) {
			$font_display = $this->option_manager->get_global_option( 'font_display_property' );
		}

		wp_enqueue_script( 'jquery' );

		wp_enqueue_script(
			'rmp_menu_touchSwipe',
			RMP_PLUGIN_URL_V4 . '/assets/js/jquery.touchSwipe.min.js',
			array( 'jquery' ),
			RMP_PLUGIN_VERSION,
			true
		);

		$rmp_front_js_path = '/assets/js/rmp-menu.js';
		if ( 'on' === $this->option_manager->get_global_option( 'rmp_minify_scripts' ) ) {
			$rmp_front_js_path = '/assets/js/rmp-menu.min.js';
		}
		wp_register_script(
			'rmp_menu_scripts',
			RMP_PLUGIN_URL_V4 . $rmp_front_js_path,
			array( 'jquery' ),
			RMP_PLUGIN_VERSION,
			$in_footer
		);

		$menu_options = $this->get_all_menu_options();
		wp_localize_script(
			'rmp_menu_scripts',
			'rmp_menu',
			array(
				'ajaxURL'  => admin_url( 'admin-ajax.php' ),
				'wp_nonce' => wp_create_nonce( 'ajax-nonce' ),
				'menu'     => $menu_options,
			)
		);

		wp_enqueue_script( 'rmp_menu_scripts' );

		if ( 'on' != $this->option_manager->get_global_option( 'rmp_remove_material_icons' ) ) {

			wp_enqueue_style(
				'rmp-material-icons',
				'https://fonts.googleapis.com/icon?family=Material+Icons&display=' . $font_display,
				null,
				RMP_PLUGIN_VERSION
			);
		}

		if ( 'on' != $this->option_manager->get_global_option( 'rmp_remove_fontawesome' ) ) {
			wp_enqueue_style(
				'rmp-font-awesome',
				'https://use.fontawesome.com/releases/v5.13.0/css/all.css',
				null,
				RMP_PLUGIN_VERSION
			);
		}

		if ( 'on' != $this->option_manager->get_global_option( 'rmp_remove_glyphicon' ) ) {
			wp_enqueue_style(
				'rmp-glyph-icons',
				RMP_PLUGIN_URL_V4 . '/assets/admin/scss/glyphicons.css',
				null,
				RMP_PLUGIN_VERSION
			);
		}

		if ( 'on' != $this->option_manager->get_global_option( 'rmp_remove_dashicons' ) ) {
			wp_enqueue_style( 'dashicons' );
		}

		// Enqueue menu dynamic styles as file.
		if ( 'on' == $this->option_manager->get_global_option( 'rmp_external_files' ) ) {
			$upload_dir = wp_upload_dir();
			$file_path  = trailingslashit( $upload_dir['basedir'] ) . 'rmp-menu/css/rmp-menu.css';

			// If file is not exist then create it.
			if ( ! file_exists( $file_path ) ) {
				$this->save_style_css_on_file();
			}

			// File exist then load the stylesheet.
			if ( file_exists( $file_path ) ) {

				wp_enqueue_style(
					'rmp-menu-dynamic-styles',
					set_url_scheme( untrailingslashit( $upload_dir['baseurl'] . '/rmp-menu/css/rmp-menu.css' ) ),
					null,
					get_option('rmp_dynamic_file_version', wp_rand( 100, 1000 ) )
				);
			}
		}

		/**
		 * Fires after frontend scripts are enqueued.
		 *
		 * @since 4.0.4
		 */
		do_action( 'after_rmp_enqueue_frontend_scripts' );

	}

	/**
	 * This function load the menu stylesheet as inline.
	 *
	 * @since 4.0.4
	 *
	 * @return void
	 */
	public function enqueue_styles_as_inline() {

		// External file setting is enabled.
		if ( 'on' == $this->option_manager->get_global_option( 'rmp_external_files' ) ) {
			return;
		}

		$option_manager = Option_Manager::get_instance();
		$menu_ids       = get_all_rmp_menu_ids();

		if ( empty( $menu_ids ) ) {
			return;
		}
		$menu_exit = 0;
		foreach ( $menu_ids as $menu_id ) {

			$menu_show_on = $option_manager->get_option( $menu_id, 'menu_display_on' );
			$page_ids     = $option_manager->get_option( $menu_id, 'menu_show_on_pages' );

			if ( ! empty( $menu_show_on ) &&
				( 'shortcode' === $menu_show_on ||
				( 'exclude-pages' === $menu_show_on && in_array( get_queried_object_id(), $page_ids ) ) ||
				( 'include-pages' === $menu_show_on && ! in_array( get_queried_object_id(), $page_ids ) ) ) ) {
				continue;
			}
			$menu_exit = 1;

		}

		if ( ! $menu_exit ) {
			return;
		}

		$css = $this->get_menus_scss_to_css();
		printf(
			'<style id="rmp-inline-menu-styles">%s</style>',
			$css
		);

	}

	/**
	 * This function convert the scss to css for menu.
	 *
	 * @param int $menu_id  This is menu id for which converting the scss to css.
	 *
	 * @return string|WP_Error
	 */
	public function get_css_for_menu( $menu_id ) {

		try {
			$options = $this->option_manager->get_options( $menu_id );

			$menu_items = '';
			if ( ! empty( $options['mega_menu'] ) ) {
				$menu_items = $options['mega_menu'];
			}

			$mega_menu_options = $this->mega_menu_options_to_scss( $menu_id, $menu_items );

			$mobile_breakpoint = '';
			if ( ! empty( $options['mobile_breakpoint'] ) ) {
				$mobile_breakpoint = $options['mobile_breakpoint'] . 'px';
			}

			$tablet_breakpoint = '';
			if ( ! empty( $options['tablet_breakpoint'] ) ) {
				$tablet_breakpoint = $options['tablet_breakpoint'] . 'px';
			}

			$menu_trigger_id = '';
			if ( ! empty( $options['menu_id'] ) ) {
				$menu_trigger_id = '#rmp_menu_trigger-' . $options['menu_id'];
			}

			$button_position_type = '';
			if ( ! empty( $options['button_position_type'] ) ) {
				$button_position_type = $options['button_position_type'];
			}

			$menu_trigger_side = '';
			if ( ! empty( $options['button_left_or_right'] ) ) {
				$menu_trigger_side = $options['button_left_or_right'];
			}

			$button_distance_from_side = '';
			if ( ! empty( $options['button_distance_from_side'] ) ) {
				$button_distance_from_side = $options['button_distance_from_side'] . $options['button_distance_from_side_unit'];
			}

			$menu_trigger_distance_from_top = '';
			if ( ! empty( $options['button_top'] ) ) {
				$menu_trigger_distance_from_top = $options['button_top'] . $options['button_top_unit'];
			}

			$menu_trigger_width = '';
			if ( ! empty( $options['button_width'] ) ) {
				$menu_trigger_width = $options['button_width'] . $options['button_width_unit'];
			}

			$menu_trigger_height = '';
			if ( ! empty( $options['button_height'] ) ) {
				$menu_trigger_height = $options['button_height'] . $options['button_height_unit'];
			}

			$menu_trigger_background_color = 'inherit';
			if ( ! empty( $options['button_background_colour'] ) ) {
				$menu_trigger_background_color = $options['button_background_colour'];
			}

			$menu_trigger_background_color_hover = 'inherit';
			if ( ! empty( $options['button_background_colour_hover'] ) ) {
				$menu_trigger_background_color_hover = $options['button_background_colour_hover'];
			}

			$menu_trigger_active_color = 'inherit';
			if ( ! empty( $options['button_background_colour_active'] ) ) {
				$menu_trigger_active_color = $options['button_background_colour_active'];
			}

			$toggle_button_border_radius = '0';
			if ( ! empty( $options['toggle_button_border_radius'] ) ) {
				$toggle_button_border_radius = $options['toggle_button_border_radius'];
			}

			$menu_trigger_transparent_background = '';
			if ( ! empty( $options['button_transparent_background'] ) ) {
				$menu_trigger_transparent_background = $options['button_transparent_background'];
			}

			$menu_trigger_line_color = 'inherit';
			if ( ! empty( $options['button_line_colour'] ) ) {
				$menu_trigger_line_color = $options['button_line_colour'];
			}

			$menu_trigger_line_color_hover = 'inherit';
			if ( ! empty( $options['button_line_colour_hover'] ) ) {
				$menu_trigger_line_color_hover = $options['button_line_colour_hover'];
			}

			$menu_trigger_line_active_color = 'inherit';
			if ( ! empty( $options['button_line_colour_active'] ) ) {
				$menu_trigger_line_active_color = $options['button_line_colour_active'];
			}

			$menu_trigger_title_color = 'inherit';
			if ( ! empty( $options['button_text_colour'] ) ) {
				$menu_trigger_title_color = $options['button_text_colour'];
			}

			$menu_trigger_title_line_height = '';
			if ( ! empty( $options['button_title_line_height'] ) ) {
				$menu_trigger_title_line_height = $options['button_title_line_height'] . $options['button_title_line_height_unit'];
			}

			$menu_trigger_title_font_size = '';
			if ( ! empty( $options['button_font_size'] ) ) {
				$menu_trigger_title_font_size = $options['button_font_size'] . $options['button_font_size_unit'];
			}

			$menu_trigger_title_font = '';
			if ( ! empty( $options['button_font'] ) ) {
				$menu_trigger_title_font = $options['button_font'];
			}

			$menu_trigger_line_height = '';
			if ( ! empty( $options['button_line_height'] ) ) {
				$menu_trigger_line_height = $options['button_line_height'];
			}

			$menu_trigger_line_height_unit = '';
			if ( ! empty( $options['button_line_height_unit'] ) ) {
				$menu_trigger_line_height_unit = $options['button_line_height_unit'];
			}

			$menu_trigger_line_width = '';
			if ( ! empty( $options['button_line_width'] ) ) {
				$menu_trigger_line_width = $options['button_line_width'];
			}

			$menu_trigger_line_width_unit = '';
			if ( ! empty( $options['button_line_width_unit'] ) ) {
				$menu_trigger_line_width_unit = $options['button_line_width_unit'];
			}

			$menu_trigger_line_margin = '';
			if ( ! empty( $options['button_line_margin'] ) ) {
				$menu_trigger_line_margin = get_post_meta( $menu_id, 'rmp_lagecy_menu_line_space', true ) ? $options['button_line_margin'] : 10;
			}

			$menu_trigger_line_margin_unit = '';
			if ( ! empty( $options['button_line_margin_unit'] ) ) {
				$menu_trigger_line_margin_unit = $options['button_line_margin_unit'];
			}

			$menu_search_box_wrap = '';
			if ( ! empty( $options['menu_id'] ) ) {
				$menu_search_box_wrap = '#rmp-search-box-' . $options['menu_id'];
			}

			$menu_search_section_padding_left = '';
			if ( ! empty( $options['menu_search_section_padding']['left'] ) ) {
				$menu_search_section_padding_left = $options['menu_search_section_padding']['left'];
			}

			$menu_search_section_padding_top = '';
			if ( ! empty( $options['menu_search_section_padding']['top'] ) ) {
				$menu_search_section_padding_top = $options['menu_search_section_padding']['top'];
			}

			$menu_search_section_padding_right = '';
			if ( ! empty( $options['menu_search_section_padding']['right'] ) ) {
				$menu_search_section_padding_right = $options['menu_search_section_padding']['right'];
			}

			$menu_search_section_padding_bottom = '';
			if ( ! empty( $options['menu_search_section_padding']['bottom'] ) ) {
				$menu_search_section_padding_bottom = $options['menu_search_section_padding']['bottom'];
			}

			$menu_search_box_text_color = 'inherit';
			if ( ! empty( $options['menu_search_box_text_colour'] ) ) {
				$menu_search_box_text_color = $options['menu_search_box_text_colour'];
			}

			$menu_search_box_background_color = 'inherit';
			if ( ! empty( $options['menu_search_box_background_colour'] ) ) {
				$menu_search_box_background_color = $options['menu_search_box_background_colour'];
			}

			$menu_search_box_border_color = 'currentColor';
			if ( ! empty( $options['menu_search_box_border_colour'] ) ) {
				$menu_search_box_border_color = $options['menu_search_box_border_colour'];
			}

			$menu_search_box_placeholder_color = 'inherit';
			if ( ! empty( $options['menu_search_box_placeholder_colour'] ) ) {
				$menu_search_box_placeholder_color = $options['menu_search_box_placeholder_colour'];
			}

			$menu_search_box_border_radius = '0';
			if ( ! empty( $options['menu_search_box_border_radius'] ) ) {
				$menu_search_box_border_radius = $options['menu_search_box_border_radius'] . 'px';
			}

			$menu_search_box_height = '';
			if ( ! empty( $options['menu_search_box_height'] ) ) {
				$menu_search_box_height = $options['menu_search_box_height'];
			}

			$menu_search_box_height_unit = '';
			if ( ! empty( $options['menu_search_box_height_unit'] ) ) {
				$menu_search_box_height_unit = $options['menu_search_box_height_unit'];
			}

			$menu_additional_content_wrap = '';
			if ( ! empty( $options['menu_id'] ) ) {
				$menu_additional_content_wrap = '#rmp-menu-additional-content-' . $options['menu_id'];
			}

			$menu_additional_section_padding_left = '';
			if ( ! empty( $options['menu_additional_section_padding']['left'] ) ) {
				$menu_additional_section_padding_left = $options['menu_additional_section_padding']['left'];
			}

			$menu_additional_section_padding_top = '';
			if ( ! empty( $options['menu_additional_section_padding']['top'] ) ) {
				$menu_additional_section_padding_top = $options['menu_additional_section_padding']['top'];
			}

			$menu_additional_section_padding_right = '';
			if ( ! empty( $options['menu_additional_section_padding']['right'] ) ) {
				$menu_additional_section_padding_right = $options['menu_additional_section_padding']['right'];
			}

			$menu_additional_section_padding_bottom = '';
			if ( ! empty( $options['menu_additional_section_padding']['bottom'] ) ) {
				$menu_additional_section_padding_bottom = $options['menu_additional_section_padding']['bottom'];
			}

			$menu_additional_content_color = 'inherit';
			if ( ! empty( $options['menu_additional_content_colour'] ) ) {
				$menu_additional_content_color = $options['menu_additional_content_colour'];
			}

			$menu_additional_content_alignment = '';
			if ( ! empty( $options['menu_additional_content_alignment'] ) ) {
				$menu_additional_content_alignment = $options['menu_additional_content_alignment'];
			}

			$menu_additional_content_font_size = '';
			if ( ! empty( $options['menu_additional_content_font_size'] ) ) {
				$menu_additional_content_font_size = $options['menu_additional_content_font_size'];
			}

			$menu_additional_content_font_size_unit = '';
			if ( ! empty( $options['menu_additional_content_font_size_unit'] ) ) {
				$menu_additional_content_font_size_unit = $options['menu_additional_content_font_size_unit'];
			}

			$menu_social_icons_wrap = '';
			if ( ! empty( $options['menu_id'] ) ) {
				$menu_social_icons_wrap = '#rmp-menu-social-icons-' . $options['menu_id'];
			}

			$menu_social_icons_size = '';
			if ( ! empty( $options['menu_social_icons_size'] ) ) {
				$menu_social_icons_size = $options['menu_social_icons_size'];
			}

			$menu_social_icons_size_unit = 'px';
			if ( ! empty( $options['menu_social_icons_size_unit'] ) ) {
				$menu_social_icons_size_unit = $options['menu_social_icons_size_unit'];
			}

			$menu_social_icons_gap = '';
			if ( ! empty( $options['menu_social_icons_gap'] ) ) {
				$menu_social_icons_gap = $options['menu_social_icons_gap'];
			}

			$menu_social_icons_gap_unit = 'px';
			if ( ! empty( $options['menu_social_icons_gap_unit'] ) ) {
				$menu_social_icons_gap_unit = $options['menu_social_icons_gap_unit'];
			}

			$menu_social_icons_section_padding_left = '';
			if ( ! empty( $options['menu_social_icons_section_padding']['left'] ) ) {
				$menu_social_icons_section_padding_left = $options['menu_social_icons_section_padding']['left'];
			}

			$menu_social_icons_section_padding_top = '';
			if ( ! empty( $options['menu_social_icons_section_padding']['top'] ) ) {
				$menu_social_icons_section_padding_top = $options['menu_social_icons_section_padding']['top'];
			}

			$menu_social_icons_section_padding_right = '';
			if ( ! empty( $options['menu_social_icons_section_padding']['right'] ) ) {
				$menu_social_icons_section_padding_right = $options['menu_social_icons_section_padding']['right'];
			}

			$menu_social_icons_section_padding_bottom = '';
			if ( ! empty( $options['menu_social_icons_section_padding']['bottom'] ) ) {
				$menu_social_icons_section_padding_bottom = $options['menu_social_icons_section_padding']['bottom'];
			}

			$menu_title_wrap = '';
			if ( ! empty( $options['menu_id'] ) ) {
				$menu_title_wrap = '#rmp-menu-title-' . $options['menu_id'];
			}

			$menu_title_section_padding_left = '';
			if ( ! empty( $options['menu_title_section_padding']['left'] ) ) {
				$menu_title_section_padding_left = $options['menu_title_section_padding']['left'];
			}

			$menu_title_section_padding_top = '';
			if ( ! empty( $options['menu_title_section_padding']['top'] ) ) {
				$menu_title_section_padding_top = $options['menu_title_section_padding']['top'];
			}

			$menu_title_section_padding_right = '';
			if ( ! empty( $options['menu_title_section_padding']['right'] ) ) {
				$menu_title_section_padding_right = $options['menu_title_section_padding']['right'];
			}

			$menu_title_section_padding_bottom = '';
			if ( ! empty( $options['menu_title_section_padding']['bottom'] ) ) {
				$menu_title_section_padding_bottom = $options['menu_title_section_padding']['bottom'];
			}

			$menu_title_background = 'inherit';
			if ( ! empty( $options['menu_title_background_colour'] ) ) {
				$menu_title_background = $options['menu_title_background_colour'];
			}

			$menu_title_background_hover = 'inherit';
			if ( ! empty( $options['menu_title_background_hover_colour'] ) ) {
				$menu_title_background_hover = $options['menu_title_background_hover_colour'];
			}

			$menu_title_font_color = 'inherit';
			if ( ! empty( $options['menu_title_colour'] ) ) {
				$menu_title_font_color = $options['menu_title_colour'];
			}

			$menu_title_font_family = '';
			if ( ! empty( $options['menu_title_font_family'] ) ) {
				$menu_title_font_family = $options['menu_title_font_family'];
			}

			$menu_title_font_weight = '';
			if ( ! empty( $options['menu_title_font_weight'] ) ) {
				$menu_title_font_weight = $options['menu_title_font_weight'];
			}

			$menu_title_font_color_hover = 'inherit';
			if ( ! empty( $options['menu_title_hover_colour'] ) ) {
				$menu_title_font_color_hover = $options['menu_title_hover_colour'];
			}

			$menu_title_text_alignment = '';
			if ( ! empty( $options['menu_title_alignment'] ) ) {
				$menu_title_text_alignment = $options['menu_title_alignment'];
			}

			$menu_title_font_size = '';
			if ( ! empty( $options['menu_title_font_size'] ) ) {
				$menu_title_font_size = $options['menu_title_font_size'] . 'px';
			}

			$menu_title_image_width = '';
			if ( ! empty( $options['menu_title_image_width'] ) ) {
				$menu_title_image_width = $options['menu_title_image_width'] . $options['menu_title_image_width_unit'];
			}

			$menu_title_image_height = '';
			if ( ! empty( $options['menu_title_image_height'] ) ) {
				$menu_title_image_height = $options['menu_title_image_height'] . $options['menu_title_image_height_unit'];
			}

			$menu_container = '';
			if ( ! empty( $options['menu_id'] ) ) {
				$menu_container = '#rmp-container-' . $options['menu_id'];
			}

			$menu_container_padding_left = '';
			if ( ! empty( $options['menu_container_padding']['left'] ) ) {
				$menu_container_padding_left = $options['menu_container_padding']['left'];
			}

			$menu_container_padding_top = '';
			if ( ! empty( $options['menu_container_padding']['top'] ) ) {
				$menu_container_padding_top = $options['menu_container_padding']['top'];
			}

			$menu_container_padding_right = '';
			if ( ! empty( $options['menu_container_padding']['right'] ) ) {
				$menu_container_padding_right = $options['menu_container_padding']['right'];
			}

			$menu_container_padding_bottom = '';
			if ( ! empty( $options['menu_container_padding']['bottom'] ) ) {
				$menu_container_padding_bottom = $options['menu_container_padding']['bottom'];
			}

			$menu_auto_height = '';
			if ( ! empty( $options['menu_auto_height'] ) ) {
				$menu_auto_height = $options['menu_auto_height'];
			}

			$menu_width = '';
			if ( ! empty( $options['menu_width'] ) ) {
				$menu_width = $options['menu_width'];
			}

			$menu_width_unit = '';
			if ( ! empty( $options['menu_width_unit'] ) ) {
				$menu_width_unit = $options['menu_width_unit'];
			}

			$menu_maximum_width = '';
			if ( ! empty( $options['menu_maximum_width'] ) ) {
				$menu_maximum_width = $options['menu_maximum_width'];
			}

			$menu_maximum_width_unit = '';
			if ( ! empty( $options['menu_maximum_width_unit'] ) ) {
				$menu_maximum_width_unit = $options['menu_maximum_width_unit'];
			}

			$menu_minimum_width = '';
			if ( ! empty( $options['menu_minimum_width'] ) ) {
				$menu_minimum_width = $options['menu_minimum_width'];
			}

			$menu_minimum_width_unit = '';
			if ( ! empty( $options['menu_minimum_width_unit'] ) ) {
				$menu_minimum_width_unit = $options['menu_minimum_width_unit'];
			}

			$menu_container_background_colour = '';
			if ( ! empty( $options['menu_container_background_colour'] ) ) {
				$menu_container_background_colour = $options['menu_container_background_colour'];
			}

			$menu_container_gradient_background_colour_on = '';
			if ( ! empty( $options['menu_container_gradient_background_colour_on'] ) ) {
				$menu_container_gradient_background_colour_on = $options['menu_container_gradient_background_colour_on'];
			}

			$menu_container_gradient_background_first_colour = '#dd3333';
			if ( ! empty( $options['menu_container_gradient_background_first_colour'] ) ) {
				$menu_container_gradient_background_first_colour = $options['menu_container_gradient_background_first_colour'];
			}

			$menu_container_gradient_background_second_colour = '#eeee22';
			if ( ! empty( $options['menu_container_gradient_background_second_colour'] ) ) {
				$menu_container_gradient_background_second_colour = $options['menu_container_gradient_background_second_colour'];
			}

			$menu_container_gradient_background_type = '';
			if ( ! empty( $options['menu_container_gradient_background_type'] ) ) {
				$menu_container_gradient_background_type = $options['menu_container_gradient_background_type'];
			}

			$menu_container_gradient_background_direction = '';
			if ( ! empty( $options['menu_container_gradient_background_direction'] ) ) {
				$menu_container_gradient_background_direction = $options['menu_container_gradient_background_direction'];
			}

			$menu_container_gradient_background_start_position = '';
			if ( ! empty( $options['menu_container_gradient_background_start_position'] ) ) {
				$menu_container_gradient_background_start_position = $options['menu_container_gradient_background_start_position'];
			}

			$menu_container_gradient_background_end_position = '';
			if ( ! empty( $options['menu_container_gradient_background_end_position'] ) ) {
				$menu_container_gradient_background_end_position = $options['menu_container_gradient_background_end_position'];
			}

			$desktop_menu_container_background_colour = '';
			if ( ! empty( $options['desktop_menu_container_background_colour'] ) ) {
				$desktop_menu_container_background_colour = $options['desktop_menu_container_background_colour'];
			}

			$desktop_menu_container_gradient_background_colour_on = '';
			if ( ! empty( $options['desktop_menu_container_gradient_background_colour_on'] ) ) {
				$desktop_menu_container_gradient_background_colour_on = $options['desktop_menu_container_gradient_background_colour_on'];
			}

			$desktop_menu_container_gradient_background_first_colour = '#dd3333';
			if ( ! empty( $options['desktop_menu_container_gradient_background_first_colour'] ) ) {
				$desktop_menu_container_gradient_background_first_colour = $options['desktop_menu_container_gradient_background_first_colour'];
			}

			$desktop_menu_container_gradient_background_second_colour = '#eeee22';
			if ( ! empty( $options['desktop_menu_container_gradient_background_second_colour'] ) ) {
				$desktop_menu_container_gradient_background_second_colour = $options['desktop_menu_container_gradient_background_second_colour'];
			}

			$desktop_menu_container_gradient_background_type = '';
			if ( ! empty( $options['desktop_menu_container_gradient_background_type'] ) ) {
				$desktop_menu_container_gradient_background_type = $options['desktop_menu_container_gradient_background_type'];
			}

			$desktop_menu_container_gradient_background_direction = '';
			if ( ! empty( $options['desktop_menu_container_gradient_background_direction'] ) ) {
				$desktop_menu_container_gradient_background_direction = $options['desktop_menu_container_gradient_background_direction'];
			}

			$desktop_menu_container_gradient_background_start_position = '';
			if ( ! empty( $options['desktop_menu_container_gradient_background_start_position'] ) ) {
				$desktop_menu_container_gradient_background_start_position = $options['desktop_menu_container_gradient_background_start_position'];
			}

			$desktop_menu_container_gradient_background_end_position = '';
			if ( ! empty( $options['desktop_menu_container_gradient_background_end_position'] ) ) {
				$desktop_menu_container_gradient_background_end_position = $options['desktop_menu_container_gradient_background_end_position'];
			}

			$use_current_theme_location = '';
			if ( ! empty( $options['use_current_theme_location'] ) ) {
				$use_current_theme_location = $options['use_current_theme_location'];
			}

			$menu_container_background_image = '';
			if ( ! empty( $options['menu_background_image'] ) ) {
				$menu_container_background_image = $options['menu_background_image'];
			}

			$menu_container_appear_from = '';
			if ( ! empty( $options['menu_appear_from'] ) ) {
				$menu_container_appear_from = $options['menu_appear_from'];
			}

			$page_wrapper = '';
			if ( ! empty( $options['page_wrapper'] ) ) {
				$page_wrapper = $options['page_wrapper'];
			}

			$menu_wrap = '';
			if ( ! empty( $options['menu_id'] ) ) {
				$menu_wrap = '#rmp-menu-wrap-' . $options['menu_id'];
			}

			$menu_section_padding_left = '';
			if ( ! empty( $options['menu_section_padding']['left'] ) ) {
				$menu_section_padding_left = $options['menu_section_padding']['left'];
			}

			$menu_section_padding_top = '';
			if ( ! empty( $options['menu_section_padding']['top'] ) ) {
				$menu_section_padding_top = $options['menu_section_padding']['top'];
			}

			$menu_section_padding_right = '';
			if ( ! empty( $options['menu_section_padding']['right'] ) ) {
				$menu_section_padding_right = $options['menu_section_padding']['right'];
			}

			$menu_section_padding_bottom = '';
			if ( ! empty( $options['menu_section_padding']['bottom'] ) ) {
				$menu_section_padding_bottom = $options['menu_section_padding']['bottom'];
			}

			$menu_background_color = '';
			if ( ! empty( $options['menu_background_colour'] ) ) {
				$menu_background_color = $options['menu_background_colour'];
			}

			$menu_item_height = '';
			if ( ! empty( $options['menu_links_height'] ) ) {
				$menu_item_height = $options['menu_links_height'];
			}

			$menu_container_columns = '';
			if ( ! empty( $options['menu_container_columns'] ) ) {
				$menu_container_columns = $options['menu_container_columns'];
			}

			$menu_item_height_unit = '';
			if ( ! empty( $options['menu_links_height_unit'] ) ) {
				$menu_item_height_unit = $options['menu_links_height_unit'];
			}

			$menu_item_line_height = '';
			if ( ! empty( $options['menu_links_line_height'] ) ) {
				$menu_item_line_height = $options['menu_links_line_height'];
			}

			$menu_item_line_height_unit = '';
			if ( ! empty( $options['menu_links_line_height_unit'] ) ) {
				$menu_item_line_height_unit = $options['menu_links_line_height_unit'];
			}

			$menu_item_font_weight = 'intial';
			if ( ! empty( $options['menu_font_weight'] ) ) {
				$menu_item_font_weight = $options['menu_font_weight'];
			}

			$menu_item_letter_spacing = '0';
			if ( ! empty( $options['menu_text_letter_spacing'] ) ) {
				$menu_item_letter_spacing = $options['menu_text_letter_spacing'];
			}

			$submenu_text_letter_spacing = '0';
			if ( ! empty( $options['submenu_text_letter_spacing'] ) ) {
				$submenu_text_letter_spacing = $options['submenu_text_letter_spacing'];
			}

			$menu_item_border_width = '';
			if ( ! empty( $options['menu_border_width'] ) ) {
				$menu_item_border_width = $options['menu_border_width'];
			}

			$menu_item_border_width_unit = '';
			if ( ! empty( $options['menu_border_width_unit'] ) ) {
				$menu_item_border_width_unit = $options['menu_border_width_unit'];
			}

			$menu_item_border_color = 'currentColor';
			if ( ! empty( $options['menu_item_border_colour'] ) ) {
				$menu_item_border_color = $options['menu_item_border_colour'];
			}

			$menu_item_border_color_hover = 'currentColor';
			if ( ! empty( $options['menu_item_border_colour_hover'] ) ) {
				$menu_item_border_color_hover = $options['menu_item_border_colour_hover'];
			}

			$menu_current_item_border_color = 'currentColor';
			if ( ! empty( $options['menu_current_item_border_colour'] ) ) {
				$menu_current_item_border_color = $options['menu_current_item_border_colour'];
			}

			$menu_current_item_border_color_hover = 'currentColor';
			if ( ! empty( $options['menu_current_item_border_hover_colour'] ) ) {
				$menu_current_item_border_color_hover = $options['menu_current_item_border_hover_colour'];
			}

			$menu_item_font_size = '';
			if ( ! empty( $options['menu_font_size'] ) ) {
				$menu_item_font_size = $options['menu_font_size'];
			}

			$menu_item_font_size_unit = '';
			if ( ! empty( $options['menu_font_size_unit'] ) ) {
				$menu_item_font_size_unit = $options['menu_font_size_unit'];
			}

			$menu_item_font_family = '';
			if ( ! empty( $options['menu_font'] ) ) {
				$menu_item_font_family = $options['menu_font'];
			}

			$menu_item_text_alignment = '';
			if ( ! empty( $options['menu_text_alignment'] ) ) {
				$menu_item_text_alignment = $options['menu_text_alignment'];
			}

			$menu_item_text_color = 'inherit';
			if ( ! empty( $options['menu_link_colour'] ) ) {
				$menu_item_text_color = $options['menu_link_colour'];
			}

			$menu_item_text_color_hover = 'inherit';
			if ( ! empty( $options['menu_link_hover_colour'] ) ) {
				$menu_item_text_color_hover = $options['menu_link_hover_colour'];
			}

			$menu_current_item_text_color = 'inherit';
			if ( ! empty( $options['menu_current_link_colour'] ) ) {
				$menu_current_item_text_color = $options['menu_current_link_colour'];
			}

			$menu_current_item_text_color_hover = 'inherit';
			if ( ! empty( $options['menu_current_link_hover_colour'] ) ) {
				$menu_current_item_text_color_hover = $options['menu_current_link_hover_colour'];
			}

			$menu_parent_item_text_color_active = 'inherit';
			if ( ! empty( $options['menu_parent_link_active_color'] ) ) {
				$menu_parent_item_text_color_active = $options['menu_parent_link_active_color'];
			}

			$menu_parent_item_background_color_active = 'inherit';
			if ( ! empty( $options['menu_parent_link_background_active_color'] ) ) {
				$menu_parent_item_background_color_active = $options['menu_parent_link_background_active_color'];
			}

			$menu_item_background_color = 'inherit';
			if ( ! empty( $options['menu_item_background_colour'] ) ) {
				$menu_item_background_color = $options['menu_item_background_colour'];
			}

			$menu_item_background_color_hover = 'inherit';
			if ( ! empty( $options['menu_item_background_hover_colour'] ) ) {
				$menu_item_background_color_hover = $options['menu_item_background_hover_colour'];
			}

			$menu_current_item_background_color = 'inherit';
			if ( ! empty( $options['menu_current_item_background_colour'] ) ) {
				$menu_current_item_background_color = $options['menu_current_item_background_colour'];
			}

			$menu_current_item_background_color_hover = 'inherit';
			if ( ! empty( $options['menu_current_item_background_hover_colour'] ) ) {
				$menu_current_item_background_color_hover = $options['menu_current_item_background_hover_colour'];
			}

			$menu_item_padding = '';
			if ( ! empty( $options['menu_depth_0'] ) ) {
				$menu_item_padding = $options['menu_depth_0'];
			}

			$menu_item_padding_unit = '';
			if ( ! empty( $options['menu_depth_0_unit'] ) ) {
				$menu_item_padding_unit = $options['menu_depth_0_unit'];
			}

			$use_slide_effect = 'off';
			if ( ! empty( $options['use_slide_effect'] ) ) {
				$use_slide_effect = $options['use_slide_effect'];
			}

			$menu_depth_side = '';
			if ( ! empty( $options['menu_depth_side'] ) ) {
				$menu_depth_side = $options['menu_depth_side'];
			}

			$menu_item_padding_depth_1 = '';
			if ( ! empty( $options['menu_depth_1'] ) ) {
				$menu_item_padding_depth_1 = $options['menu_depth_1'];
			}

			$menu_item_padding_depth_1_unit = '';
			if ( ! empty( $options['menu_depth_1_unit'] ) ) {
				$menu_item_padding_depth_1_unit = $options['menu_depth_1_unit'];
			}

			$menu_item_padding_depth_2 = '';
			if ( ! empty( $options['menu_depth_2'] ) ) {
				$menu_item_padding_depth_2 = $options['menu_depth_2'];
			}

			$menu_item_padding_depth_2_unit = '';
			if ( ! empty( $options['menu_depth_2_unit'] ) ) {
				$menu_item_padding_depth_2_unit = $options['menu_depth_2_unit'];
			}

			$menu_item_padding_depth_3 = '';
			if ( ! empty( $options['menu_depth_3'] ) ) {
				$menu_item_padding_depth_3 = $options['menu_depth_3'];
			}

			$menu_item_padding_depth_3_unit = '';
			if ( ! empty( $options['menu_depth_3_unit'] ) ) {
				$menu_item_padding_depth_3_unit = $options['menu_depth_3_unit'];
			}

			$menu_item_padding_depth_4 = '';
			if ( ! empty( $options['menu_depth_4'] ) ) {
				$menu_item_padding_depth_4 = $options['menu_depth_4'];
			}

			$menu_item_padding_depth_4_unit = '';
			if ( ! empty( $options['menu_depth_4_unit'] ) ) {
				$menu_item_padding_depth_4_unit = $options['menu_depth_4_unit'];
			}

			$menu_word_wrap = '';
			if ( ! empty( $options['menu_word_wrap'] ) ) {
				$menu_word_wrap = $options['menu_word_wrap'];
			}

			$menu_item_toggle_position = '';
			if ( ! empty( $options['arrow_position'] ) ) {
				$menu_item_toggle_position = $options['arrow_position'];
			}

			$menu_item_toggle_height = '';
			if ( ! empty( $options['submenu_arrow_height'] ) ) {
				$menu_item_toggle_height = $options['submenu_arrow_height'];
			}

			$menu_item_toggle_height_unit = '';
			if ( ! empty( $options['submenu_arrow_height_unit'] ) ) {
				$menu_item_toggle_height_unit = $options['submenu_arrow_height_unit'];
			}

			$menu_item_toggle_width = '';
			if ( ! empty( $options['submenu_arrow_width'] ) ) {
				$menu_item_toggle_width = $options['submenu_arrow_width'];
			}

			$menu_item_toggle_width_unit = '';
			if ( ! empty( $options['submenu_arrow_width_unit'] ) ) {
				$menu_item_toggle_width_unit = $options['submenu_arrow_width_unit'];
			}

			$menu_item_toggle_text_color = 'inherit';
			if ( ! empty( $options['menu_sub_arrow_shape_colour'] ) ) {
				$menu_item_toggle_text_color = $options['menu_sub_arrow_shape_colour'];
			}

			$menu_item_toggle_text_color_hover = 'inherit';
			if ( ! empty( $options['menu_sub_arrow_shape_hover_colour'] ) ) {
				$menu_item_toggle_text_color_hover = $options['menu_sub_arrow_shape_hover_colour'];
			}

			$menu_current_item_toggle_text_color = 'inherit';
			if ( ! empty( $options['menu_sub_arrow_shape_colour_active'] ) ) {
				$menu_current_item_toggle_text_color = $options['menu_sub_arrow_shape_colour_active'];
			}

			$menu_current_item_toggle_text_color_hover = 'inherit';
			if ( ! empty( $options['menu_sub_arrow_shape_hover_colour_active'] ) ) {
				$menu_current_item_toggle_text_color_hover = $options['menu_sub_arrow_shape_hover_colour_active'];
			}

			$menu_item_toggle_background_color = 'inherit';
			if ( ! empty( $options['menu_sub_arrow_background_colour'] ) ) {
				$menu_item_toggle_background_color = $options['menu_sub_arrow_background_colour'];
			}

			$menu_item_toggle_background_color_hover = 'inherit';
			if ( ! empty( $options['menu_sub_arrow_background_hover_colour'] ) ) {
				$menu_item_toggle_background_color_hover = $options['menu_sub_arrow_background_hover_colour'];
			}

			$menu_current_item_toggle_background_color = 'inherit';
			if ( ! empty( $options['menu_sub_arrow_background_colour_active'] ) ) {
				$menu_current_item_toggle_background_color = $options['menu_sub_arrow_background_colour_active'];
			}

			$menu_current_item_toggle_background_color_hover = 'inherit';
			if ( ! empty( $options['menu_sub_arrow_background_hover_colour_active'] ) ) {
				$menu_current_item_toggle_background_color_hover = $options['menu_sub_arrow_background_hover_colour_active'];
			}

			$menu_item_toggle_border_color = 'currentColor';
			if ( ! empty( $options['menu_sub_arrow_border_colour'] ) ) {
				$menu_item_toggle_border_color = $options['menu_sub_arrow_border_colour'];
			}

			$menu_item_toggle_border_color_hover = 'currentColor';
			if ( ! empty( $options['menu_sub_arrow_border_hover_colour'] ) ) {
				$menu_item_toggle_border_color_hover = $options['menu_sub_arrow_border_hover_colour'];
			}

			$menu_current_item_toggle_border_color = 'currentColor';
			if ( ! empty( $options['menu_sub_arrow_border_colour_active'] ) ) {
				$menu_current_item_toggle_border_color = $options['menu_sub_arrow_border_colour_active'];
			}

			$menu_current_item_toggle_border_color_hover = 'currentColor';
			if ( ! empty( $options['menu_sub_arrow_border_hover_colour_active'] ) ) {
				$menu_current_item_toggle_border_color_hover = $options['menu_sub_arrow_border_hover_colour_active'];
			}

			$submenu_item_height = '';
			if ( ! empty( $options['submenu_links_height'] ) ) {
				$submenu_item_height = $options['submenu_links_height'];
			}

			$submenu_item_height_unit = '';
			if ( ! empty( $options['submenu_links_height_unit'] ) ) {
				$submenu_item_height_unit = $options['submenu_links_height_unit'];
			}

			$submenu_item_line_height = '';
			if ( ! empty( $options['submenu_links_line_height'] ) ) {
				$submenu_item_line_height = $options['submenu_links_line_height'];
			}

			$submenu_item_line_height_unit = '';
			if ( ! empty( $options['submenu_links_line_height_unit'] ) ) {
				$submenu_item_line_height_unit = $options['submenu_links_line_height_unit'];
			}

			$submenu_item_border_width = '';
			if ( ! empty( $options['submenu_border_width'] ) ) {
				$submenu_item_border_width = $options['submenu_border_width'];
			}

			$submenu_font_weight = '';
			if ( ! empty( $options['submenu_font_weight'] ) ) {
				$submenu_font_weight = $options['submenu_font_weight'];
			}

			$submenu_item_border_width_unit = '';
			if ( ! empty( $options['submenu_border_width_unit'] ) ) {
				$submenu_item_border_width_unit = $options['submenu_border_width_unit'];
			}

			$submenu_item_border_color = 'currentColor';
			if ( ! empty( $options['submenu_item_border_colour'] ) ) {
				$submenu_item_border_color = $options['submenu_item_border_colour'];
			}

			$submenu_item_border_color_hover = 'currentColor';
			if ( ! empty( $options['submenu_item_border_colour_hover'] ) ) {
				$submenu_item_border_color_hover = $options['submenu_item_border_colour_hover'];
			}

			$submenu_current_item_border_color = 'currentColor';
			if ( ! empty( $options['submenu_current_item_border_colour'] ) ) {
				$submenu_current_item_border_color = $options['submenu_current_item_border_colour'];
			}

			$submenu_current_item_border_color_hover = 'currentColor';
			if ( ! empty( $options['submenu_current_item_border_hover_colour'] ) ) {
				$submenu_current_item_border_color_hover = $options['submenu_current_item_border_hover_colour'];
			}

			$submenu_item_font_size = '';
			if ( ! empty( $options['submenu_font_size'] ) ) {
				$submenu_item_font_size = $options['submenu_font_size'];
			}

			$submenu_item_font_size_unit = '';
			if ( ! empty( $options['submenu_font_size_unit'] ) ) {
				$submenu_item_font_size_unit = $options['submenu_font_size_unit'];
			}

			$submenu_item_font_family = '';
			if ( ! empty( $options['submenu_font'] ) ) {
				$submenu_item_font_family = $options['submenu_font'];
			}

			$submenu_item_text_alignment = '';
			if ( ! empty( $options['submenu_text_alignment'] ) ) {
				$submenu_item_text_alignment = $options['submenu_text_alignment'];
			}

			$submenu_item_text_color = 'inherit';
			if ( ! empty( $options['submenu_link_colour'] ) ) {
				$submenu_item_text_color = $options['submenu_link_colour'];
			}

			$submenu_item_text_color_hover = 'inherit';
			if ( ! empty( $options['submenu_link_hover_colour'] ) ) {
				$submenu_item_text_color_hover = $options['submenu_link_hover_colour'];
			}

			$submenu_current_item_text_color = 'inherit';
			if ( ! empty( $options['submenu_current_link_colour'] ) ) {
				$submenu_current_item_text_color = $options['submenu_current_link_colour'];
			}

			$submenu_current_item_text_color_hover = 'inherit';
			if ( ! empty( $options['submenu_current_link_hover_colour'] ) ) {
				$submenu_current_item_text_color_hover = $options['submenu_current_link_hover_colour'];
			}

			$submenu_item_background_color = 'inherit';
			if ( ! empty( $options['submenu_item_background_colour'] ) ) {
				$submenu_item_background_color = $options['submenu_item_background_colour'];
			}

			$submenu_item_background_color_hover = 'inherit';
			if ( ! empty( $options['submenu_item_background_hover_colour'] ) ) {
				$submenu_item_background_color_hover = $options['submenu_item_background_hover_colour'];
			}

			$submenu_current_item_background_color = 'inherit';
			if ( ! empty( $options['submenu_current_item_background_colour'] ) ) {
				$submenu_current_item_background_color = $options['submenu_current_item_background_colour'];
			}

			$submenu_current_item_background_color_hover = 'inherit';
			if ( ! empty( $options['submenu_current_item_background_hover_colour'] ) ) {
				$submenu_current_item_background_color_hover = $options['submenu_current_item_background_hover_colour'];
			}

			$submenu_item_toggle_position = '';
			if ( ! empty( $options['submenu_arrow_position'] ) ) {
				$submenu_item_toggle_position = $options['submenu_arrow_position'];
			}

			$submenu_item_toggle_height = '';
			if ( ! empty( $options['submenu_submenu_arrow_height'] ) ) {
				$submenu_item_toggle_height = $options['submenu_submenu_arrow_height'];
			}

			$submenu_item_toggle_height_unit = '';
			if ( ! empty( $options['submenu_submenu_arrow_height_unit'] ) ) {
				$submenu_item_toggle_height_unit = $options['submenu_submenu_arrow_height_unit'];
			}

			$submenu_item_toggle_width = '';
			if ( ! empty( $options['submenu_submenu_arrow_width'] ) ) {
				$submenu_item_toggle_width = $options['submenu_submenu_arrow_width'];
			}

			$submenu_item_toggle_width_unit = '';
			if ( ! empty( $options['submenu_submenu_arrow_width_unit'] ) ) {
				$submenu_item_toggle_width_unit = $options['submenu_submenu_arrow_width_unit'];
			}

			$menu_sub_arrow_border_width = '0';
			if ( ! empty( $options['menu_sub_arrow_border_width'] ) ) {
				$menu_sub_arrow_border_width = $options['menu_sub_arrow_border_width'];
			}

			$menu_sub_arrow_border_width_unit = 'px';
			if ( ! empty( $options['menu_sub_arrow_border_width_unit'] ) ) {
				$menu_sub_arrow_border_width_unit = $options['menu_sub_arrow_border_width_unit'];
			}

			$submenu_sub_arrow_border_width = '0';
			if ( ! empty( $options['submenu_sub_arrow_border_width'] ) ) {
				$submenu_sub_arrow_border_width = $options['submenu_sub_arrow_border_width'];
			}

			$submenu_sub_arrow_border_width_unit = 'px';
			if ( ! empty( $options['submenu_sub_arrow_border_width_unit'] ) ) {
				$submenu_sub_arrow_border_width_unit = $options['submenu_sub_arrow_border_width_unit'];
			}

			$submenu_item_toggle_text_color = 'inherit';
			if ( ! empty( $options['submenu_sub_arrow_shape_colour'] ) ) {
				$submenu_item_toggle_text_color = $options['submenu_sub_arrow_shape_colour'];
			}

			$submenu_item_toggle_text_color_hover = 'inherit';
			if ( ! empty( $options['submenu_sub_arrow_shape_hover_colour'] ) ) {
				$submenu_item_toggle_text_color_hover = $options['submenu_sub_arrow_shape_hover_colour'];
			}

			$submenu_current_item_toggle_text_color = 'inherit';
			if ( ! empty( $options['submenu_sub_arrow_shape_colour_active'] ) ) {
				$submenu_current_item_toggle_text_color = $options['submenu_sub_arrow_shape_colour_active'];
			}

			$submenu_current_item_toggle_text_color_hover = 'inherit';
			if ( ! empty( $options['submenu_sub_arrow_shape_hover_colour_active'] ) ) {
				$submenu_current_item_toggle_text_color_hover = $options['submenu_sub_arrow_shape_hover_colour_active'];
			}

			$submenu_item_toggle_background_color = 'inherit';
			if ( ! empty( $options['submenu_sub_arrow_background_colour'] ) ) {
				$submenu_item_toggle_background_color = $options['submenu_sub_arrow_background_colour'];
			}

			$submenu_item_toggle_background_color_hover = 'inherit';
			if ( ! empty( $options['submenu_sub_arrow_background_hover_colour'] ) ) {
				$submenu_item_toggle_background_color_hover = $options['submenu_sub_arrow_background_hover_colour'];
			}

			$submenu_current_item_toggle_background_color = 'inherit';
			if ( ! empty( $options['submenu_sub_arrow_background_colour_active'] ) ) {
				$submenu_current_item_toggle_background_color = $options['submenu_sub_arrow_background_colour_active'];
			}

			$submenu_current_item_toggle_background_color_hover = 'inherit';
			if ( ! empty( $options['submenu_sub_arrow_background_hover_colour_active'] ) ) {
				$submenu_current_item_toggle_background_color_hover = $options['submenu_sub_arrow_background_hover_colour_active'];
			}

			$submenu_item_toggle_border_color = 'currentColor';
			if ( ! empty( $options['submenu_sub_arrow_border_colour'] ) ) {
				$submenu_item_toggle_border_color = $options['submenu_sub_arrow_border_colour'];
			}

			$submenu_item_toggle_border_color_hover = 'currentColor';
			if ( ! empty( $options['submenu_sub_arrow_border_hover_colour'] ) ) {
				$submenu_item_toggle_border_color_hover = $options['submenu_sub_arrow_border_hover_colour'];
			}

			$submenu_current_item_toggle_border_color = 'currentColor';
			if ( ! empty( $options['submenu_sub_arrow_border_colour_active'] ) ) {
				$submenu_current_item_toggle_border_color = $options['submenu_sub_arrow_border_colour_active'];
			}

			$submenu_current_item_toggle_border_color_hover = 'currentColor';
			if ( ! empty( $options['submenu_sub_arrow_border_hover_colour_active'] ) ) {
				$submenu_current_item_toggle_border_color_hover = $options['submenu_sub_arrow_border_hover_colour_active'];
			}

			$desktop_menu_width = '';
			if ( ! empty( $options['desktop_menu_width'] ) ) {
				$desktop_menu_width = $options['desktop_menu_width'];
			}

			$desktop_menu_width_unit = '';
			if ( ! empty( $options['desktop_menu_width_unit'] ) ) {
				$desktop_menu_width_unit = $options['desktop_menu_width_unit'];
			}

			$desktop_menu_height = '';
			if ( ! empty( $options['desktop_menu_height'] ) ) {
				$desktop_menu_height = $options['desktop_menu_height'];
			}

			$desktop_menu_height_unit = '';
			if ( ! empty( $options['desktop_menu_height_unit'] ) ) {
				$desktop_menu_height_unit = $options['desktop_menu_height_unit'];
			}

			$desktop_menu_position = '';
			if ( ! empty( $options['desktop_menu_positioning'] ) ) {
				$desktop_menu_position = $options['desktop_menu_positioning'];
			}

			$desktop_menu_alignment = '';
			if ( ! empty( $options['desktop_menu_side'] ) ) {
				$desktop_menu_alignment = $options['desktop_menu_side'];
			}

			$desktop_menu_content_alignment = 'left';
			if ( ! empty( $options['desktop_menu_content_alignment'] ) ) {
				$desktop_menu_content_alignment = $options['desktop_menu_content_alignment'];
			}

			$use_desktop_menu = '';
			if ( ! empty( $options['use_desktop_menu'] ) ) {
				$use_desktop_menu = $options['use_desktop_menu'];
			}

			$desktop_submenu_open_on_click = 'off';
			if ( ! empty( $options['desktop_submenu_open_on_click'] ) ) {
				$desktop_submenu_open_on_click = $options['desktop_submenu_open_on_click'];
			}

			$desktop_submenu_open_animation = '';
			if ( ! empty( $options['desktop_submenu_open_animation'] ) ) {
				$desktop_submenu_open_animation = $options['desktop_submenu_open_animation'];
			}

			$desktop_submenu_open_animation_speed = '';
			if ( ! empty( $options['desktop_submenu_open_animation_speed'] ) ) {
				$desktop_submenu_open_animation_speed = $options['desktop_submenu_open_animation_speed'];
			}

			$use_tablet_menu = '';
			if ( ! empty( $options['use_tablet_menu'] ) ) {
				$use_tablet_menu = $options['use_tablet_menu'];
			}

			$use_mobile_menu = '';
			if ( ! empty( $options['use_mobile_menu'] ) ) {
				$use_mobile_menu = $options['use_mobile_menu'];
			}

			$header_bar_id = '';
			if ( ! empty( $options['menu_id'] ) ) {
				$header_bar_id = '#rmp-header-bar-' . $options['menu_id'];
			}

			$use_header_bar = 'off';
			if ( ! empty( $options['use_header_bar'] ) ) {
				$use_header_bar = $options['use_header_bar'];
			}

			$header_bar_breakpoint = '';
			if ( ! empty( $options['header_bar_breakpoint'] ) ) {
				$header_bar_breakpoint = $options['header_bar_breakpoint'] . 'px';
			}

			$header_bar_padding_left = '';
			if ( ! empty( $options['header_bar_padding']['left'] ) ) {
				$header_bar_padding_left = $options['header_bar_padding']['left'];
			}

			$header_bar_padding_top = '';
			if ( ! empty( $options['header_bar_padding']['top'] ) ) {
				$header_bar_padding_top = $options['header_bar_padding']['top'];
			}

			$header_bar_padding_right = '';
			if ( ! empty( $options['header_bar_padding']['right'] ) ) {
				$header_bar_padding_right = $options['header_bar_padding']['right'];
			}

			$header_bar_padding_bottom = '';
			if ( ! empty( $options['header_bar_padding']['bottom'] ) ) {
				$header_bar_padding_bottom = $options['header_bar_padding']['bottom'];
			}

			$header_bar_position_type = '';
			if ( ! empty( $options['header_bar_position_type'] ) ) {
				$header_bar_position_type = $options['header_bar_position_type'];
			}

			$header_bar_height = '';
			if ( ! empty( $options['header_bar_height'] ) ) {
				$header_bar_height = $options['header_bar_height'];
			}

			$header_bar_height_unit = '';
			if ( ! empty( $options['header_bar_height_unit'] ) ) {
				$header_bar_height_unit = $options['header_bar_height_unit'];
			}

			$header_bar_adjust_page = 'off';
			if ( ! empty( $options['header_bar_adjust_page'] ) ) {
				$header_bar_adjust_page = $options['header_bar_adjust_page'];
			}

			$header_bar_font = '';
			if ( ! empty( $options['header_bar_font'] ) ) {
				$header_bar_font = $options['header_bar_font'];
			}

			$header_bar_font_size = '';
			if ( ! empty( $options['header_bar_font_size'] ) ) {
				$header_bar_font_size = $options['header_bar_font_size'];
			}

			$header_bar_font_size_unit = '';
			if ( ! empty( $options['header_bar_font_size_unit'] ) ) {
				$header_bar_font_size_unit = $options['header_bar_font_size_unit'];
			}

			$header_bar_text_color = 'inherit';
			if ( ! empty( $options['header_bar_text_color'] ) ) {
				$header_bar_text_color = $options['header_bar_text_color'];
			}

			$header_bar_background_color = 'inherit';
			if ( ! empty( $options['header_bar_background_color'] ) ) {
				$header_bar_background_color = $options['header_bar_background_color'];
			}

			$header_bar_gradient_background_colour_on = '';
			if ( ! empty( $options['header_bar_gradient_background_colour_on'] ) ) {
				$header_bar_gradient_background_colour_on = $options['header_bar_gradient_background_colour_on'];
			}

			$header_bar_gradient_background_first_colour = '#dd3333';
			if ( ! empty( $options['header_bar_gradient_background_first_colour'] ) ) {
				$header_bar_gradient_background_first_colour = $options['header_bar_gradient_background_first_colour'];
			}

			$header_bar_gradient_background_second_colour = '#eeee22';
			if ( ! empty( $options['header_bar_gradient_background_second_colour'] ) ) {
				$header_bar_gradient_background_second_colour = $options['header_bar_gradient_background_second_colour'];
			}

			$header_bar_gradient_background_type = '';
			if ( ! empty( $options['header_bar_gradient_background_type'] ) ) {
				$header_bar_gradient_background_type = $options['header_bar_gradient_background_type'];
			}

			$header_bar_gradient_background_direction = '';
			if ( ! empty( $options['header_bar_gradient_background_direction'] ) ) {
				$header_bar_gradient_background_direction = $options['header_bar_gradient_background_direction'];
			}

			$header_bar_gradient_background_start_position = '';
			if ( ! empty( $options['header_bar_gradient_background_start_position'] ) ) {
				$header_bar_gradient_background_start_position = $options['header_bar_gradient_background_start_position'];
			}

			$header_bar_gradient_background_end_position = '';
			if ( ! empty( $options['header_bar_gradient_background_end_position'] ) ) {
				$header_bar_gradient_background_end_position = $options['header_bar_gradient_background_end_position'];
			}

			$header_bar_logo_width = '';
			if ( ! empty( $options['header_bar_logo_width'] ) ) {
				$header_bar_logo_width = $options['header_bar_logo_width'];
			}

			$header_bar_logo_width_unit = '';
			if ( ! empty( $options['header_bar_logo_width_unit'] ) ) {
				$header_bar_logo_width_unit = $options['header_bar_logo_width_unit'];
			}

			$header_bar_logo_height = '';
			if ( ! empty( $options['header_bar_logo_height'] ) ) {
				$header_bar_logo_height = $options['header_bar_logo_height'];
			}

			$header_bar_logo_height_unit = '';
			if ( ! empty( $options['header_bar_logo_height_unit'] ) ) {
				$header_bar_logo_height_unit = $options['header_bar_logo_height_unit'];
			}

			// Animation delay and times.

			$animation_speed = '';
			if ( ! empty( $options['animation_speed'] ) ) {
				$animation_speed = $options['animation_speed'] . 's';
			}

			$color_transition_speed = '';
			if ( ! empty( $options['transition_speed'] ) ) {
				$color_transition_speed = $options['transition_speed'] . 's';
			}

			$sub_menu_transition_speed = '';
			if ( ! empty( $options['sub_menu_speed'] ) ) {
				$sub_menu_transition_speed = $options['sub_menu_speed'] . 's';
			}

			$menu_to_hide = '';
			if ( ! empty( $options['menu_to_hide'] ) ) {
				$menu_to_hide = $options['menu_to_hide'];
			}

			$desktop_menu_to_hide = '';
			if ( ! empty( $options['desktop_menu_to_hide'] ) ) {
				$desktop_menu_to_hide = $options['desktop_menu_to_hide'];
			}

			$menu_page_overlay_id = 'off';
			if ( ! empty( $options['menu_id'] ) ) {
				$menu_page_overlay_id = '#rmp-page-overlay-' . $options['menu_id'];
			}

			$menu_page_overlay = 'off';
			if ( ! empty( $options['menu_overlay'] ) ) {
				$menu_page_overlay = $options['menu_overlay'];
			}

			$menu_overlay_background = '';
			if ( ! empty( $options['menu_overlay_colour'] ) ) {
				$menu_overlay_background = $options['menu_overlay_colour'];
			}

			$header_bar_scroll_background_color = 'inherit';
			if ( ! empty( $options['header_bar_scroll_background_color'] ) ) {
				$header_bar_scroll_background_color = $options['header_bar_scroll_background_color'];
			}

			$is_legacy = 'off';
			if ( ! empty( get_option( 'responsive_menu_pro_version' ) ) ) {
				$is_legacy = 'on';
			}

			$parse_options = array(
				// Menu breakpoints.
				'mobile_breakpoint'                        => $mobile_breakpoint,
				'tablet_breakpoint'                        => $tablet_breakpoint,
				'header_bar_breakpoint'                    => $header_bar_breakpoint,

				// Header bar options.
				'header_bar_id'                            => $header_bar_id,
				'header_bar_position_type'                 => $header_bar_position_type,
				'header_bar_height'                        => $header_bar_height,
				'header_bar_height_unit'                   => $header_bar_height_unit,
				'header_bar_adjust_page'                   => $header_bar_adjust_page,
				'header_bar_font'                          => $header_bar_font,
				'header_bar_font_size'                     => $header_bar_font_size,
				'header_bar_font_size_unit'                => $header_bar_font_size_unit,
				'header_bar_text_color'                    => $header_bar_text_color,
				'header_bar_background_color'              => $header_bar_background_color,
				'header_bar_gradient_background_colour_on' => $header_bar_gradient_background_colour_on,
				'header_bar_gradient_background_type'      => $header_bar_gradient_background_type,
				'header_bar_gradient_background_direction' => $header_bar_gradient_background_direction,
				'header_bar_gradient_background_first_colour' => $header_bar_gradient_background_first_colour,
				'header_bar_gradient_background_second_colour' => $header_bar_gradient_background_second_colour,
				'header_bar_gradient_background_start_position' => $header_bar_gradient_background_start_position,
				'header_bar_gradient_background_end_position' => $header_bar_gradient_background_end_position,
				'header_bar_logo_width_unit'               => $header_bar_logo_width_unit,
				'header_bar_logo_width'                    => $header_bar_logo_width,
				'header_bar_logo_height'                   => $header_bar_logo_height,
				'header_bar_logo_height_unit'              => $header_bar_logo_height_unit,
				'use_header_bar'                           => $use_header_bar,
				'header_bar_padding_bottom'                => $header_bar_padding_bottom,
				'header_bar_padding_top'                   => $header_bar_padding_top,
				'header_bar_padding_right'                 => $header_bar_padding_right,
				'header_bar_padding_left'                  => $header_bar_padding_left,

				// Menu hamburger toggle options.
				'menu_trigger_id'                          => $menu_trigger_id,
				'menu_trigger_position_type'               => $button_position_type,
				'menu_trigger_side'                        => $menu_trigger_side,
				'menu_trigger_distance_from_side'          => $button_distance_from_side,
				'menu_trigger_distance_from_top'           => $menu_trigger_distance_from_top,
				'menu_trigger_width'                       => $menu_trigger_width,
				'menu_trigger_height'                      => $menu_trigger_height,
				'menu_trigger_background_color'            => $menu_trigger_background_color,
				'menu_trigger_background_color_hover'      => $menu_trigger_background_color_hover,
				'menu_trigger_active_color'                => $menu_trigger_active_color,
				'toggle_button_border_radius'              => $toggle_button_border_radius,
				'menu_trigger_transparent_background'      => $menu_trigger_transparent_background,
				'menu_trigger_line_color'                  => $menu_trigger_line_color,
				'menu_trigger_line_color_hover'            => $menu_trigger_line_color_hover,
				'menu_trigger_line_active_color'           => $menu_trigger_line_active_color,
				'menu_trigger_title_color'                 => $menu_trigger_title_color,
				'menu_trigger_title_line_height'           => $menu_trigger_title_line_height,
				'menu_trigger_title_font_size'             => $menu_trigger_title_font_size,
				'menu_trigger_title_font'                  => $menu_trigger_title_font,
				'menu_trigger_line_height'                 => $menu_trigger_line_height,
				'menu_trigger_line_height_unit'            => $menu_trigger_line_height_unit,
				'menu_trigger_line_width'                  => $menu_trigger_line_width,
				'menu_trigger_line_width_unit'             => $menu_trigger_line_width_unit,
				'menu_trigger_line_margin'                 => $menu_trigger_line_margin,
				'menu_trigger_line_margin_unit'            => $menu_trigger_line_margin_unit,

				// Menu searchbox options.
				'menu_search_box_wrap'                     => $menu_search_box_wrap,
				'menu_search_box_text_color'               => $menu_search_box_text_color,
				'menu_search_box_background_color'         => $menu_search_box_background_color,
				'menu_search_box_border_color'             => $menu_search_box_border_color,
				'menu_search_box_placeholder_color'        => $menu_search_box_placeholder_color,
				'menu_search_box_border_radius'            => $menu_search_box_border_radius,
				'menu_search_box_height_unit'              => $menu_search_box_height_unit,
				'menu_search_box_height'                   => $menu_search_box_height,
				'menu_search_section_padding_bottom'       => $menu_search_section_padding_bottom,
				'menu_search_section_padding_right'        => $menu_search_section_padding_right,
				'menu_search_section_padding_top'          => $menu_search_section_padding_top,
				'menu_search_section_padding_left'         => $menu_search_section_padding_left,

				// Menu additional contents options.
				'menu_additional_content_wrap'             => $menu_additional_content_wrap,
				'menu_additional_content_color'            => $menu_additional_content_color,
				'menu_additional_content_alignment'        => $menu_additional_content_alignment,
				'menu_additional_content_font_size'        => $menu_additional_content_font_size,
				'menu_additional_content_font_size_unit'   => $menu_additional_content_font_size_unit,
				'menu_additional_section_padding_left'     => $menu_additional_section_padding_left,
				'menu_additional_section_padding_top'      => $menu_additional_section_padding_top,
				'menu_additional_section_padding_bottom'   => $menu_additional_section_padding_bottom,
				'menu_additional_section_padding_right'    => $menu_additional_section_padding_right,

				// Menu social icons options.
				'menu_social_icons_wrap'                   => $menu_social_icons_wrap,
				'menu_social_icons_size'                   => $menu_social_icons_size,
				'menu_social_icons_size_unit'              => $menu_social_icons_size_unit,
				'menu_social_icons_gap'                    => $menu_social_icons_gap,
				'menu_social_icons_gap_unit'               => $menu_social_icons_gap_unit,
				'menu_social_icons_section_padding_left'   => $menu_social_icons_section_padding_left,
				'menu_social_icons_section_padding_top'    => $menu_social_icons_section_padding_top,
				'menu_social_icons_section_padding_right'  => $menu_social_icons_section_padding_right,
				'menu_social_icons_section_padding_bottom' => $menu_social_icons_section_padding_bottom,

				// Menu title options.
				'menu_title_wrap'                          => $menu_title_wrap,
				'menu_title_font_weight'                   => $menu_title_font_weight,
				'menu_title_font_family'                   => $menu_title_font_family,
				'menu_title_background'                    => $menu_title_background,
				'menu_title_background_hover'              => $menu_title_background_hover,
				'menu_title_font_color'                    => $menu_title_font_color,
				'menu_title_font_color_hover'              => $menu_title_font_color_hover,
				'menu_title_font_size'                     => $menu_title_font_size,
				'menu_title_image_width'                   => $menu_title_image_width,
				'menu_title_image_height'                  => $menu_title_image_height,
				'menu_title_text_alignment'                => $menu_title_text_alignment,
				'menu_title_section_padding_left'          => $menu_title_section_padding_left,
				'menu_title_section_padding_top'           => $menu_title_section_padding_top,
				'menu_title_section_padding_right'         => $menu_title_section_padding_right,
				'menu_title_section_padding_bottom'        => $menu_title_section_padding_bottom,

				// Menu items and it's settings options.
				'menu_container'                           => $menu_container,
				'menu_auto_height'                         => $menu_auto_height,
				'menu_width'                               => $menu_width,
				'menu_width_unit'                          => $menu_width_unit,
				'menu_maximum_width'                       => $menu_maximum_width,
				'menu_maximum_width_unit'                  => $menu_maximum_width_unit,
				'menu_minimum_width'                       => $menu_minimum_width,
				'menu_minimum_width_unit'                  => $menu_minimum_width_unit,
				'menu_container_background_colour'         => $menu_container_background_colour,
				'menu_container_background_image'          => "'" . $menu_container_background_image . "'",
				'menu_container_gradient_background_colour_on' => $menu_container_gradient_background_colour_on,
				'menu_container_gradient_background_type'  => $menu_container_gradient_background_type,
				'menu_container_gradient_background_direction' => $menu_container_gradient_background_direction,
				'menu_container_gradient_background_first_colour' => $menu_container_gradient_background_first_colour,
				'menu_container_gradient_background_second_colour' => $menu_container_gradient_background_second_colour,
				'menu_container_gradient_background_start_position' => $menu_container_gradient_background_start_position,
				'menu_container_gradient_background_end_position' => $menu_container_gradient_background_end_position,
				'desktop_menu_container_background_colour' => $desktop_menu_container_background_colour,
				'desktop_menu_container_gradient_background_colour_on' => $desktop_menu_container_gradient_background_colour_on,
				'desktop_menu_container_gradient_background_type' => $desktop_menu_container_gradient_background_type,
				'desktop_menu_container_gradient_background_direction' => $desktop_menu_container_gradient_background_direction,
				'desktop_menu_container_gradient_background_first_colour' => $desktop_menu_container_gradient_background_first_colour,
				'desktop_menu_container_gradient_background_second_colour' => $desktop_menu_container_gradient_background_second_colour,
				'desktop_menu_container_gradient_background_start_position' => $desktop_menu_container_gradient_background_start_position,
				'desktop_menu_container_gradient_background_end_position' => $desktop_menu_container_gradient_background_end_position,
				'use_current_theme_location'               => $use_current_theme_location,
				'menu_container_appear_from'               => $menu_container_appear_from,
				'page_wrapper'                             => $page_wrapper,
				'menu_to_hide'                             => $menu_to_hide,
				'menu_container_padding_left'              => $menu_container_padding_left,
				'menu_container_padding_top'               => $menu_container_padding_top,
				'menu_container_padding_bottom'            => $menu_container_padding_bottom,
				'menu_container_padding_right'             => $menu_container_padding_right,
				'menu_wrap'                                => $menu_wrap,
				'menu_section_padding_bottom'              => $menu_section_padding_bottom,
				'menu_section_padding_right'               => $menu_section_padding_right,
				'menu_section_padding_top'                 => $menu_section_padding_top,
				'menu_section_padding_left'                => $menu_section_padding_left,

				'menu_background_color'                    => $menu_background_color,
				'menu_container_columns'                   => $menu_container_columns,
				'menu_item_height'                         => $menu_item_height,
				'menu_item_height_unit'                    => $menu_item_height_unit,
				'menu_item_line_height'                    => $menu_item_line_height,
				'menu_item_line_height_unit'               => $menu_item_line_height_unit,
				'menu_item_font_weight'                    => $menu_item_font_weight,
				'menu_item_letter_spacing'                 => $menu_item_letter_spacing,

				'menu_item_border_width'                   => $menu_item_border_width,
				'menu_item_border_width_unit'              => $menu_item_border_width_unit,
				'menu_item_border_color'                   => $menu_item_border_color,
				'menu_item_border_color_hover'             => $menu_item_border_color_hover,
				'menu_current_item_border_color'           => $menu_current_item_border_color,
				'menu_current_item_border_color_hover'     => $menu_current_item_border_color_hover,

				'menu_item_font_size'                      => $menu_item_font_size,
				'menu_item_font_size_unit'                 => $menu_item_font_size_unit,
				'menu_item_font_family'                    => $menu_item_font_family,
				'menu_item_text_alignment'                 => $menu_item_text_alignment,
				'menu_item_text_color'                     => $menu_item_text_color,
				'menu_item_text_color_hover'               => $menu_item_text_color_hover,
				'menu_current_item_text_color'             => $menu_current_item_text_color,
				'menu_current_item_text_color_hover'       => $menu_current_item_text_color_hover,
				'menu_parent_item_text_color_active'       => $menu_parent_item_text_color_active,
				'menu_parent_item_background_color_active' => $menu_parent_item_background_color_active,

				'menu_item_padding'                        => $menu_item_padding,
				'menu_item_padding_unit'                   => $menu_item_padding_unit,
				'menu_item_background_color'               => $menu_item_background_color,
				'menu_item_background_color_hover'         => $menu_item_background_color_hover,
				'menu_current_item_background_color'       => $menu_current_item_background_color,
				'menu_current_item_background_color_hover' => $menu_current_item_background_color_hover,

				'menu_item_toggle_position'                => $menu_item_toggle_position,
				'menu_item_toggle_height'                  => $menu_item_toggle_height,
				'menu_item_toggle_height_unit'             => $menu_item_toggle_height_unit,
				'menu_item_toggle_width'                   => $menu_item_toggle_width,
				'menu_item_toggle_width_unit'              => $menu_item_toggle_width_unit,
				'menu_item_toggle_text_color'              => $menu_item_toggle_text_color,
				'menu_item_toggle_text_color_hover'        => $menu_item_toggle_text_color_hover,
				'menu_current_item_toggle_text_color'      => $menu_current_item_toggle_text_color,
				'menu_current_item_toggle_text_color_hover' => $menu_current_item_toggle_text_color_hover,
				'menu_item_toggle_background_color'        => $menu_item_toggle_background_color,
				'menu_item_toggle_background_color_hover'  => $menu_item_toggle_background_color_hover,
				'menu_current_item_toggle_background_color' => $menu_current_item_toggle_background_color,
				'menu_current_item_toggle_background_color_hover' => $menu_current_item_toggle_background_color_hover,
				'menu_item_toggle_border_color'            => $menu_item_toggle_border_color,
				'menu_item_toggle_border_color_hover'      => $menu_item_toggle_border_color_hover,
				'menu_current_item_toggle_border_color'    => $menu_current_item_toggle_border_color,
				'menu_current_item_toggle_border_color_hover' => $menu_current_item_toggle_border_color_hover,
				'menu_item_toggle_border_width'            => $menu_sub_arrow_border_width,
				'menu_item_toggle_border_width_unit'       => $menu_sub_arrow_border_width_unit,

				// Sub-level menu items options.
				'submenu_item_height'                      => $submenu_item_height,
				'submenu_item_height_unit'                 => $submenu_item_height_unit,
				'submenu_item_line_height'                 => $submenu_item_line_height,
				'submenu_item_line_height_unit'            => $submenu_item_line_height_unit,

				'submenu_font_weight'                      => $submenu_font_weight,
				'submenu_item_border_width'                => $submenu_item_border_width,
				'submenu_item_border_width_unit'           => $submenu_item_border_width_unit,
				'submenu_item_border_color'                => $submenu_item_border_color,
				'submenu_item_border_color_hover'          => $submenu_item_border_color_hover,
				'submenu_current_item_border_color'        => $submenu_current_item_border_color,
				'submenu_current_item_border_color_hover'  => $submenu_current_item_border_color_hover,

				'submenu_item_font_size'                   => $submenu_item_font_size,
				'submenu_item_font_size_unit'              => $submenu_item_font_size_unit,
				'submenu_item_font_family'                 => $submenu_item_font_family,
				'submenu_item_text_alignment'              => $submenu_item_text_alignment,
				'submenu_text_letter_spacing'              => $submenu_text_letter_spacing,

				'submenu_item_text_color'                  => $submenu_item_text_color,
				'submenu_item_text_color_hover'            => $submenu_item_text_color_hover,
				'submenu_current_item_text_color'          => $submenu_current_item_text_color,
				'submenu_current_item_text_color_hover'    => $submenu_current_item_text_color_hover,
				'submenu_item_background_color'            => $submenu_item_background_color,
				'submenu_item_background_color_hover'      => $submenu_item_background_color_hover,
				'submenu_current_item_background_color'    => $submenu_current_item_background_color,
				'submenu_current_item_background_color_hover' => $submenu_current_item_background_color_hover,

				'submenu_item_toggle_border_width'         => $submenu_sub_arrow_border_width,
				'submenu_item_toggle_border_width_unit'    => $submenu_sub_arrow_border_width_unit,
				'submenu_item_toggle_position'             => $submenu_item_toggle_position,
				'submenu_item_toggle_height'               => $submenu_item_toggle_height,
				'submenu_item_toggle_height_unit'          => $submenu_item_toggle_height_unit,
				'submenu_item_toggle_width'                => $submenu_item_toggle_width,
				'submenu_item_toggle_width_unit'           => $submenu_item_toggle_width_unit,

				'submenu_item_toggle_text_color'           => $submenu_item_toggle_text_color,
				'submenu_item_toggle_text_color_hover'     => $submenu_item_toggle_text_color_hover,
				'submenu_current_item_toggle_text_color'   => $submenu_current_item_toggle_text_color,
				'submenu_current_item_toggle_text_color_hover' => $submenu_current_item_toggle_text_color_hover,
				'submenu_item_toggle_background_color'     => $submenu_item_toggle_background_color,
				'submenu_item_toggle_background_color_hover' => $submenu_item_toggle_background_color_hover,
				'submenu_current_item_toggle_background_color' => $submenu_current_item_toggle_background_color,
				'submenu_current_item_toggle_background_color_hover' => $submenu_current_item_toggle_background_color_hover,
				'submenu_item_toggle_border_color'         => $submenu_item_toggle_border_color,
				'submenu_item_toggle_border_color_hover'   => $submenu_item_toggle_border_color_hover,
				'submenu_current_item_toggle_border_color' => $submenu_current_item_toggle_border_color,
				'submenu_current_item_toggle_border_color_hover' => $submenu_current_item_toggle_border_color_hover,

				'use_slide_effect'                         => $use_slide_effect,
				'menu_depth_side'                          => $menu_depth_side,
				'menu_item_padding_depth_1'                => $menu_item_padding_depth_1,
				'menu_item_padding_depth_1_unit'           => $menu_item_padding_depth_1_unit,
				'menu_item_padding_depth_2'                => $menu_item_padding_depth_2,
				'menu_item_padding_depth_2_unit'           => $menu_item_padding_depth_2_unit,
				'menu_item_padding_depth_3'                => $menu_item_padding_depth_3,
				'menu_item_padding_depth_3_unit'           => $menu_item_padding_depth_3_unit,
				'menu_item_padding_depth_4'                => $menu_item_padding_depth_4,
				'menu_item_padding_depth_4_unit'           => $menu_item_padding_depth_4_unit,
				'menu_word_wrap'                           => $menu_word_wrap,
				// Desktop menu options.
				'desktop_menu_width'                       => $desktop_menu_width,
				'desktop_menu_width_unit'                  => $desktop_menu_width_unit,
				'desktop_menu_height'                      => $desktop_menu_height,
				'desktop_menu_height_unit'                 => $desktop_menu_height_unit,
				'desktop_menu_position'                    => $desktop_menu_position,
				'desktop_menu_side'                        => $desktop_menu_alignment,
				'desktop_menu_content_alignment'           => $desktop_menu_content_alignment,
				'menu_mega'                                => $mega_menu_options,
				'desktop_menu_to_hide'                     => $desktop_menu_to_hide,
				'desktop_submenu_open_on_click'            => $desktop_submenu_open_on_click,
				'desktop_submenu_open_animation'           => $desktop_submenu_open_animation,
				'desktop_submenu_open_animation_speed'     => $desktop_submenu_open_animation_speed,
				// Menu device responsive options.
				'use_desktop_menu'                         => $use_desktop_menu,
				'use_tablet_menu'                          => $use_tablet_menu,
				'use_mobile_menu'                          => $use_mobile_menu,

				// Animation and transition.
				'animation_speed'                          => $animation_speed,
				'menu_page_overlay_id'                     => $menu_page_overlay_id,
				'menu_overlay_background'                  => $menu_overlay_background,
				'menu_page_overlay'                        => $menu_page_overlay,
				'color_transition_speed'                   => $color_transition_speed,
				'sub_menu_transition_speed'                => $sub_menu_transition_speed,
				'header_bar_scroll_background_color'       => $header_bar_scroll_background_color,
				// Legacy
				'is_legacy'                                => $is_legacy,
			);

			// Multi device options.
			$option_manager  = Option_Manager::get_instance();
			$multi_inputs    = rmp_get_multi_device_options();
			$mobile_options  = $option_manager->get_mobile_options( $menu_id );
			$tablet_options  = $option_manager->get_tablet_options( $menu_id );
			$desktop_options = $option_manager->get_desktop_options( $menu_id );

			foreach ( $multi_inputs as $key => $value ) {
				$parse_options[ $key . '_mobile' ]  = ! empty( $mobile_options[ $key ] ) ? $mobile_options[ $key ] : '';
				$parse_options[ $key . '_tablet' ]  = ! empty( $tablet_options[ $key ] ) ? $tablet_options[ $key ] : '';
				$parse_options[ $key . '_desktop' ] = ! empty( $desktop_options[ $key ] ) ? $desktop_options[ $key ] : '';
			}

			/**
			 * Apply before parse the scss to css.
			 *
			 * @since 4.1.0
			 *
			 * @param array  $parse_options  Parsed menu settings.
			 * @param int    $menu_id        Menu Id.
			 * @param array  $options        Menu options array.
			 */
			$parse_options = apply_filters( 'rmp_before_parse_scss_to_css', $parse_options, $menu_id, $options );

			$scss = new Compiler();
			$scss->setImportPaths( RMP_PLUGIN_PATH_V4 . '/assets/scss/' );
			$scss->setVariables( $parse_options );
			$css  = $scss->compile( '@import "main.scss";' );
			$css .= $scss->compile( '@import "animation.scss";' );

			/**
			 * Apply after parsed the scss to css.
			 *
			 * @since 4.1.0
			 *
			 * @param string $css            Compiled CSS.
			 * @param int    $menu_id        Menu Id.
			 * @param array  $parse_options  Parsed menu settings.
			 * @param array  $options        Menu options array.
			 */
			$css = apply_filters( 'rmp_after_parse_scss_to_css', $css, $menu_id, $parse_options, $options );
			$css .= $this->get_social_icons_color_css( $options );

			return $css;
		} catch ( Exception $e ) {
			return new \WP_Error( 'Warning: Menu style scss compile failed <br/> <br />' . $e->getMessage() );
		}
	}

	/**
	 * Build dynamic social icon colors CSS.
	 *
	 * @param array $options Menu options array.
	 *
	 * @return string
	 */
	private function get_social_icons_color_css( $options ) {
		if ( empty( $options['menu_id'] ) ) {
			return '';
		}

		if ( empty( $options['menu_social_icons'] ) || ! is_array( $options['menu_social_icons'] ) ) {
			return '';
		}

		$menu_id = $options['menu_id'];
		$css     = '';
		$base    = '#rmp-container-' . $menu_id . ' #rmp-menu-social-icons-' . $menu_id;

		foreach ( $options['menu_social_icons'] as $index => $icon ) {
			$color = ! empty( $icon['color'] ) ? sanitize_text_field( $icon['color'] ) : '';
			$hover = ! empty( $icon['hover_color'] ) ? sanitize_text_field( $icon['hover_color'] ) : '';

			if ( '' === $color && '' === $hover ) {
				continue;
			}

			$item_selector  = $base . ' .rmp-social-icon-item--' . sanitize_html_class( (string) $index );
			$link_selector  = $item_selector . ' .rmp-social-icon-link, ' . $item_selector . ' .rmp-social-icon-link--no-link';
			$hover_selector = $item_selector . ' .rmp-social-icon-link:hover, ' . $item_selector . ' .rmp-social-icon-link--no-link:hover';

			if ( '' !== $color ) {
				$css .= $link_selector . '{ color:' . $color . '; }';
				$css .= $item_selector . ' .rmp-social-icon svg{ fill:' . $color . '; }';
			}

			if ( '' !== $hover ) {
				$css .= $hover_selector . '{ color:' . $hover . '; }';
				$css .= $hover_selector . ' svg{ fill:' . $hover . '; }';
			}
		}

		return $css;
	}

	/**
	 * This function convert the common scss style to css.
	 *
	 * @return string/WP_Error
	 */
	public function get_common_scss_to_css() {
		try {

			$menu_adjust_for_wp_admin_bar = $this->option_manager->get_global_option( 'menu_adjust_for_wp_admin_bar' );

			$options = array(
				'menu_adjust_for_wp_admin_bar' => $menu_adjust_for_wp_admin_bar,
			);

			$scss = new Compiler();
			$scss->setImportPaths( RMP_PLUGIN_PATH_V4 . '/assets/scss/' );
			$scss->setVariables( $options );
			$css = $scss->compile( '@import "common.scss";' );

			return $css;
		} catch ( Exception $e ) {
			return new \WP_Error( 'Warning: Common style scss compile failed <br/> <br />' . $e->getMessage() );
		}

	}

	/**
	 * This function convert the mega menu options to the scss variable and list.
	 *
	 * @param int   $menu_id     This is menu id.
	 * @param array $menu_items  List of mega menu items.
	 *
	 * @return string
	 */
	public function mega_menu_options_to_scss( $menu_id, $menu_items ) {

		if ( empty( $menu_items ) || empty( $menu_id ) ) {
			return;
		}

		$mega_menu_options = array();
		foreach ( $menu_items as $item_id => $status ) {

			$mega_menu = $this->option_manager->mega_menu_options( $menu_id, $item_id );

			if ( ! empty( $mega_menu ) ) {

				$row_count = 1;
				$rows      = array();
				if ( ! empty( $mega_menu['rows'] ) ) {
					foreach ( $mega_menu['rows'] as $row ) {

						$row_meta    = $row['meta'];
						$row_options = array(
							'margin-left'   => '0px',
							'margin-top'    => '0px',
							'margin-right'  => '0px',
							'margin-bottom' => '0px',
						);
						$col_count = 1;
						$cols      = array();
						if ( ! empty( $row['columns'] ) ) {
							foreach ( $row['columns'] as $column ) {
								$column_meta    = $column['meta'];
								$column_options = array(
									'padding-left'     => isset( $column_meta['column_padding']['left'] ) ? $column_meta['column_padding']['left'] : 0,
									'padding-top'      => isset( $column_meta['column_padding']['top'] ) ? $column_meta['column_padding']['top'] : 0,
									'padding-right'    => isset( $column_meta['column_padding']['right'] ) ? $column_meta['column_padding']['right'] : 0,
									'padding-bottom'   => isset( $column_meta['column_padding']['bottom'] ) ? $column_meta['column_padding']['bottom'] : 0,
									'background-color' => isset( $column_meta['column_background']['color'] ) ? $column_meta['column_background']['color'] : "transparent",
									'background-hover' => isset( $column_meta['column_background']['hover'] ) ? $column_meta['column_background']['hover'] : "transparent",
									'color'            => isset( $column_meta['text_color']['color'] ) ? $column_meta['text_color']['color'] : "transparent",
									'text-hover'       => isset( $column_meta['text_color']['hover'] ) ? $column_meta['text_color']['hover'] : "transparent",
								);

								$cols[ $col_count++ ] = $this->convert_php_array_to_scss_list( $column_options );
							}
						}

						$row_options['cols']  = $this->convert_php_array_to_scss_list( $cols );
						$rows[ $row_count++ ] = $this->convert_php_array_to_scss_list( $row_options );

					}
				}

				$panel_options = array();
				if ( ! empty( $mega_menu['meta'] ) ) {
					$panel_meta    = $mega_menu['meta'];
					$panel_options = array(
						'rows'             => $this->convert_php_array_to_scss_list( $rows ),
						'width'            => $panel_meta['panel_width'] . $panel_meta['panel_width_unit'],
						'padding-left'     => $panel_meta['panel_padding']['left'],
						'padding-top'      => $panel_meta['panel_padding']['top'],
						'padding-right'    => $panel_meta['panel_padding']['right'],
						'padding-bottom'   => $panel_meta['panel_padding']['bottom'],
						'background-color' => $panel_meta['panel_background']['color'],
						'background-image' => 'url(' . $panel_meta['panel_background']['image'] . ')',
					);
				}

				$mega_menu_options[ $item_id ] = $this->convert_php_array_to_scss_list( $panel_options );
			}
		}
		return $this->convert_php_array_to_scss_list( $mega_menu_options );
	}

	/**
	 * Convert the php array to scss list.
	 *
	 * @param array $options List of mega menu item option.
	 *
	 * @return string
	 */
	public function convert_php_array_to_scss_list( $options ) {

		if ( empty( $options ) ) {
			return;
		}

		array_walk(
			$options,
			function ( &$value, $key ) {
				$value = '"' . $key . '":' . $value;
			}
		);

		return '(' . implode( ',', $options ) . ')';
	}

	/**
	 * Adding theme support for menus
	 *
	 * @since 4.2.0
	 */
	public function rmp_add_classic_menu_support() {
		add_theme_support( 'menus' );
	}

}
