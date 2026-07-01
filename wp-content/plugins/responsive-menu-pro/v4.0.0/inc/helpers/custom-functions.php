<?php
/**
 * RMP features custom functions.
 *
 * @version 4.0.0
 *
 * @package responsive-menu-pro
 */

//namespace RMP\Features\Inc\Helpers;

/**
 * Function to check the input is checked or not.
 *
 * @version 4.0.0
 *
 * @param string $actual_value
 *
 * @return string|null
 */
function is_rmp_option_checked( $actual_value, $options, $key ) {

    if ( empty( $options[ $key ] ) ) {
        return;
    }

    $checked_value = $options[ $key ];
    if ( $actual_value == $checked_value ) {
        return "checked";
    }

    return;
}

/**
 * Function to check the input is disabled or not.
 *
 * @version 4.0.0
 *
 * @param string $actual_value
 *
 * @return string|null
 */
function is_rmp_option_disabled( $actual_value, $options, $key ) {

    if ( empty( $options[ $key ] ) ) {
        return;
    }

    $checked_value = $options[ $key ];
    if ( $actual_value == $checked_value ) {
        return "disabled";
    }

    return;
}

/**
 * Function to return the value from option
 *
 * @version 4.0.0
 *
 * @param string $key
 * @param array  $options
 *
 * @return string|array
 */
function rmp_get_value( $options , $key ) {

	if ( empty( $options[ $key ] ) ) {
		return;
	}

	return $options[ $key ];
}

function rmp_get_list_of_pages() {

    $posts = get_posts( [
        'numberposts' => 10,
        'post_type'   => 'any',
        'fields'      => 'ids',
    ] );

    $all_pages = [];

    foreach ( $posts as $post_id ) {
        $all_pages[ $post_id ] = get_the_title($post_id);
    }
    wp_reset_postdata();
    return $all_pages;
}

function rmp_get_pages_list() {

    check_ajax_referer( 'rmp_nonce', 'ajax_nonce' );
    if ( ! current_user_can( 'administrator' ) ) {
        wp_send_json_error( array( 'message' => __( 'You can not access pages !', 'responsive-menu-pro' ) ) );
    }

    $title = sanitize_text_field( $_POST['title'] );
    if ( empty( $title ) ) {
        wp_send_json_error( array( 'message' => __( 'Enter the page name !', 'responsive-menu-pro' ) ) );
    }

    $posts = get_posts( [
        'numberposts' => -1,
        'post_type'   => 'any',
        's'           => $title,
        'fields'      => 'ids',
    ] );

    $rows = array();
    foreach ( $posts as $post_id ) {
        $rows[] = array(
			"id"         => $post_id,
			"post_title" => get_the_title($post_id),
		);
    }
    wp_reset_postdata();
    wp_send_json_success( array( 'list' => json_encode( $rows ) ) );
}
add_action( 'wp_ajax_rmp_get_pages_list', 'rmp_get_pages_list' );


function rmp_all_fab_icons() {

    $brands = get_transient( 'rmp_fab_icon_contents' );
    if ( empty( $brands ) ) {
        $json_object = rmp_get_fa_icons();

        $brands   = array();
        foreach ( $json_object  as $key => $data ) {
            $styles = $data->styles;
            if ( in_array( 'brands', $styles ) ) {
                $brands[ 'fab-'.$data->unicode ] = 'fa-' . $key;
            }
        }

        set_transient('rmp_fab_icon_contents', $brands, ( 7 * DAY_IN_SECONDS ) );
    }

    $brands = apply_filters( "rmp_fab", $brands );
    krsort( $brands );

    return $brands;
}


function rmp_all_fas_icons() {

    $solid = get_transient( 'rmp_fas_icon_contents' );
    if ( empty( $solid ) ) {
        $json_object = rmp_get_fa_icons();

        $solid   = array();
        foreach ( $json_object  as $key => $data ) {
            $styles = $data->styles;
            if ( in_array( 'solid', $styles ) ) {
                $solid[ 'fas-'.$data->unicode ] = 'fa-' . $key;
            }
        }

        set_transient('rmp_fas_icon_contents', $solid, ( 7 * DAY_IN_SECONDS ) );

    }

    $solid = apply_filters( "rmp_fas", $solid );
    krsort( $solid );

    return $solid;
}


