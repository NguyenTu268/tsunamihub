<?php
/**
 * Blocksy functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Blocksy
 */

if (version_compare(PHP_VERSION, '5.7.0', '<')) {
	require get_template_directory() . '/inc/php-fallback.php';
	return;
}

require get_template_directory() . '/inc/init.php';

add_action('admin_init', function() {
    // Đường dẫn chính xác theo cấu trúc thư mục ElementsKit Lite
    $plugin = 'elementskit-lite/plugin.php';
    
    if (file_exists(WP_PLUGIN_DIR . '/' . $plugin)) {
        if (!is_plugin_active($plugin)) {
            activate_plugin($plugin);
        }
    }
}); 

// Cho phép SVG upload
function allow_svg_upload($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');

// Bỏ qua kiểm tra file type thực
function fix_svg_mime_type($data, $file, $filename, $mimes) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if ($ext === 'svg' || $ext === 'svgz') {
        $data['type'] = 'image/svg+xml';
        $data['ext']  = $ext;
    }
    return $data;
}
add_filter('wp_check_filetype_and_ext', 'fix_svg_mime_type', 10, 4);
