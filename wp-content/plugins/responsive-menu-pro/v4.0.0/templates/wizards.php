<?php
/**
 * This is file contain the new menu creation settings markups.
 *
 * @since      4.0.0
 *
 * @package    responsive_menu_pro
 */

$dashicons = rmp_dashicon_selector();
$glyphicon = rmp_glyphicon_selector();

// If theme list is cached then access it.
$cached_data      = get_transient( 'rmp_theme_api_response' );
$rmp_browse_class = '';
if ( empty( $cached_data ) ) {
    $rmp_browse_class = 'rmp-call-theme-api-button';
}

?>
<section class="rmp-dialog-overlay rmp-menu-icons-dialog" style="display:none">
    <div class="rmp-dialog-backdrop"></div>
    <div class="rmp-dialog-wrap wp-clearfix">
        <div class="rmp-dialog-header">
            <div class="title">
                <img alt="logo" width="34" height="34" src="<?php echo RMP_PLUGIN_URL_V4 .'/assets/images/rmp-logo.png'; ?>" />
                <span> <?php esc_html_e('Select Icon', 'responsive-menu-pro'); ?> </span>
            </div>

            <button class="close dashicons dashicons-no"></button>
        </div>
        <div class="rmp-dialog-contents wp-clearfix">
            <div id="tabs" class="tabs icon-tabs">
                <ul class="nav-tab-wrapper">
                    <li><a class="nav-tab-active nav-tab" href="#dashicons"><?php esc_html_e('Dashicons', 'responsive-menu-pro'); ?></a></li>
                    <li><a class="nav-tab" href="#material-icon"><?php esc_html_e('Material Icons (mdi)', 'responsive-menu-pro'); ?></a></li>
                    <li><a class="nav-tab" href="#fas"><?php esc_html_e('FontAwesome Solid (fas)', 'responsive-menu-pro'); ?></a></li>
                    <li><a class="nav-tab" href="#fab"><?php esc_html_e('FontAwesome Brand (fab)', 'responsive-menu-pro'); ?></a></li>
                    <li><a class="nav-tab" href="#far"><?php esc_html_e('FontAwesome Regular (far)', 'responsive-menu-pro'); ?></a></li>
                    <li><a class="nav-tab" href="#glyphicons"><?php esc_html_e('GlyphIcon', 'responsive-menu-pro'); ?></a></li>
                </ul>
                <div class="rmp-icon-tab-contents">
                    <div>
                        <input type="text" class="medium-text" id="rmp-icon-search" placeholder="Search icons"/>
                    </div>
                    <div id="dashicons">
                       <?php echo $dashicons; ?>
                    </div>
                    <div id="fab">
                        <?php echo rmp_fab_selector(); ?>
                    </div>
                    <div id="fas">
                        <?php echo rmp_fas_selector(); ?>
                    </div>
                    <div id="glyphicons">
                        <?php echo $glyphicon; ?>
                    </div>
                    <div id="material-icon">
                        <?php echo rmp_mdi_selector(); ?>
                    </div>
                    <div id="far">
                        <div><?php echo rmp_far_selector(); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="rmp-dialog-footer">
            <a class="button button-secondary button-large" id="rmp-icon-dialog-clear"><?php esc_html_e('Clear', 'responsive-menu-pro'); ?></a>
            <a class="button button-primary button-large" id="rmp-icon-dialog-select"><?php esc_html_e('Select', 'responsive-menu-pro'); ?></a>
        </div>
    </div>
</section>

<!-- //This save template as theme wizard. -->
<section id="rmp-menu-save-theme-wizard" class="rmp-dialog-overlay" style="display:none">
    <div class="rmp-dialog-backdrop"></div>
    <div class="rmp-dialog-wrap wp-clearfix">
        <span class="close dashicons dashicons-no"></span>
        <div class="rmp-dialog-contents wp-clearfix">
            <span class="rmp-menu-library-blank-icon  fas fa-save"></span>
            <h3 class="rmp-menu-library-title"><?php esc_html_e('Save menu options as theme template', 'responsive-menu-pro'); ?></h3>
            <p class="rmp-menu-library-message"><?php esc_html_e('Your designs will be available for export and reuse on any menu or website.', 'responsive-menu-pro'); ?></p>
            <div class="rmp-save-menu-input">
                <input type="text" id="rmp-save-theme-name" name="rmp_theme_name" placeholder="Enter Template Name"/>
                <button type="button" class="button save-button" id="rmp-save-theme"><?php esc_html_e('Save Theme', 'responsive-menu-pro'); ?></button>
            </div>
        </div>
    </div>
</section>