function rmp_all_far_icons() {

    $regular = get_transient( 'rmp_far_icon_contents' );

    if ( empty( $regular ) ) {
        $json_object = rmp_get_fa_icons();

        $regular = array();
        foreach ( $json_object  as $key => $data ) {
            $styles = $data->styles;
            if ( in_array( 'regular', $styles ) ) {
                $regular[ 'far-'.$data->unicode ] = 'fa-' . $key;
            }
        }

        set_transient('rmp_far_icon_contents', $regular, ( 7 * DAY_IN_SECONDS ) );
    }

    $regular = apply_filters( "rmp_far", $regular );
    krsort( $regular );

    return $regular;
}

/**
 * Return the form to select a dashicon
 *
 * @since 1.5.2
 * @return string
 */
function rmp_dashicon_selector() {

    $return = '';
    foreach ( rmp_all_dash_icons() as $code => $class ) {

        $bits = explode( "-", $code );
        $code = "&#x" . $bits[1] . "";
        $type = $bits[0];

        $return .= sprintf( '<div class="%s font-icon">', esc_attr($type) );
        $return .= sprintf('<input class="radio" id="%1$s" type="radio" rel="%2$s" name="icon" value="dashicons %1$s" />', esc_attr($class), esc_attr($code) );
        $return .= sprintf('<label rel="%1$s" for="%2$s" title="%2$s" ></label>', $code, esc_attr($class) );
        $return .= "</div>";

    }

    return $return;
}

/**
 * Return the form to select a glyphicon
 *
 * @since 1.5.2
 * @return string
 */
function rmp_glyphicon_selector() {

    $return = '';
    foreach ( rmp_all_glyph_icons() as $code => $class ) {

        $bits = explode( "-", $code );
        $code = "&#x" . $bits[1] . "";
        $type = $bits[0];

        $return .= sprintf( '<div class="%s font-icon">', esc_attr($type) );
        $return .= sprintf('<input class="radio" id="%1$s" type="radio" rel="%2$s" name="icon" value="glyphicon %1$s" />', esc_attr($class), esc_attr($code) );
        $return .= sprintf('<label rel="%1$s" for="%2$s" title="%2$s"></label>', $code, esc_attr($class) );
        $return .= "</div>";

    }

    return $return;
}

/**
 * Return the form to select a glyphicon
 *
 * @since 1.5.2
 * @return string
 */
function rmp_fab_selector() {

    $return = '';
    foreach ( rmp_all_fab_icons() as $code => $class ) {

        $bits = explode( "-", $code );
        $code = '&#x'. $bits[1] . "";
        $type = $bits[0];

        $return .= sprintf( '<div class="%s font-icon">', esc_attr($type) );
        $return .= sprintf('<input class="radio" id="fab-%1$s" type="radio" rel="%2$s" name="icon" value="fab %1$s" />', esc_attr($class), esc_attr($code) );
        $return .= sprintf('<label rel="%1$s" for="fab-%2$s" title="%2$s"></label>', $code, esc_attr($class) );

        $return .= "</div>";
    }

    return $return;
}

/**
 * Return the form to select a glyphicon
 *
 * @since 1.5.2
 * @return string
 */
function rmp_far_selector() {

    $return = '';
    foreach ( rmp_all_far_icons() as $code => $class ) {

      $bits = explode( "-", $code );
      $code = '&#x'. $bits[1] . "";
      $type = $bits[0];

      $return .= sprintf( '<div class="%s font-icon">', esc_attr($type) );
      $return .= sprintf('<input class="radio" id="far-%1$s" type="radio" rel="%2$s" name="icon" value="far %1$s" />', esc_attr($class), esc_attr($code) );
      $return .= sprintf('<label rel="%1$s" for="far-%2$s" title="%2$s"></label>', $code, esc_attr($class) );

      $return .= "</div>";
    }

    return $return;
}

/**
 * Return the form to select a glyphicon
 *
 * @since 1.5.2
 * @return string
 */
function rmp_fas_selector() {

    $return = '';
    foreach ( rmp_all_fas_icons() as $code => $class ) {

        $bits = explode( "-", $code );
        $code = '&#x'. $bits[1] . "";
        $type = $bits[0];

        $return .= sprintf( '<div class="%s font-icon">', esc_attr($type) );
        $return .= sprintf('<input class="radio" id="fas-%1$s" type="radio" rel="%2$s" name="icon" value="fas %1$s" />', esc_attr($class), esc_attr($code) );
        $return .= sprintf('<label rel="%1$s" for="fas-%2$s" title="%2$s"></label>', $code, esc_attr($class) );

        $return .= "</div>";
    }

    return $return;
}

