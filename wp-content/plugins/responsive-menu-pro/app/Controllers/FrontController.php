<?php

namespace ResponsiveMenuPro\Controllers;
use ResponsiveMenuPro\Collections\OptionsCollection;
use ResponsiveMenuPro\View\View;
use ResponsiveMenuPro\Management\OptionManager;
use ResponsiveMenuPro\Formatters\Minifier;

/**
* Entry point for all front end functionality.
*
* All routing for the front end comes through the functions below. When a
* front end page is loaded in the browser it will come through here.
*
* @author Peter Featherstone <peter@featherstone.me>
*
* @since 3.0
*/
class FrontController {

    /**
    * Constructor for setting up the FrontController.
    *
    * The constructor allows us to switch implementations for managing options
    * and for rendering views. Useful for switching out mocked or stubbed
    * classes during testing.
    *
    * @author Peter Featherstone <peter@featherstone.me>
    *
    * @since 3.0
    *
    * @param OptionManager  $manager    Instance of a Management options class.
    * @param View           $view       Instance of a View class for rendering.
    */
    public function __construct(OptionManager $manager, View $view) {
        $this->manager = $manager;
        $this->view = $view;
    }

    /**
    * Main route for the front end.
    *
    * This is the default view for the plugin on an initial GET request to the
    * front end page.
    *
    * @author Peter Featherstone <peter@featherstone.me>
    *
    * @since 3.0
    *
    * @return string    Output HTML from rendered view.
    */
    public function index() {
        $options = $this->manager->all();
        $this->buildFrontEnd($options);
    }

    /**
    * Preview route for the front end.
    *
    * This is the preview view for the plugin when the preview admin option is
    * pressed. We turn external files off to enable the preview options to
    * take effect.
    *
    * @author Peter Featherstone <peter@featherstone.me>
    *
    * @since 3.0
    *
    * @param array  $options    An array of the preview options.
    *
    * @return string            Output HTML from rendered view.
    */
    public function preview($options) {
        $options['external_files'] = 'off';
        $collection = $this->manager->buildFromArray($options);
        $this->buildFrontEnd($collection);
    }

    /**
    * Helper private method to setup and build the front end.
    *
    * This is the preview view for the plugin when the preview admin option is
    * pressed. We turn external files off to enable the preview options to
    * take effect.
    *
    * TODO: This is a horrible method that really needs to be broken up in some
    * way. There is a lot of setup and WordPress specific functionality going
    * on here that would ideally be abstracted away.
    *
    * @author Peter Featherstone <peter@featherstone.me>
    *
    * @since 3.0
    *
    * @param OptionsCollection  $options    A OptionsCollection object.
    */
    private function buildFrontEnd(OptionsCollection $options) {
        if($options['hide_on_desktop'] == 'on' && !wp_is_mobile())
            return;

        if($options['hide_on_mobile'] == 'on' && wp_is_mobile())
            return;

        if(isset($options['excluded_pages']) && $options['excluded_pages'] && in_array(get_queried_object_id(), json_decode($options['excluded_pages'])))
            return;

        $font_icons = $options->usesFontIcons();

        add_action('wp_enqueue_scripts', function() use ($font_icons, $options) {

            if($font_icons):
                if ((in_array('font-awesome', $font_icons) || in_array('font-awesome-brand', $font_icons)) && !$options['remove_fontawesome'])
                    wp_enqueue_style('responsive-menu-pro-font-awesome', 'https://use.fontawesome.com/releases/v5.2.0/css/all.css', null, null);

                if (in_array('glyphicon', $font_icons) && !$options['remove_bootstrap']):
                    wp_enqueue_script('responsive-menu-pro-bootstrap-js', plugin_dir_url(dirname(dirname(__FILE__))) . 'public/js/admin/bootstrap.js', null, null);
                    wp_enqueue_style('responsive-menu-pro-bootstrap-css', plugin_dir_url(dirname(dirname(__FILE__))) . 'public/css/admin/bootstrap.css', null, null);
                endif;
            endif;

            if($options['enable_touch_gestures'])
                wp_enqueue_script('responsive-menu-pro-jquery-touchswipe', plugin_dir_url(dirname(dirname(__FILE__))) . 'public/js/jquery.touchSwipe.min.js', null, null);

            if($options['menu_disable_scrolling'])
                wp_enqueue_script('responsive-menu-pro-noscroll', plugin_dir_url(dirname(dirname(__FILE__))) . 'public/js/noscroll.js', null, null);

        });

        add_filter('body_class', function($classes) use($options) {
            $classes[] = 'responsive-menu-pro-' . $options['animation_type'] . '-' . $options['menu_appear_from'];
            return $classes;
        });

        if($options['show_menu_on_page_load']):
            add_filter('language_attributes', function($output) {
                return $output . ' class="responsive-menu-pro-open"';
            });
        endif;

        if($options['external_files'] == 'on'):
            add_action('wp_enqueue_scripts', function() use($options) {
                $css_file = wp_upload_dir()['baseurl'] . '/responsive-menu-pro/css/responsive-menu-pro-' . get_current_blog_id() . '.css';
                $js_file = wp_upload_dir()['baseurl'] . '/responsive-menu-pro/js/responsive-menu-pro-' . get_current_blog_id() . '.js';
                wp_enqueue_style('responsive-menu-pro', $css_file, null, get_option('rmp_dynamic_file_version', wp_rand( 100, 1000 ) ) );
                wp_enqueue_script('responsive-menu-pro', $js_file, ['jquery'], get_option('rmp_dynamic_file_version', wp_rand( 100, 1000 ) ), $options['scripts_in_footer'] == 'on' ? true : false);
            });
        else:
            add_action('wp_head', function() use($options)  {
                $css_data = $this->view->render('css/app.css.twig', ['options' => $options]);
                if($options['minify_scripts'] == 'on')
                    $css_data = Minifier::minify($css_data);

                echo '<style>' . $css_data . '</style>';
            }, 100);

            add_action($options['scripts_in_footer'] == 'on' ? 'wp_footer' : 'wp_head', function() use($options) {
                $js_data = $this->view->render('js/app.js.twig', ['options' => $options]);
                if($options['minify_scripts'] == 'on')
                    $js_data = Minifier::minify($js_data);

                echo '<script>' . $js_data . '</script>';
            }, 100);
        endif;

        if($options['shortcode'] == 'on'):
            add_shortcode('responsive_menu_pro', function($atts) use($options) {
                if(is_array($atts))
                    $merged_options = array_merge($options->toArray(), $atts);
                else
                    $merged_options = $options->toArray();

                $new_collection = new OptionsCollection($merged_options);
                $html = '';
                if($options['use_header_bar'] == 'on'):
                    $html .= $this->view->render('app/header-bar.html.twig', ['options' => $new_collection]);
                endif;
                $html .= $this->view->render('app.html.twig', ['options' => $new_collection]);
                return $html;
            });
        else:
            add_action('wp_footer', function() use($options) {
                if($options['use_header_bar'] == 'on'):
                    echo $this->view->render('app/header-bar.html.twig', ['options' => $options]);
                endif;
                echo $this->view->render('app.html.twig', ['options' => $options]);
            });
        endif;
    }

}