<!-- //This save changes wizard. -->
<section id="rmp-menu-save-changes-wizard" class="rmp-dialog-overlay" style="display:none">
    <div class="rmp-dialog-backdrop"></div>
    <div class="rmp-dialog-wrap wp-clearfix">
        <div class="rmp-dialog-header">
            <div class="title">
                <span class="icon fas fa-save"></span>
                <span><?php esc_html_e('Save changes', 'responsive-menu-pro'); ?></span>
            </div>
            <button class="close dashicons dashicons-no"></button>
        </div>
        <div class="rmp-dialog-contents wp-clearfix">
            <p class="rmp-menu-library-message"><?php esc_html_e('You are about to change the device mode. We have detected some unsaved changes in your current device mode.', 'responsive-menu-pro'); ?></p>
            <p class="rmp-menu-library-message"><?php esc_html_e('Do you want to save these changes before proceeding?', 'responsive-menu-pro'); ?></p>
            <div class="rmp-save-changes-btn-group">
                <button type="button" class="button button-secondary button-large close" id="rmp-cancel-changes-btn"><?php esc_html_e('Cancel', 'responsive-menu-pro'); ?></button>
                <button type="button" class="button rmp-menu-button-danger button-large" id="rmp-discard-changes-btn"><?php esc_html_e('No', 'responsive-menu-pro'); ?></button>
                <button type="button" class="button button-primary button-large" id="rmp-save-changes-btn"><?php esc_html_e('Yes', 'responsive-menu-pro'); ?></button>
            </div>
        </div>
    </div>
</section>

<!-- Theme wizard in customizer page. -->
<section id="rmp-new-menu-wizard" class="rmp-dialog-overlay rmp-new-menu-wizard" style="display:none">
    <div class="rmp-dialog-backdrop"></div>
    <div class="rmp-dialog-wrap wp-clearfix">
        <div class="rmp-dialog-header">
            <div class="title">
                <img alt="logo" width="34" height="34" src="<?php echo RMP_PLUGIN_URL_V4 .'/assets/images/rmp-logo.png'; ?>" />
                <span> <?php esc_html_e('Use Theme', 'responsive-menu-pro'); ?> </span>
            </div>

            <button class="close dashicons dashicons-no"></button>
        </div>
        <div class="rmp-dialog-contents wp-clearfix tabs" id="tabs" >
            <div id="select-themes" class="rmp-new-menu-themes">
                <div id="tabs" class="tabs">
                    <ul class="nav-tab-wrapper">
                        <li><a class="nav-tab rmp-v-divider" href="#tabs-1"><?php esc_html_e( 'Installed Themes', 'responsive-menu-pro'); ?></a></li>
                        <li><a class="nav-tab rmp-v-divider <?php echo $rmp_browse_class; ?>" href="#tabs-2"><?php esc_html_e( 'Marketplace', 'responsive-menu-pro'); ?></a></li>
                        <li><a class="nav-tab" href="#tabs-3"><?php esc_html_e('Saved Templates', 'responsive-menu-pro'); ?></a></li>
                        <li style="float:right;"><button id="rmp-upload-new-theme" class="button btn-import-theme"><?php esc_html_e('Import', 'responsive-menu-pro'); ?></button></li>
                    </ul>

                     <!-- This is menu theme upload section -->
                     <div id="rmp-menu-library-import" class="rmp-theme-upload-container hide" >
                        <p><?php esc_html_e('If you have a menu theme in a .zip format, you can upload here.', 'responsive-menu-pro'); ?></p>
                        <form method="post" enctype="multipart/form-data" id="rmp-menu-theme-upload-form" class="wp-upload-form">
                            <label class="screen-reader-text" for="themezip">Upload zip</label>
                            <input type="file" accept=".zip" id="rmp_menu_theme_zip" name="rmp_menu_theme_zip" />
                            <button id="rmp-theme-upload" class="button" type="button"> Upload Theme </button>
                        </form>
                    </div>

                    <div id="tabs-2" class="rmp-themes">
                        <ul class="rmp_theme_grids">
                            <?php
                                if ( ! empty( $cached_data ) ) {
                                    echo $theme_manager->get_themes_from_theme_store( true );
                                } else {
                            ?>
                            <div class="rmp-page-loader">
                                <img class="rmp-loader-image" src="<?php echo RMP_PLUGIN_URL_V4 .'/assets/images/rmp-logo.png'; ?>"/>
                                <h3 class="rmp-loader-message">
                                    <?php _e( 'Just a moment <br/> Getting data from the server..', 'responsive-menu-pro' ); ?>
                                </h3>
                            </div>
                            <?php } ?>
                        </ul>
                    </div>

                    <div id="tabs-1" class="rmp-themes">
                        <?php echo $theme_manager->get_available_themes( true ); ?>
                    </div>

                    <div id="tabs-3" class="rmp-themes">
                        <?php echo $theme_manager->rmp_saves_theme_template_list( true ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