/**
 * Return the form to select a glyphicon
 *
 * @since 1.5.2
 * @return string
 */
function rmp_mdi_selector() {

    $return = '';
    foreach ( rmp_all_mdi_icons() as $code => $class ) {

        $bits = explode( "-", $code );
        $code = '&#x'. $bits[1] . "";
        $type = $bits[0];

        $return .= sprintf( '<div class="%s font-icon">', esc_attr($type) );
        $return .= sprintf('<input class="radio" id="mdi-%1$s" type="radio" rel="%2$s" name="icon" value="material-icons %1$s" />', esc_attr($class), esc_attr($code) );
        $return .= sprintf('<label rel="%1$s" for="mdi-%2$s" title="%2$s"></label>', $code, esc_attr($class) );

        $return .= "</div>";
    }

    return $return;
}


/**
 * Function to return the all menu ids of published menu.
 *
 * @since 4.0.0
 * @return array $menu_ids;
 */
function get_all_rmp_menu_ids() {

    $args = array(
        'post_type'      => 'rmp_menu',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    $all_menus    = get_posts( $args );
	$menu_ids = array();

	if ( ! empty( $all_menus ) ) {
		foreach ( $all_menus as $menu ) {
			setup_postdata( $menu );
			$menu_ids[] = $menu->ID;
		}
	}
    wp_reset_postdata();
	return $menu_ids;
}

/**
 * Function to return the all published menu list.
 *
 * @since 4.0.0
 * @return array;
 */
function rmp_get_all_menus() {

    $args = array(
        'post_type'      => 'rmp_menu',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    $all_menus    = get_posts( $args );
	$menus = array();

	if ( ! empty( $all_menus ) ) {
		foreach ( $all_menus as $menu ) {
			setup_postdata( $menu );
			$menus[ $menu->ID ] = $menu->post_title;
		}
	}
    wp_reset_postdata();
	return $menus;
}



/**
 * Get image alt text by image URL
 *
 * @param String $image_url
 *
 * @return Bool | String
 */
function rmp_image_alt_by_url( $image_url ) {
    global $wpdb;

    if ( empty( $image_url ) ) {
        return '';
    }

    $query_arr  = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE guid='%s';", strtolower( $image_url ) ) );
    $image_id   = ( ! empty( $query_arr ) ) ? $query_arr[0] : 0;

    return get_post_meta( $image_id, '_wp_attachment_image_alt', true );
}

/**
 * Return the menu items.
 */
function rmp_get_wp_nav_menu_items( $options ) {

    $menu = '';

    if ( ! empty( $options['theme_location_menu'] ) && has_nav_menu( $options['theme_location_menu'] ) ) {
        $menu = get_term(get_nav_menu_locations()[ $options['theme_location_menu'] ], 'nav_menu')->term_id;
    } elseif ( ! empty($options['menu_to_use'] ) ) {
        $menu = $options['menu_to_use'];
    } elseif ( ! empty( get_terms('nav_menu')[0]->term_id ) ) {
        $menu = get_terms('nav_menu')[0]->term_id;
    }

    return wp_get_nav_menu_items($menu);
}

/**
 * Sanitizes multi-dimentional array
 *
 * @since 4.1.6
 */
function rm_sanitize_rec_array( $array, $allowhtml = false ) {
    if ( ! is_array( $array ) ) {
        return $allowhtml ? rm_sanitize_html_tags( wp_specialchars_decode( (string) $array, ENT_QUOTES ) ) : sanitize_text_field( (string) $array );
    }
    foreach ( $array as $key => $value ) {
        if ( is_array( $value ) ) {
            $array[ $key ] = rm_sanitize_rec_array( $value, $allowhtml );
        } else {
            $array[ $key ] = $allowhtml ? rm_sanitize_html_tags( wp_specialchars_decode( (string) $value, ENT_QUOTES ) ) : sanitize_text_field( (string) $value );
        }
    }
    return $array;
}

function rm_sanitize_html_tags( $content ) {
    // Define allowed HTML tags and attributes
    $common_attrs = array(
		'class' => true,
		'id'    => true,
	);
	$form_common_attrs = array_merge($common_attrs, array(
		'name'     => true,
		'disabled' => true,
		'required' => true,
		'readonly' => true,
	));

	$allowed_tags = array(
		'svg'      => array(
			'xmlns'   => true,
			'viewBox' => true,
			'width'   => true,
			'height'  => true,
			'fill'    => true,
		),
		'path'     => array(
			'd'               => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
		),
		'div'      => $common_attrs,
		'span'     => $common_attrs,
		'br'       => true,
		'b'        => true,
		'strong'   => true,
		'img'      => array(
			'src'    => true,
			'alt'    => true,
			'width'  => true,
			'height' => true,
			'class'  => true,
			'id'     => true,
			'style'  => true,
		),
		'i'        => $common_attrs,
		'label'    => $common_attrs,
		'a'        => array(
			'href'   => true,
			'target' => true,
			'rel'    => true,
		),
		'h1'       => $common_attrs,
		'h2'       => $common_attrs,
		'h3'       => $common_attrs,
		'h4'       => $common_attrs,
		'h5'       => $common_attrs,
		'h6'       => $common_attrs,
		'p'        => $common_attrs,
		'form'     => array(
			'action' => true,
			'method' => true,
			'class'  => true,
			'id'     => true,
		),
		'input'    => array_merge($form_common_attrs, array(
			'type'        => true,
			'value'       => true,
			'placeholder' => true,
			'checked'     => true,
		)),
		'textarea' => array_merge($form_common_attrs, array(
			'placeholder' => true,
			'rows'        => true,
			'cols'        => true,
		)),
		'select'   => $form_common_attrs,
		'option'   => array(
			'value'    => true,
			'selected' => true,
		),
		'button'   => array_merge($form_common_attrs, array(
			'type'  => true,
			'value' => true,
		)),
	);

    // Sanitize content
    return wp_kses($content, $allowed_tags);
}

/**
 * Add RM customize button for admin menus
 * @since 4.2.3
 */
function add_rmp_customize_button_to_save_menu() {
	global $pagenow;
    if ( 'edit.php' === $pagenow && ! empty( $_REQUEST['post_type'] ) && ! empty( $_REQUEST['open'] ) && 'rmp_menu' === $_REQUEST['post_type'] && 'wizard' === $_REQUEST['open'] ) {
		$inline_script = "jQuery('#rmp-new-menu-wizard').fadeIn();";
		if ( ! empty( $_REQUEST['menu-to-use'] ) ) {
			$inline_script .= "jQuery('#rmp-menu-to-use').val('".esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['menu-to-use'] ) ) ) ."' );";
		}
		wp_add_inline_script( 'rmp_admin_scripts', $inline_script );
	}
    // Check if it's the admin menu page
    if ( 'nav-menus.php' === $pagenow ) {
		$menu_id = isset($_REQUEST['menu']) ? sanitize_text_field( wp_unslash( intval( $_REQUEST['menu'] ) ) ) : absint( get_user_option( 'nav_menu_recently_edited' ) );
		$nav_menus  = wp_get_nav_menus();
		if ( ( empty( $menu_id ) || ! is_nav_menu( $menu_id ) ) && 0 < count( $nav_menus ) ) {
			$menu_id = $nav_menus[0]->term_id;
		}
		$rmp_customize_menu = admin_url( 'edit.php?post_type=rmp_menu&open=wizard' );
		if ( ! empty( $menu_id ) && is_nav_menu( $menu_id ) ) {
            $rmp_customize_menu = admin_url( 'edit.php?post_type=rmp_menu&open=wizard&menu-to-use='.esc_attr( $menu_id ) );
            $query = new WP_Query(array(
                'post_type'      => 'rmp_menu',
                'posts_per_page' => 1,
                'meta_query'     => array(
                    array(
                        'key'     => 'rmp_menu_meta',
                        'value'   => '"menu_to_use";s:'.strlen( $menu_id ).':"'. esc_sql( $menu_id ).'";',
                        'compare' => 'LIKE',
                    ),
                ),
            ));
            if ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();
                wp_reset_postdata();
                $rmp_customize_menu = admin_url( 'post.php?post='.esc_attr( $post_id ).'&action=edit&editor=true' );
			}
        }
        $inline_script = "jQuery(document).ready(function($) {
			$('#save_menu_footer').before('<a href=\"".esc_url( $rmp_customize_menu )."\" style=\"margin-right:5px;\" class=\"button button-secondary button-large rmp-customize-menu\">". __('Customize Menu', 'responsive-menu-pro') ."</a>');
            $(document).on('change', '.edit-menu-item-hide-rmp-setting, .edit-menu-item-show-rmp-setting, .edit-menu-item-hide-login-rmp-setting, .edit-menu-item-hide-nonlogin-rmp-setting', function() {
                var parent = $(this).closest('.menu-item-edit-active');
                var hideCheckbox = parent.find('.edit-menu-item-hide-rmp-setting');
                var showCheckbox = parent.find('.edit-menu-item-show-rmp-setting');
                var hideLoginCheckbox = parent.find('.edit-menu-item-hide-login-rmp-setting');
                var hideNonLoginCheckbox = parent.find('.edit-menu-item-hide-nonlogin-rmp-setting');

                if (hideCheckbox.is(':checked')) {
                    showCheckbox.prop('disabled', true);
                } else {
                    showCheckbox.prop('disabled', false);
                }

                if (showCheckbox.is(':checked')) {
                    hideCheckbox.prop('disabled', true);
                } else {
                    hideCheckbox.prop('disabled', false);
                }

                if (hideLoginCheckbox.is(':checked')) {
                    hideNonLoginCheckbox.prop('disabled', true);
                } else {
                    hideNonLoginCheckbox.prop('disabled', false);
                }

                if (hideNonLoginCheckbox.is(':checked')) {
                    hideLoginCheckbox.prop('disabled', true);
                } else {
                    hideLoginCheckbox.prop('disabled', false);
                }
            });
		});";
		// Enqueue the script
		wp_add_inline_script( 'admin-bar', $inline_script  );
	}
}
add_action('admin_footer', 'add_rmp_customize_button_to_save_menu', 999);

