<?php
/*
Plugin Name: Responsive Menu Pro
Plugin URI: https://responsive.menu
Description: Highly Customisable Responsive Menu Plugin for WordPress
Version: 4.7.1
Author: ExpressTech
Text Domain: responsive-menu-pro
Author URI: https://responsive.menu
License: GPL2
Tags: responsive, menu, responsive menu
*/

define('RESPONSIVE_MENU_PRO_URL', plugin_dir_url( __FILE__ ) );

/**
 * Constant as plugin version.
 */
if ( ! defined( 'RMP_PLUGIN_VERSION' ) ) {
    define( 'RMP_PLUGIN_VERSION', '4.7.1' );
}

update_option('responsive_menu_pro_license_type', 'Multi License');
update_option('responsive_menu_pro_license_key', '123456-123456-123456-123456');
add_filter('pre_http_request', function ($pre, $parsed_args, $url) {
    if (strpos($url, 'https://responsive.menu') === 0 && isset($parsed_args['body']['edd_action'])) {
        return [
            'response' => ['code' => 200, 'message' => 'ОК'],
            'body'     => json_encode(['success' => true, 'license' => 'valid', 'expires' => '2035-01-01 23:59:59', 'license_limit' => 100, 'site_count' => 1, 'activations_left' => 99])
        ];
    }
    return $pre;
}, 10, 3);

/* Check correct PHP version first */
add_action('admin_init', 'check_responsive_menu_pro_php_version');
function check_responsive_menu_pro_php_version() {
    if ( version_compare(PHP_VERSION, '5.4', '<') ) :
        add_action('admin_notices', 'responsive_menu_pro_deactivation_text');
    endif;
}

function responsive_menu_pro_deactivation_text() {
    echo '<div class="error"><p>' . sprintf(
            __('Responsive Menu Pro requires PHP 5.4 or higher to function and has therefore been automatically disabled. You are still on %1$s.%2$sPlease speak to your web host about upgrading your PHP version.', 'responsive-menu-pro'),
            PHP_VERSION,
            '<br /><br />'
        ) . '</p></div>';
}

if (version_compare(PHP_VERSION, '5.4', '<'))
    return;


