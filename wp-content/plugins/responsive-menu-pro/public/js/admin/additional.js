/**
    Responsive Menu JS file.
    NOT Safe to Copy

    Do Not Copy
**/

jQuery(function($) {

    /* --> Colour Select Options */
        $.minicolors.defaults = $.extend(
            $.minicolors.defaults, {
                format: 'rgb',
                opacity: true,
                theme: 'bootstrap'
            }
        );
        $('.mini-colours').minicolors();
    /* <-- End Colour Select Options */

    /* --> Preview Options */
        $(document).on('click', '#responsive-menu-preview', function(e) {
            e.preventDefault();
            var form = $('#responsive-menu-pro-form');
            form.attr('action', WP_RMP.HOME_URL + '?responsive-menu-pro-preview=true');
            form.attr('target', '_blank');
            form.submit();
            form.attr('action', '');
            form.attr('target', '');
        });
    /* <-- End Preview Options */

    /* --> Font Icons */
        $(document).on('click', '.delete-font-icon-row', function() {
            $(this).closest('tr').remove();
        });

        $(document).on('click', '#add-font-icon', function() {
            var lastRow = $('#font-icon-container tr').last();
            var nextRow = lastRow.clone();
            nextRow.find(':text').val('');
            lastRow.after(nextRow);
            /* Solution: https://github.com/silviomoreto/bootstrap-select/issues/605#issuecomment-186148737 */
            nextRow.find('.bootstrap-select').replaceWith(function() { return $('select', this); });
            nextRow.find('select').selectpicker();
        });
    /* <-- End Font Iconts */

    /* --> Header Bar Scripts */
        $('#header-bar-sortable').sortable({
            revert: true,
            placeholder: 'header-dashed-placeholder'
        });
    /* <-- End Header Bar Scripts */

    /* --> Desktop Menu Scripts */
        $('.responsive-menu-desktop-menu-label').on('click', function(e) {
            $('.responsive-menu-desktop-menu-options-container').hide();
            $('.responsive-menu-desktop-menu-widget-container').hide();
            $('#responsive-menu-desktop-menu-option-' + $(this).data('id')).show();
            $('#responsive-menu-desktop-menu-widget-' + $(this).data('id')).show();
            $('.responsive-menu-desktop-menu-label').removeClass('responsive-menu-desktop-menu-label-active');
            $(this).addClass('responsive-menu-desktop-menu-label-active');
        });

        $(document).on('click', '.responsive-menu-desktop-menu-options-container .responsive-menu-desktop-menu-widget .responsive-menu-desktop-menu-widget-header', function(e) {
            $(this).siblings('.responsive-menu-desktop-menu-widget-body').slideToggle(100);
        });

        $(document).on('click', '.responsive-menu-desktop-menu-widget .responsive-menu-desktop-menu-widget-header .glyphicon', function() {
            $(this).closest('.responsive-menu-desktop-menu-widget').remove();
        });

        $(document).on('changed.bs.select', '.desktop-menu-type-switcher', function(event, clickedIndex) {
            $mega_menu_background =
                $(this)
                    .parents('.responsive-menu-desktop-menu-option-container')
                    .siblings('.responsive-menu-desktop-menu-mega-menu-option');

            if(clickedIndex == 1) {
                $mega_menu_background.removeClass('hidden');
            } else {
                $mega_menu_background.addClass('hidden');
            }
        });

        /** Hide and show fixed header scroll option */
        desktop_menu_position = $('#responsive-menu-desktop-menu-positioning');
        var option_row = desktop_menu_position.parents('tr').next();
        if ( 'fixed' === desktop_menu_position.val() ) {
            option_row.show();
        } else {
            option_row.hide();
        }
        /** Hide and show on position change */
        desktop_menu_position.on( 'change', function(){
            var option_row = $(this).parents('tr').next();
            if ( 'fixed' === $(this).val() ) {
                option_row.show();
            } else {
                option_row.hide();
            }
        });

        /** Show and Hide the Mega Menu depth notice. */
        $('select.desktop-menu-type-switcher').on('change', function() {
            if( $(this).val() == 'mega' ) {
                $(this).parents('.responsive-menu-desktop-menu-option-container').append('<p style="color:red;margin-top:10px;" class="mega-menu-depth-notice sub-text">Note : The Menu > Depth option will not apply for Mega Menu items.</p>');
            } else {
                var notice  = $(this).parents('.responsive-menu-desktop-menu-option-container').children('.mega-menu-depth-notice');
                if( notice.length ) {
                    notice.remove();
                }
            }
        });

        /** Call ajax to hide admin notice permanent. */
        $( '.rmp-license-upgrade-notice' ).on( 'click', '.notice-dismiss', function( event ) {
            event.preventDefault();
            jQuery.ajax( {
                type: "POST",
                url:  ajaxurl,
                data: "action=responsive_menu_license_admin_notice_dismiss",
            });
        });

        /** Call ajax to hide admin notice permanent. */
        $( '.rmp-version-upgrade-notice' ).on( 'click', '.notice-dismiss', function( event ) {
            event.preventDefault();
            jQuery.ajax( {
                type: "POST",
                url:  ajaxurl,
                data: "action=rmp_version_admin_notice_dismiss",
            });
        });

         /** Move to new version */
        jQuery( '.rmp-upgrade-version' ).on( 'click', function(e) {
            e.preventDefault();
            jQuery.ajax({
                url: ajaxurl,
                data: { action : 'rmp_switch_version' },
                type: 'POST',
                dataType: 'json',
                error: function( error ) {
                    jQuery(this).prop('disabled', false);
                },
                success: function( response ) {
                    if ( response.data.redirect ) {
                        location.href = response.data.redirect;    
                    }
                }
            });
        });

    /* <-- End Desktop Menu Scripts */

});