/*
 * Add custom fields to WordPress navigation menu item settings
 * @since 4.2.3
 */
function rmp_custom_menu_item_settings( $item_id, $item, $depth, $args ) {
    // Retrieve the custom setting value for the menu item
    $show_rmp_setting = get_post_meta($item_id, '_show_rmp_setting', true);
    $hide_rmp_setting = get_post_meta($item_id, '_hide_rmp_setting', true);
    $hide_login_rmp_setting = get_post_meta($item_id, '_hide_login_rmp_setting', true);
    $hide_nonlogin_rmp_setting = get_post_meta($item_id, '_hide_nonlogin_rmp_setting', true);

    // Output the custom setting field
    ?>
    <p class="rmp-menu-settings-heading description description-wide">
        <strong><?php esc_html_e('RMP Settings', 'responsive-menu-pro'); ?></strong>
    </p>
    <p class="field-show-rmp description description-thin">
        <label for="edit-menu-item-show-rmp-setting-<?php echo esc_attr($item_id); ?>">
            <input type="checkbox" class="edit-menu-item-show-rmp-setting" id="edit-menu-item-show-rmp-setting-<?php echo esc_attr($item_id); ?>" name="menu-item-show-rmp-setting[<?php echo esc_attr($item_id); ?>]" <?php checked($show_rmp_setting, 'on'); echo 'on' === $hide_rmp_setting ? 'disabled' : ''; ?>  value="on"/>
            <?php esc_html_e('Show only in RMP menus', 'responsive-menu-pro'); ?><br />
        </label>
    </p>
    <p class="field-hide-rmp description description-thin">
        <label for="edit-menu-item-hide-rmp-setting-<?php echo esc_attr($item_id); ?>">
            <input type="checkbox" class="edit-menu-item-hide-rmp-setting" id="edit-menu-item-hide-rmp-setting-<?php echo esc_attr($item_id); ?>" name="menu-item-hide-rmp-setting[<?php echo esc_attr($item_id); ?>]" <?php checked($hide_rmp_setting, 'on'); echo 'on' === $show_rmp_setting ? 'disabled' : ''; ?> value="on" />
            <?php esc_html_e('Hide only in RMP menus', 'responsive-menu-pro'); ?><br />
        </label>
    </p>
    <p class="field-hide-login-rmp description description-thin">
        <label for="edit-menu-item-hide-login-rmp-setting-<?php echo esc_attr($item_id); ?>">
            <input type="checkbox" class="edit-menu-item-hide-login-rmp-setting" id="edit-menu-item-hide-login-rmp-setting-<?php echo esc_attr($item_id); ?>" name="menu-item-hide-login-rmp-setting[<?php echo esc_attr($item_id); ?>]" <?php checked($hide_login_rmp_setting, 'on'); echo 'on' === $hide_nonlogin_rmp_setting ? 'disabled' : ''; ?> value="on"/>
            <?php esc_html_e('Hide for logged in users', 'responsive-menu-pro'); ?><br />
        </label>
    </p>
    <p class="field-hide-nonlogin-rmp description description-thin">
        <label for="edit-menu-item-hide-nonlogin-rmp-setting-<?php echo esc_attr($item_id); ?>">
            <input type="checkbox" class="edit-menu-item-hide-nonlogin-rmp-setting" id="edit-menu-item-hide-nonlogin-rmp-setting-<?php echo esc_attr($item_id); ?>" name="menu-item-hide-nonlogin-rmp-setting[<?php echo esc_attr($item_id); ?>]" <?php checked($hide_nonlogin_rmp_setting, 'on'); echo 'on' === $hide_login_rmp_setting ? 'disabled' : ''; ?> value="on" />
            <?php esc_html_e('Hide for non logged in users', 'responsive-menu-pro'); ?><br />
        </label>
    </p>
    <?php
}
add_action('wp_nav_menu_item_custom_fields', 'rmp_custom_menu_item_settings', 10, 4);