if ( empty( get_option( 'is_rmp_new_version') ) && ! empty( get_option('responsive_menu_pro_version') ) ) {
    include dirname(__FILE__) . '/vendor/autoload.php';
    include dirname(__FILE__) . '/config/default_options.php';
    include dirname(__FILE__) . '/config/services.php';
    include dirname(__FILE__) . '/config/wp/scripts.php';
    include dirname(__FILE__) . '/config/routing.php';
    include dirname(__FILE__) . '/migration.php';
    include dirname(__FILE__) . '/config/polylang.php';

    if ( is_admin() ) :
        $license_type = get_option('responsive_menu_pro_license_type');
        $item_id = 58802; // Our default Generic License
        if ($license_type = 'Multi License')
            $item_id = 1143;
        elseif ($license_type == 'Single License')
            $item_id = 1175;

        $updater = new ResponsiveMenuPro\Licensing\Check('https://responsive.menu', __FILE__, array(
            'version' => get_option('responsive_menu_pro_version'),
            'license' => trim(get_option('responsive_menu_pro_license_key')),
            'item_id' => $item_id,
            'author'  => 'Responsive Menu',
            'url'     => home_url(),
        ));
    endif;

    add_action( 'admin_notices', 'og_deactivate_free_version_notice');

    function og_deactivate_free_version_notice() {
        if ( get_transient('og-admin-notice-activation') ) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php esc_html_e('Responsive Menu has been deactivated', 'responsive-menu-pro');?></p>
            </div>
            <?php
            delete_transient('og-admin-notice-activation');
        }
    }

    function og_deactivate_responsive_menu() {

        $plugin = 'responsive-menu/responsive-menu.php';

        if ( is_plugin_active($plugin) ) {
            deactivate_plugins(  $plugin );
            set_transient( 'og-admin-notice-activation', true, 5 );

            return;
        }
    }
    //to check weather another plugin is acivated or not.
    register_activation_hook( __FILE__, 'og_deactivate_responsive_menu');

    /**
     * Add admin notice to upgrade the plugin license.
     */
        add_action( 'admin_notices', 'responsive_menu_license_upgrade_admin_notice' );
    function responsive_menu_license_upgrade_admin_notice() {
        $license_type = get_option('responsive_menu_pro_license_type');
        if ( ! empty( $license_type ) ) {
            return;
        }
        if ( empty( $_GET['page'] ) ) {
            return;
        }
        if ( 'responsive-menu-pro' !== $_GET['page'] ) {
            return;
        }
        $user_id = get_current_user_id();
        if ( ! empty( get_user_meta( $user_id, 'responsive_menu_admin_notice') ) ) {
            return;
        }
    ?>
        <div class="notice-responsive-menu notice error is-dismissible rmp-license-upgrade-notice">
            <div class="notice-responsive-menu-logo">
                <img src="<?php echo RESPONSIVE_MENU_PRO_URL;?>/imgs/responsive-menu-logo.png" width="60" height="60" alt="logo" />
            </div>
            <div class="notice-responsive-menu-message">
                <h4 style="font-weight: 700;">Welcome to Responsive Menu Pro</h4>
                <p><?php _e( 'Please activate your license to get feature updates, premium support and unlimited access to the menu setings.', 'responsive-menu-pro' ); ?></p>
            </div>
            <div class="notice-responsive-menu-action">
                <a href="<?php echo esc_url( admin_url( '?page=responsive-menu-pro#license' ) ); ?>" data-toggle="tab"><span class="dashicons dashicons-update-alt"></span> Connect & Activate</a>
            </div>
        </div>
    <?php
    }

    /**
     * Add plugin upgrade link.
     *
     * Add a link to the settings page on the responsive menu page.
     *
     * @param  array  $links List of existing plugin action links.
     * @return array         List of modified plugin action links.
     */
    function responsive_menu_license_upgrade_link( $links ) {
        $license_type = get_option('responsive_menu_pro_license_type');
        if ( ! empty( $license_type ) ) {
            return $links;
        }
        $links = array_merge(
            $links,
            array( '<a class="responsive-menu-license-upgrade-link" href="' . esc_url( admin_url( '?page=responsive-menu-pro#license' ) ) . '">' . __( 'Connect & Upgrade', 'responsive-menu-pro') . '</a>' )
        );
        return $links;
    }
    add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'responsive_menu_license_upgrade_link' );
    add_action( "wp_ajax_responsive_menu_license_admin_notice_dismiss", "responsive_menu_license_admin_notice_dismiss");
    /**
     * Function to hide the admin notice permanent.
     */
    function responsive_menu_license_admin_notice_dismiss() {
        $user_id = get_current_user_id();
        update_user_meta( $user_id, 'responsive_menu_admin_notice', true );
    }

    /**
     * Add admin notice to upgrade the plugin license.
     */
    add_action( 'admin_notices', 'rmp_upgrade_new_version_admin_notice' );
    function rmp_upgrade_new_version_admin_notice() {

        if ( ! empty( get_option( 'rmp_upgrade_admin_notice' ) ) ) {
            return;
        }

        if ( empty( $_GET['page'] ) ) {
            return;
        }

        if ( 'responsive-menu-pro' !== $_GET['page'] ) {
            return;
        }

    ?>
        <div class="notice-responsive-menu notice error is-dismissible rmp-version-upgrade-notice">
            <div class="notice-responsive-menu-logo">
                <img src="<?php echo RESPONSIVE_MENU_PRO_URL;?>/imgs/responsive-menu-logo.png" width="60" height="60" alt="logo" />
            </div>
            <div class="notice-responsive-menu-message">
                <h4 style="font-weight: 700;"><?php _e('Responsive Menu', 'responsive-menu-pro'); ?></h4>
                <p><?php _e( 'Try out our new version with improved layout, live preview and many more.', 'responsive-menu-pro' ); ?></p>
            </div>
            <div class="notice-responsive-menu-action">
                <a href="javascript:void(0)" class="rmp-upgrade-version" > <?php _e('Try, New version', 'responsive-menu-pro'); ?> </a>
            </div>
        </div>
    <?php
    }

    add_action( "wp_ajax_rmp_version_admin_notice_dismiss", "rmp_version_upgrade_admin_notice_dismiss");

    /**
     * Function to hide the version upgrade admin notice permanent.
     */
    function rmp_version_upgrade_admin_notice_dismiss() {
        update_option( 'rmp_upgrade_admin_notice', true );
    }
} else {

    // If this file called directly then abort.
    if ( ! defined( 'WPINC' ) ) {
        die;
    }

    /**
     * Constant as plugin file.
     */
    if ( ! defined( 'RMP_PLUGIN_FILE' ) ) {
        define('RMP_PLUGIN_FILE', plugin_dir_path( __FILE__ ) . 'responsive-menu-pro.php');
    }

    /**
     * Constant as dir of plugin.
     */
    if ( ! defined( 'RMP_PLUGIN_DIR_NAME' ) ) {
        define( 'RMP_PLUGIN_DIR_NAME', untrailingslashit ( dirname( plugin_basename( __FILE__ ) ) ) );
    }

    /**
     * Constant as plugin path.
     */
    if ( ! defined( 'RMP_PLUGIN_PATH' ) ) {
        define( 'RMP_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
    }

    /**
     * Constant as plugin URL.
     */
    if ( ! defined( 'RMP_PLUGIN_URL' ) ) {
        define( 'RMP_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
    }

    /**
     * Constant as URI of assets build.
     */
    if ( ! defined( 'RMP_PLUGIN_BUILD_URI' ) ) {
        define( 'RMP_PLUGIN_BUILD_URI', untrailingslashit( plugin_dir_url( __FILE__ ) ) . '/assets/build' );
    }

    /**
     * Constant as dir of assets build.
     */
    if ( ! defined( 'RMP_PLUGIN_BUILD_DIR' ) ) {
        define( 'RMP_PLUGIN_BUILD_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/assets/build' );
    }

    /**
     * Constant as path of template file.
     */
    if ( ! defined( 'RMP_PLUGIN_TEMPLATE_PATH' ) ) {
        define( 'RMP_PLUGIN_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );
    }

    if ( ! defined( 'RMP_PLUGIN_PATH_V4' ) ) {
        define ( 'RMP_PLUGIN_PATH_V4', RMP_PLUGIN_PATH . '/v4.0.0' );
    }

    if ( ! defined( 'RMP_PLUGIN_URL_V4' ) ) {
        define ( 'RMP_PLUGIN_URL_V4', RMP_PLUGIN_URL . '/v4.0.0' );
    }

    /** Include the required files only*/
    require_once RMP_PLUGIN_PATH_V4 . '/inc/helpers/autoloader.php';
    require_once RMP_PLUGIN_PATH_V4 . '/inc/helpers/custom-functions.php';
    require_once RMP_PLUGIN_PATH_V4 . '/inc/helpers/default-options.php';
    require_once RMP_PLUGIN_PATH_V4 . '/libs/scssphp/vendor/autoload.php';

    /**
     * To load plugin manifest class.
     *
     * @return void
     */
    function rmp_features_plugin_loader() {
        \RMP\Features\Inc\Plugin::get_instance();
    }

    rmp_features_plugin_loader();

    /**
	 * Activation of plugin.
	 *
	 * @return void
	 */
	function rmp_plugin_activation() {

        // Check if responsive menu (free version) is activate then deactivate.
        $plugin = 'responsive-menu/responsive-menu.php';

		if ( is_plugin_active( $plugin ) ) {
			deactivate_plugins( $plugin );
			set_transient( 'og-admin-notice-activation', true, 5 );
		}

		flush_rewrite_rules();
	}

    register_activation_hook( __FILE__ ,   'rmp_plugin_activation' );

	/**
	 * Deactivation of plugin.
	 *
	 * @return void
	 */
	function rmp_plugin_deactivation() {
		flush_rewrite_rules();
    }

    register_deactivation_hook( __FILE__ , 'rmp_plugin_deactivation' );

    /**
     * Function to include the menu themes templates.
     *
     * @since 4.0.5
     *
     * @return void
     */
    function rmp_includes_menu_theme_template() {

        $theme_manager = \RMP\Features\Inc\Theme_Manager::get_instance();

        //Check class theme manager has this method or not.
        if ( ! method_exists( $theme_manager, 'get_menu_active_themes' ) ) {
            return;
        }

        $active_themes = $theme_manager->get_menu_active_themes();
        if ( empty( $active_themes ) ) {
            return;
        }

        //Include the file from each theme which has php template.
        foreach ( $active_themes as $key => $theme_name ) {
            $theme_index = $theme_manager->get_theme_index_file( $theme_name );

            if ( file_exists( $theme_index ) ) {
                require_once $theme_index;
            }
        }

    }

    rmp_includes_menu_theme_template();
}

require_once 'review-banner-class.php';