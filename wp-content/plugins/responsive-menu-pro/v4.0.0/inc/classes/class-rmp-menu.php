<?php

/**
 * This is core class file for responsive menu pro.
 *
 * @since      4.0.0
 *
 * @package    responsive_menu_pro
 */

namespace RMP\Features\Inc;

use RMP\Features\Inc\Option_Manager;
use RMP\Features\Inc\Walker;

/** Disbale the direct access to this class */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'RMP_Menu' ) ) :

	/**
	 * Class RMP_Menu prepare the menu as per loction and menu id.
	 *
	 * @package    responsive_menu_pro
	 *
	 * @author     Expresstech System
	 */
	class RMP_Menu {

		/**
		 * Hold the menu id.
		 *
		 * @since    4.0.0
		 * @access   protected
		 * @var      string $menu_id
		 */
		protected $menu_id;

		/**
		 * Hold the menu id.
		 *
		 * @since    4.0.0
		 * @access   protected
		 * @var      array $options
		 */
		public $options;

		/**
		 * This is menu class constructor function.
		 *
		 * @access public
		 */
		public function __construct( $menu_id ) {

			$option_manager = Option_Manager::get_instance();
			$this->options  = $option_manager->get_options( $menu_id );

			$this->menu_id = $menu_id;
			// add_filter( 'wp_nav_menu', array( $this, 'add_menu_content_on_theme_location' ), 999999, 2 );
			add_filter( 'wp_nav_menu_args', array( $this, 'rmp_location_wise_nav_menu_args' ), 999999, 1 );
		}

		/**
		 * Function to returns the contents of theme location menu.
		 *
		 * @since 4.0.2
		 */
		public function add_menu_content_on_theme_location( $nav_menu, $args ) {

			if ( 'off' == $this->options['use_current_theme_location'] ) {
				return $nav_menu;
			}

			$html = sprintf(
				'<div id="rmp-container-%s" class="rmp-container">%s</div>',
				$this->menu_id,
				$nav_menu
			);

			echo $html;
		}

		/**
		 * Function to prepare and return the mobile menu contents.
		 *
		 * @since 4.0.0
		 *
		 * @return HTML|string $html
		 */
		public function mobile_menu() {

			// Check if menu is theme location based the return empty.
			$menu_location = $this->get_wp_menu_location();
			if ( ! empty( $menu_location ) ) {
				return;
			}

			$menu_switcher = $this->menu_trigger();

			$menu_items = '';
			if ( ! empty( $this->options['items_order'] ) ) {
				$menu_items = $this->options['items_order'];
			}

			$html = '';

			if ( empty( $menu_items ) ) {
				return;
			}

			foreach ( $menu_items as $key => $value ) {
				if ( ! empty( $value ) && $value === 'on' ) {
					if ( 'menu' === $key ) {
						$html .= $this->menu();
					} elseif ( 'title' === $key ) {
						$html .= $this->menu_title();
					} elseif ( 'search' === $key ) {
						$html .= $this->menu_search_box();
					} elseif ( 'social-icons' === $key ) {
						$html .= $this->menu_social_icons();
					} else {
						$html .= $this->menu_additional_content();
					}
				}
			}

			$side_animation         = 'rmp-' . $this->options['animation_type'] . '-' . $this->options['menu_appear_from'];
			$menu_container_classes = apply_filters( 'rmp_menu_container_classes', array( 'rmp-container', $side_animation ), $this->menu_id );
			$menu_container_classes = implode( ' ', $menu_container_classes );

			$html = sprintf(
				'%s<div id="rmp-container-%s" class="%s">%s</div>',
				$menu_switcher,
				$this->menu_id,
				esc_attr( $menu_container_classes ),
				do_shortcode( $html )
			);

			// If page overlay is enable then show it.
			if ( 'on' == $this->options['menu_overlay'] ) {
				$html .= sprintf( '<div class="rmp-page-overlay" id="rmp-page-overlay-%s"></div>', esc_attr( $this->menu_id ) );
			}

			return $html;
		}

		/**
		 * Function to prints the menu contents in page.
		 *
		 * @since 4.0.0
		 *
		 * @return void
		 */
		public function build_new_menu() {

			$html = '';

			if ( 'on' == $this->options['use_header_bar'] ) {
				$html = $this->get_rmp_header();
			} else {
				$html = $this->mobile_menu();
			}

			/**
			 * Filters the menu markups.
			 *
			 * @since 4.0.4
			 *
			 * @param HTML|string $html
			 * @param int         menu_id
			 */
			$html = apply_filters( 'rmp_menu_html', $html, $this->menu_id );

			echo $html;
		}

		/**
		 * Function to return the prepared menu items.
		 *
		 * @since 4.0.0
		 *
		 * @return HTML|string
		 */
		public function menu() {

			$param = $this->rmp_nav_menu_args();

			if ( empty( $param ) ) {
				return;
			}

			$param['echo'] = false;

			$menu_markups = wp_nav_menu( $param );

			/**
			 * Filters the nav menu markups.
			 *
			 * @since 4.1.2
			 *
			 * @param HTML  $menu_markups
			 * @param int   $this->menu_id
			 * @param array $param
			 */
			$menu_markups = apply_filters( 'rmp_menu_markups', $menu_markups, $this->menu_id, $param );

			return $menu_markups;
		}

		/**
		 * Prepare the menu toggle button and return the contents.
		 *
		 * @since 4.0.0
		 *
		 * @return HTML|string
		 */
		public function menu_trigger() {

			$menu_trigger_type = '<span class="rmp-trigger-box">';

			// Normal state menu trigger type.
			if ( ! empty( $this->options['button_font_icon'] ) ) {
				$menu_trigger_type .= sprintf(
					'<span class="rmp-trigger-icon rmp-trigger-icon-inactive">%s</span>',
					$this->options['button_font_icon']
				);
			} elseif ( ! empty( $this->options['button_image'] ) ) {
				$menu_trigger_type .= sprintf(
					'<img src="%s" alt="%s" class="rmp-trigger-icon rmp-trigger-icon-inactive" width="100" height="100">',
					esc_url( $this->options['button_image'] ),
					rmp_image_alt_by_url( $this->options['button_image'] )
				);
			} else {
				$menu_trigger_type .= sprintf( '<span class="responsive-menu-pro-inner"></span>' );
			}

			// Active state menu trigger type.
			if ( ! empty( $this->options['button_font_icon_when_clicked'] ) ) {
				$menu_trigger_type .= sprintf(
					'<span class="rmp-trigger-icon rmp-trigger-icon-active">%s</span>',
					$this->options['button_font_icon_when_clicked']
				);
			} elseif ( ! empty( $this->options['button_image_when_clicked'] ) ) {
				$menu_trigger_type .= sprintf(
					'<img src="%s" alt="%s" class="rmp-trigger-icon rmp-trigger-icon-active" width="100" height="100">',
					esc_url( $this->options['button_image_when_clicked'] ),
					rmp_image_alt_by_url( $this->options['button_image_when_clicked'] )
				);
			}

			$menu_trigger_type .= '</span>';

			$menu_trigger_text     = '';
			$trigger_text_position = '';

			if ( ! empty( $this->options['button_title_position'] ) ) {
				$trigger_text_position = $this->options['button_title_position'];
			}

			// Menu trigger text.
			if ( ! empty( $this->options['button_title'] ) ) {
				$menu_trigger_text .= sprintf(
					'<span class="rmp-trigger-text">%s</span>',
					esc_html( $this->options['button_title'] )
				);

				if ( ! empty( $this->options['button_title_open'] ) ) {
					$menu_trigger_text .= sprintf(
						'<span class="rmp-trigger-text-open">%s</span>',
						esc_html( $this->options['button_title_open'] )
					);
				}

				$menu_trigger_text = sprintf(
					'<span class="rmp-trigger-label rmp-trigger-label-%s">
						%s
					</span>',
					esc_attr( $trigger_text_position ),
					$menu_trigger_text
				);
			}

			$menu_trigger_content = '';

			if ( 'left' === $trigger_text_position || 'top' === $trigger_text_position ) {
				$menu_trigger_content .= $menu_trigger_text;
			}

			$menu_trigger_content .= $menu_trigger_type;

			if ( 'bottom' === $trigger_text_position || 'right' === $trigger_text_position ) {
				$menu_trigger_content .= $menu_trigger_text;
			}

			$trigger_click_animation = '';
			if ( ! empty( $this->options['button_click_animation'] ) ) {
				$trigger_click_animation = 'rmp-menu-trigger-' . $this->options['button_click_animation'];
			}

			$toggle_theme_class = '';
			if ( ! empty( $this->options['menu_theme'] ) ) {
				$toggle_theme_class = 'rmp-' . str_replace( ' ', '-', strtolower( $this->options['menu_theme'] ) ) . '-trigger';
			}

			$toggle_theme_class = apply_filters( 'rmp_menu_toggle_classes', array( 'rmp_menu_trigger', $trigger_click_animation ), $this->menu_id );
			$toggle_theme_class = implode( ' ', $toggle_theme_class );
			if ( wp_is_mobile() ) {
				$toggle_theme_class .= ' rmp-mobile-device-menu';
			}
			$menu_trigger_destination = '';
			if ( ! empty( $this->options['hamburger_position_selector'] ) ) {
				$menu_trigger_destination = 'data-destination=' . $this->options['hamburger_position_selector'];
			}

			$rmp_menu_trigger = sprintf(
				'<button type="button"  aria-controls="rmp-container-%s" aria-label="Menu Trigger" id="rmp_menu_trigger-%s" %s class="%s">
					%s
				</button>',
				$this->menu_id,
				$this->menu_id,
				$menu_trigger_destination,
				esc_attr( $toggle_theme_class ),
				$menu_trigger_content
			);

			return $rmp_menu_trigger;
		}


		public function menu_title() {

			$menu_title_wrap = null;
			$menu_title      = '';
			if ( ! empty( $this->options['menu_title'] ) ) {
				$menu_title = $this->options['menu_title'];
			}

			$menu_image = '';
			if ( ! empty( $this->options['menu_title_image'] ) ) {
				$image_alt  = rmp_image_alt_by_url( $this->options['menu_title_image'] );
				$menu_image = sprintf(
					'<img class="rmp-menu-title-image" src="%1$s" alt="%2$s" title="%2$s" width="100" height="100"/>',
					esc_url( $this->options['menu_title_image'] ),
					esc_attr( $image_alt )
				);
			}

			if ( ! empty( $this->options['menu_title_font_icon'] ) ) {
				$menu_image = sprintf( '%s', $this->options['menu_title_font_icon'] );
			}

			$link_target = '_self';
			if ( ! empty( $this->options['menu_title_link_location'] ) ) {
				$link_target = $this->options['menu_title_link_location'];
			}
			$menu_title_wrap = '<div id="rmp-menu-title-' . esc_attr( $this->menu_id ) . '" class="rmp-menu-title">';
				$menu_title_wrap .= ! empty( $this->options['menu_title_link'] ) ? '<a href="' . esc_url( $this->options['menu_title_link'] ) .'" target="' .esc_attr( $link_target ) .'" class="rmp-menu-title-link" id="rmp-menu-title-link">' : '<span class="rmp-menu-title-link">';
				$menu_title_wrap .= $menu_image;
				$menu_title_wrap .= '<span>' . $menu_title .'</span>';
				$menu_title_wrap .= ! empty( $this->options['menu_title_link'] ) ? '</a>' : '</span>';
			$menu_title_wrap .= '</div>';
			$menu_title_wrap = apply_filters( 'rmp_menu_title_html', rm_sanitize_html_tags( $menu_title_wrap ) , $this->menu_id );
			return $menu_title_wrap;
		}

		public function menu_search_box() {

			$placeholder_text = '';
			if ( ! empty( $this->options['menu_search_box_text'] ) ) {
				$placeholder_text = $this->options['menu_search_box_text'];
			}
			if ( ! empty( $this->options['menu_search_box_code'] ) ) {
				$menu_search_wrap = sprintf(
					'<div id="rmp-search-box-%s" class="rmp-search-box">%s</div>',
					esc_attr( $this->menu_id ),
					$this->options['menu_search_box_code']
				);
			}else {
				$menu_search_wrap = sprintf(
					'
					<div id="rmp-search-box-%s" class="rmp-search-box">
						<form action="%s" class="rmp-search-form" role="search">
							<input type="search" name="s" title="Search"
								placeholder="%s"
								class="rmp-search-box">
						</form>
					</div>',
					esc_attr( $this->menu_id ),
					esc_url( home_url( '/' ) ),
					esc_attr( $placeholder_text )
				);
			}

			return $menu_search_wrap;
		}

		/**
		 * Function to prepare the the menu additional content section.
		 *
		 * @since 4.0.0
		 *
		 * @return HTML|string $content
		 */
		public function menu_additional_content() {

			$content = '';

			if ( ! empty( $this->options['menu_additional_content'] ) ) {

				// Remove script tags if found in menu contents.
				$content = preg_replace( '#<script(.*?)>(.*?)</script>#', '', $this->options['menu_additional_content'] );

				$content = do_shortcode( $content );
			}

			$content = sprintf(
				'<div id="rmp-menu-additional-content-%s" class="rmp-menu-additional-content">%s</div>',
				esc_attr( $this->menu_id ),
				$content
			);

			/**
			 * Filters the menu additional contents markups.
			 *
			 * @since 4.1.0
			 *
			 * @param string $content
			 * @param int    $menu_id
			 */
			$content = apply_filters( 'menu_additional_content_html', $content, $this->menu_id );

			return $content;
		}

		/**
		 * Function to prepare the menu social icons section.
		 *
		 * @since 4.6.0
		 *
		 * @return void
		 */
		public function menu_social_icons() {
			$social_icons = array();

			if ( ! empty( $this->options['menu_social_icons'] ) && is_array( $this->options['menu_social_icons'] ) ) {
				$social_icons = $this->options['menu_social_icons'];
			}

			if ( empty( $social_icons ) ) {
				return;
			}

			$layout = ! empty( $this->options['menu_social_icons_layout'] ) ? $this->options['menu_social_icons_layout'] : 'horizontal';
			$layout = in_array( $layout, array( 'horizontal', 'vertical' ), true ) ? $layout : 'horizontal';

			$alignment = ! empty( $this->options['menu_social_icons_alignment'] ) ? $this->options['menu_social_icons_alignment'] : 'left';
			$alignment = in_array( $alignment, array( 'left', 'center', 'right' ), true ) ? $alignment : 'left';

			$wrapper_classes = array(
				'rmp-menu-social-icons',
				'rmp-social-icons-layout-' . $layout,
				'rmp-social-icons-align-' . $alignment,
			);
			$content = '<div id="rmp-menu-social-icons-' . esc_attr( $this->menu_id ) . '" class="' . esc_attr( implode( ' ', $wrapper_classes ) ) . '">
				<ul class="rmp-social-icons-list">
					';
					foreach ( $social_icons as $index => $icon ) {
						$icon_value = ! empty( $icon['icon'] ) ? wp_specialchars_decode( (string) $icon['icon'], ENT_QUOTES ) : '';
						$icon_value = trim( $icon_value );

						if ( '' === $icon_value ) {
							continue;
						}

						$icon_link        = ! empty( $icon['link'] ) ? esc_url( $icon['link'] ) : '';

						if ( false !== strpos( $icon_value, '<' ) ) {
							$icon_markup = rm_sanitize_html_tags( $icon_value );
						} else {
							$icon_markup = sprintf( '<span class="rmp-font-icon %s"></span>', esc_attr( $icon_value ) );
						}
						$content .= '<li class="rmp-social-icon-item rmp-social-icon-item--' . esc_attr( $index ) . '">';
						if ( ! empty( $icon_link ) ) {
							$content .= '<a href="' . $icon_link . '" target="_blank" rel="noopener noreferrer" class="rmp-social-icon-link">';
							$content .= '<span class="rmp-social-icon">' . $icon_markup . '</span>';
							$content .= '</a>';
						} else {
							$content .= '<span class="rmp-social-icon-link rmp-social-icon-link--no-link">';
							$content .= '<span class="rmp-social-icon">' . $icon_markup . '</span>';
							$content .= '</span>';
						}
						$content .= '</li>';
					}
				$content .= '</ul></div>';

			/**
			 * Filters the menu social icons markups.
			 *
			 * @since 4.6.0
			 *
			 * @param string $html
			 * @param int    $menu_id
			 */
			return apply_filters( 'rmp_menu_social_icons_html', $content, $this->menu_id );
		}

		public function rmp_nav_menu_args( $args = null ) {

			$menu          = $this->get_wp_menu_to_use();
			$menu_location = $this->get_wp_menu_location();
			$wp_menu_obj   = wp_get_nav_menu_object( $menu );

			// Check menu object is not empty.
			if ( empty( $wp_menu_obj ) ) {
				return $args;
			}

			$menu_depth = 0;
			if ( ! empty( $this->options['menu_depth'] ) ) {
				$menu_depth = $this->options['menu_depth'];
			}

			$menu_label = ! empty( $this->options['menu_name'] ) ? $this->options['menu_name'] : 'Default';

			if ( empty( $menu_label ) ) {
				$menu_label = $menu;
			}

			$item_wrap_attrs = array(
				'id'         => '%1$s',
				'class'      => '%2$s',
				'role'       => 'menubar',
				'aria-label' => $menu_label,
			);

			$wrap_attributes = apply_filters( 'rmp_wrap_attributes', $item_wrap_attrs, $this->menu_id, $menu_location );

			$attributes = '';
			foreach ( $wrap_attributes as $attribute => $value ) {
				if ( ! empty( $value ) ) {
					$attributes .= sprintf( ' %s="%s"', $attribute, esc_attr( $value ) );
				}
			}

			$walker = new Walker( $this->options );
			if ( ! empty( $this->options['custom_walker'] ) ) {
				$walker = new $this->options['custom_walker']( $this->options );
			}

			$param = array(
				'container'       => 'div',
				'container_id'    => 'rmp-menu-wrap-' . $this->menu_id,
				'container_class' => 'rmp-menu-wrap',
				'menu_id'         => 'rmp-menu-' . $this->menu_id,
				'menu_class'      => 'rmp-menu',
				'menu'            => $wp_menu_obj,
				'depth'           => $menu_depth,
				'fallback_cb'     => 'wp_page_menu',
				'before'          => '',
				'after'           => '',
				'link_before'     => '',
				'link_after'      => '',
				'theme_location'  => $menu_location,
				'walker'          => $walker,
				'items_wrap'      => '<ul' . $attributes . '>%3$s</ul>',
			);

			$param = apply_filters( 'rmp_nav_menu_args', $param, $wp_menu_obj->term_id, $menu_location );
			return $param;
		}

		/**
		 * Function to add the menu in theme location.
		 *
		 * @param array $args
		 */
		public function rmp_location_wise_nav_menu_args( $args ) {

			if ( empty( $args['theme_location'] ) ) {
				return $args;
			}

			if ( $args['theme_location'] !== $this->get_wp_menu_location() ) {
				return $args;
			}

			$param = $this->rmp_nav_menu_args();
			$args  = array_merge( $args, $param );

			return $args;
		}

		/**
		 * Function to returns the correct wp menu.
		 *
		 * @since 4.0.0
		 *
		 * @return string $menu
		 */
		public function get_wp_menu_to_use() {

			$menu = '';

			// Set menu as per settings priority.
			if ( ! empty( $this->options['different_menu_for_mobile'] ) && 'on' === $this->options['different_menu_for_mobile'] && wp_is_mobile() ) {
				$menu = $this->options['menu_to_use_in_mobile'];
			} elseif ( ! empty( $this->options['theme_location_menu'] ) && has_nav_menu( $this->options['theme_location_menu'] ) ) {
				$menu = get_term( get_nav_menu_locations()[ $this->options['theme_location_menu'] ], 'nav_menu' )->term_id;
			} elseif ( ! empty( $this->options['menu_to_use'] ) ) {
				$menu = $this->options['menu_to_use'];
			} elseif ( ! empty( get_terms( 'nav_menu' )[0]->term_id ) ) {
				$menu = get_terms( 'nav_menu' )[0]->term_id;
			}
			return $menu;
		}

		/**
		 * Function to returns the theme location when it required.
		 *
		 * @since 4.0.2
		 *
		 * @return string $theme_location
		 */
		public function get_wp_menu_location() {

			// Check if theme location is enable options is on and device is mobile then return null.
			if ( empty( $this->options['use_current_theme_location'] ) || 'off' == $this->options['use_current_theme_location'] || wp_is_mobile() ) {
				return;
			}

			$menu = $this->get_wp_menu_to_use();
			if ( empty( $menu ) ) {
				return;
			}

			$theme_location  = null;
			$menu_object     = wp_get_nav_menu_object( $menu );
			$theme_locations = get_nav_menu_locations();

			foreach ( $theme_locations as $location => $value ) {

				// If WPML is activated
				if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
					$value = apply_filters( 'wpml_object_id', $value, 'nav_menu', false, ICL_LANGUAGE_CODE );
				}

				if ( $value === $menu_object->term_id ) {
					$theme_location = $location;
					break;
				}
			}

			return $theme_location;
		}

		/**
		 * Function to prepare the headerbar with header items.
		 *
		 * @since 4.0.0
		 *
		 * @return HTML|string
		 */
		public function get_rmp_header() {

			$header_item_html = '';
			$header_items     = array();
			if ( ! empty( $this->options['header_bar_items_order'] ) ) {
				$header_items = $this->options['header_bar_items_order'];
			}

			foreach ( $header_items as $key => $value ) {
				if ( ! empty( $value ) && $value === 'on' ) {
					if ( 'menu' === $key ) {
						$header_item_html .= $this->mobile_menu();
					} elseif ( 'title' === $key ) {
						$header_item_html .= $this->get_rmp_header_title();
					} elseif ( 'search' === $key ) {
						$header_item_html .= $this->get_rmp_header_search();
					} elseif ( 'logo' === $key ) {
						$header_item_html .= $this->get_rmp_header_logo();
					} else {
						$header_item_html .= $this->get_rmp_header_content();
					}
				}
			}

			$header_container_classes = apply_filters( 'rmp_header_container_classes', array( 'rmp-header-bar-container' ), $this->menu_id );
			$header_container_classes = implode( ' ', $header_container_classes );

			return sprintf(
				'<div id="rmp-header-bar-%s" class="%s">
					<div class="rmp-header-bar-items">
						%s
					</div>
				</div>',
				esc_attr( $this->menu_id ),
				esc_attr( $header_container_classes ),
				$header_item_html
			);
		}

		/**
		 * Function to returns the header title contents.
		 *
		 * @since 4.0.0
		 *
		 * @return HTML|string
		 */
		public function get_rmp_header_title() {

			$menu_title = '';
			if ( ! empty( $this->options['header_bar_title'] ) ) {
				$menu_title = esc_html( $this->options['header_bar_title'] );
			}

			if ( ! empty( $this->options['header_bar_logo_link'] ) ) {
				$menu_title = sprintf(
					'<a href="%s">%s</a>',
					esc_url( $this->options['header_bar_logo_link'] ),
					$menu_title
				);
			}

			return sprintf(
				'<div id="rmp-header-title-%s" class="rmp-header-bar-item rmp-header-bar-title">%s</div>',
				esc_attr( $this->menu_id ),
				$menu_title
			);
		}

		public function get_rmp_header_logo() {

			$header_logo = '';
			if ( ! empty( $this->options['header_bar_logo'] ) ) {
				$image_alt   = rmp_image_alt_by_url( $this->options['header_bar_logo'] );
				$header_logo = sprintf(
					'<img class="rmp-menu-header-logo" src="%1$s" alt="%2$s" title="%2$s"/>',
					esc_url( $this->options['header_bar_logo'] ),
					esc_attr( $image_alt )
				);
			}

			if ( ! empty( $this->options['header_bar_logo_link'] ) ) {
				$header_logo = sprintf(
					'<a href="%s">%s</a>',
					esc_url( $this->options['header_bar_logo_link'] ),
					$header_logo
				);
			}

			return sprintf(
				'<div id="rmp-header-logo-%s" class="rmp-header-bar-item rmp-header-bar-logo">%s</div>',
				esc_attr( $this->menu_id ),
				$header_logo
			);
		}

		public function get_rmp_header_search() {

			$placeholder_text = '';
			if ( ! empty( $this->options['menu_search_box_text'] ) ) {
				$placeholder_text = $this->options['menu_search_box_text'];
			}

			return sprintf(
				'
				<div id="rmp-header-search-box-%s" class="rmp-header-bar-item rmp-header-bar-search">
					<form action="%s" class="rmp-search-form" role="search">
						<input type="search" name="s" title="Search"
							placeholder="%s"
							class="rmp-search-box">
					</form>
				</div>',
				esc_attr( $this->menu_id ),
				esc_url( home_url( '/' ) ),
				esc_attr( $placeholder_text )
			);

		}

		/**
		 * Function to prepare the header html contents.
		 *
		 * @since 4.0.0
		 *
		 * @return HTML|string
		 */
		public function get_rmp_header_content() {

			$content = '';

			if ( ! empty( $this->options['header_bar_html_content'] ) ) {

				// Remove script tags if found in menu contents.
				$content = preg_replace( '#<script(.*?)>(.*?)</script>#', '', $this->options['header_bar_html_content'] );
				$content = do_shortcode( $content );

			}

			return sprintf(
				'<div id="rmp-header-additional-content-%s" class="rmp-header-bar-item rmp-header-bar-contents">%s</div>',
				esc_attr( $this->menu_id ),
				$content
			);

		}

	}
endif;