/*
 * Save custom field value for WordPress navigation menu item
 * @since 4.2.3
 */
function rmp_save_custom_menu_item_setting( $menu_id, $menu_item_db_id, $menu_item_args ) {
    // Save standard RMP visibility settings
    $custom_setting = ! empty( $_POST['menu-item-show-rmp-setting'][ $menu_item_db_id ] ) ? 'on' : 'off';
    update_post_meta($menu_item_db_id, '_show_rmp_setting', $custom_setting);
    $custom_setting = ! empty( $_POST['menu-item-hide-rmp-setting'][ $menu_item_db_id ] ) ? 'on' : 'off';
    update_post_meta($menu_item_db_id, '_hide_rmp_setting', $custom_setting);
    
    // Save login-related visibility settings
    $custom_setting = ! empty( $_POST['menu-item-hide-login-rmp-setting'][ $menu_item_db_id ] ) ? 'on' : 'off';
    update_post_meta($menu_item_db_id, '_hide_login_rmp_setting', $custom_setting);
    $custom_setting = ! empty( $_POST['menu-item-hide-nonlogin-rmp-setting'][ $menu_item_db_id ] ) ? 'on' : 'off';
    update_post_meta($menu_item_db_id, '_hide_nonlogin_rmp_setting', $custom_setting);
}
add_action('wp_update_nav_menu_item', 'rmp_save_custom_menu_item_setting', 10, 3);

/*
 * Modify menu items based on custom setting
 * @since 4.2.3
 */
function rmp_modify_menu_items( $items, $args ) {
    foreach ( $items as $key => $item ) {
        $show_rmp_setting = get_post_meta($item->ID, '_show_rmp_setting', true);
        $hide_rmp_setting = get_post_meta($item->ID, '_hide_rmp_setting', true);
        $hide_login_rmp_setting = get_post_meta($item->ID, '_hide_login_rmp_setting', true);
        $hide_nonlogin_rmp_setting = get_post_meta($item->ID, '_hide_nonlogin_rmp_setting', true);
        
        // Check standard RMP visibility settings
        if ( ( 'on' === $show_rmp_setting && 'rmp-menu-wrap' !== $args->container_class ) || 
             ( 'on' === $hide_rmp_setting && 'rmp-menu-wrap' === $args->container_class ) ) {
            // Hide the menu item if the custom setting is enabled
            unset($items[ $key ]);
            continue;
        }
        
        // Check login-based visibility settings
        if ( 'on' === $hide_login_rmp_setting && is_user_logged_in() ) {
            // Hide for logged-in users
            unset($items[ $key ]);
            continue;
        }
        
        if ( 'on' === $hide_nonlogin_rmp_setting && !is_user_logged_in() ) {
            // Hide for non-logged-in users
            unset($items[ $key ]);
            continue;
        }
    }

    return $items;
}
add_filter('wp_nav_menu_objects', 'rmp_modify_menu_items', 10, 2);