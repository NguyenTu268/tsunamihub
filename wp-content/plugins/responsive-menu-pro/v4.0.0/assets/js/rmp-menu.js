/**
 * This file contain the scrips for menu frontend.
 * @author ExpressTech System
 *
 * @since 4.0.0
 */

jQuery( document ).ready( function( jQuery ) {

	/**
	 * RmpMenu Class
	 * This RMP class is handling the frontend events and action on menu elements.
	 * @since      4.0.0
	 * @access     public
	 *
	 * @class      RmpMenu
	 */
	class RmpMenu {

		/**
		 * This is constructor function which is initialize the elements and options.
		 * @access public
		 * @since 4.0.0
		 * @param {Array} options List of options.
		 */
		constructor( options ) {
			RmpMenu.activeToggleClass        = 'is-active';
			RmpMenu.openContainerClass       = 'rmp-menu-open';
			RmpMenu.activeSubMenuArrowClass  = 'rmp-menu-subarrow-active';
			RmpMenu.subMenuClass             = '.rmp-submenu';
			RmpMenu.activeTopMenuClass = 'rmp-topmenu-active';

			this.options = options;
			this.menuId  = this.options['menu_id'];
			this.trigger = '#rmp_menu_trigger-' + this.menuId;

			this.isOpen  = false;

			this.container    =  '#rmp-container-' + this.menuId;
			this.headerBar    =  '#rmp-header-bar-' + this.menuId;
			this.menuWrap     =  'ul#rmp-menu-'+ this.menuId;
			this.pageOverlay  =  '#rmp-page-overlay-' + this.menuId;
			this.subMenuArrow = '.rmp-menu-subarrow';
			this.wrapper      = '.rmp-container';
			this.linkElement  = '.rmp-menu-item-link';
			this.pageWrapper  = this.options['page_wrapper'];
			this.use_desktop_menu = this.options['use_desktop_menu'];
			this.originalHeight = '',
			this.animationSpeed        =  this.options['animation_speed'] * 1000;
			this.hamburgerBreakpoint   =  this.options['tablet_breakpoint'];
			this.subMenuTransitionTime =  this.options['sub_menu_speed'] * 1000;
			this.smoothScrollSpeed     = this.options['smooth_scroll_speed'] * 1000;

			if ( this.options['button_click_trigger'].length > 0 ) {
				this.trigger = this.trigger +' , '+ this.options['button_click_trigger'];
			}

			//Append hamburger icon inside an element
			if ( this.options['button_position_type'] == 'inside-element' ) {
				var destination = jQuery(this.trigger).attr('data-destination');
				jQuery(this.trigger).appendTo(jQuery(destination).parent());
			}

			this.init();
		}

		/**
		 * Function to smooth scroll.
		 * @param {Event} event
		 */
		smoothScrollToLocation( event ) {
			if(event.target.hash) {
				jQuery('html, body').animate({
					scrollTop: jQuery(event.target.hash).offset().top
				}, 500);
			}
		}

		/**
		 * This function register the events and initiate the menu settings.
		 */
		init() {
			const self = this;

			// If option is enable from Toggle Button > others >  Toggle menu on click.
			if (  'on' == this.options['button_trigger_type_click'] ) {

				/**
				 * Register click event of trigger.
				 * @fires click
				 */
				jQuery( this.trigger ).on( 'click', function( e ) {
					e.stopPropagation();
					self.triggerMenu();
				} );
			}

			// If option is enable from Toggle Button > others >  Toggle menu on hover.
			if ( 'on' == this.options['button_trigger_type_hover'] ) {

				/**
				 * Register mouseover event of trigger.
				 * @fires mouseover
				 */
				jQuery( this.trigger ).on( 'mouseover', function( e ) {
					e.stopPropagation();
					self.triggerMenu();
				} );
			}

			// Show/Hide sub menu item when click on item toggle.
			jQuery( self.menuWrap ).find( self.subMenuArrow ).on( 'click', function( e ) {
				e.preventDefault();
				e.stopPropagation();
				self.triggerSubArrow( this );
			});

			if ( 'on' == self.options['menu_close_on_body_click'] ) {
				jQuery( document ).on( 'click', 'body', function ( e ) {
					if ( jQuery( window ).width() < self.hamburgerBreakpoint ) {
						if ( self.isOpen ) {
							if ( jQuery( e.target ).closest( self.container ).length || jQuery( e.target ).closest( self.target ).length ) {
								return;
							}
						}
						self.closeMenu();
					}
				});
			}

			if ( 'on'  == self.options['menu_close_on_scroll'] ) {
				jQuery( window ).scroll( function( e ) {
					if ( jQuery( window ).width() < self.hamburgerBreakpoint ) {
						const menuSearchBox = jQuery( '.rmp-search-box' );

						// If focus is on search-box then menu will not be close.
						if ( menuSearchBox && menuSearchBox.is( ':focus' ) ) {
							return;
						} else if ( self.isOpen ) {
							self.closeMenu();
						}
					}
				} );
			}

			jQuery(document).on('keydown', function (event) {
				let menuOpen = jQuery('.rmp-container.rmp-menu-open');
				if (menuOpen.length) {
					let activeMenu = menuOpen.find('.rmp-selected-menu-item').length ? menuOpen.find('.rmp-selected-menu-item') : menuOpen.find('.rmp-menu-current-item');
					let parentContainer = jQuery('.rmp-container.rmp-menu-open');
					let menuItems = menuOpen.find('.rmp-menu-item');

					if (event.keyCode === 9) {
						menuItems.removeClass('rmp-selected-menu-item');
						if (activeMenu.length) {
							if (activeMenu.hasClass('rmp-menu-item-has-children')) {
								activeMenu.children('.rmp-menu-item-link').first().find('.rmp-menu-subarrow').click();
								let firstChild = activeMenu.find('.rmp-submenu').children('.rmp-menu-item').first();
								firstChild.addClass('rmp-selected-menu-item').children('.rmp-menu-item-link').first().focus();
							} else {
								let nextMenu = activeMenu.next('.rmp-menu-item');
								if (nextMenu.length) {
									nextMenu.addClass('rmp-selected-menu-item').children('.rmp-menu-item-link').first().focus();
								} else {
									let parentSubmenu = activeMenu.closest('.rmp-submenu');
									if (parentSubmenu.length) {
										let parentMenu = parentSubmenu.closest('.rmp-menu-item');
										let nextSibling = parentMenu.next('.rmp-menu-item');
										parentMenu.children('.rmp-menu-item-link').first().find('.rmp-menu-subarrow').click();
										if (nextSibling.length) {
											nextSibling.addClass('rmp-selected-menu-item').children('.rmp-menu-item-link').first().focus();
										} else {
											let parentSibling = parentMenu.closest('.rmp-submenu').closest('.rmp-menu-item').next('.rmp-menu-item');
											if (parentSibling.length) {
												parentSibling.find('.rmp-menu-item-link').addClass('rmp-selected-menu-item').children('.rmp-menu-item-link').first().focus();
											} else {
												parentContainer.find('.rmp-menu-item').first().addClass('rmp-selected-menu-item').children('.rmp-menu-item-link').first().focus();
											}
										}
									} else {
										parentContainer.find('.rmp-menu-item').first().addClass('rmp-selected-menu-item').children('.rmp-menu-item-link').first().focus();
									}
								}
							}
						} else {
							menuItems.first().addClass('rmp-selected-menu-item').children('.rmp-menu-item-link').first().focus();
						}
						event.preventDefault();
					}

					if (event.keyCode === 13 && activeMenu.length) {
						activeMenu.click();
					}
				}
			});

			/**
			 * This scripts enable the touch gestures.
			 */
			if ( self.options['enable_touch_gestures'] == 'on' ) {

				// Close menu touch gestures.
				let close_gesture = self.options['menu_appear_from'];

				if ( self.options['menu_appear_from'] == 'top' ) {
					close_gesture = 'up';
				} else if ( self.options['menu_appear_from'] == 'bottom' ) {
					close_gesture = 'down';
				}

				let pageScroll = "horizontal";
				if ( close_gesture == 'left' || close_gesture == 'right' ) {
					pageScroll = "vertical";
				}

				jQuery( self.container ).swipe( {
					swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
						if( jQuery(window).width() < self.hamburgerBreakpoint ) {
							if ( direction == close_gesture ) {
								self.closeMenu();
							}
						}
					},
					threshold: 25,
					allowPageScroll : pageScroll,
					excludedElements: "button, input, select, textarea, a, .noSwipe, .rmp-search-box"
				} );
			}

			/**
			 * Close the menu when click on menu item link before load.
			 */
			if ( self.options['menu_close_on_link_click'] == 'on') {

				jQuery( this.menuWrap +' '+ self.linkElement ).on( 'click', function(e) {

					if( jQuery(window).width() < self.hamburgerBreakpoint ) {
						e.preventDefault();

						// When close menu on parent clicks is on.
						if ( self.options['menu_item_click_to_trigger_submenu'] == 'on' ) {
							if( jQuery(this).is( '.rmp-menu-item-has-children > ' + self.linkElement ) ) {
								return;
							}
						}

						let _href = jQuery(this).attr('href');
						let _target = ( typeof jQuery(this).attr('target') ) == 'undefined' ? '_self' : jQuery(this).attr('target');

						if( self.isOpen ) {
							if( jQuery(e.target).closest(this.subMenuArrow).length) {
								return;
							}
							if( typeof _href != 'undefined' ) {
								self.closeMenu();
								setTimeout(function() {
									window.open( _href, _target);
								}, self.animationSpeed);
							}
						}
					}
				});
			}

			/** Multi screen slide effect */
			if ( self.options['use_slide_effect'] == 'on' ) {
				jQuery( '.rmp-go-back' ).on( 'click', function() {
					self.backUpSlide(this);
				});
			}

			//Show menu on page load section.
			if ( 'on' == self.options['show_menu_on_page_load'] ) {
				if ( jQuery(window).width() < self.hamburgerBreakpoint ) {
					self.openMenu();
				}
			}

			// Keyboard shortcut menu open/close.
			jQuery(document).on( 'keyup', function(e) {
				if ( jQuery(window).width() < self.hamburgerBreakpoint ) {
					let pressedKeyCode = e.keyCode.toString();
					let openKeyCodes   = self.options['keyboard_shortcut_open_menu'];
					let closeKeyCodes  = self.options['keyboard_shortcut_close_menu'];

					if ( jQuery.isArray( openKeyCodes ) &&  jQuery.inArray( pressedKeyCode, openKeyCodes ) !== -1 && ! self.isOpen ) {
						self.openMenu();
					} else if ( jQuery.isArray( closeKeyCodes ) && jQuery.inArray( pressedKeyCode, closeKeyCodes ) !== -1 && self.isOpen ) {
						self.closeMenu();
					}
				}
			});

			// Expand Sub items on Parent Item Click.
			if ( 'on' == self.options['menu_item_click_to_trigger_submenu']  ) {
				jQuery( this.menuWrap +' .rmp-menu-item-has-children > ' + self.linkElement ).on( 'click', function(e) {
					if ( jQuery(window).width() < self.hamburgerBreakpoint ) {
						e.preventDefault();
						self.triggerSubArrow(
							jQuery(this).children( '.rmp-menu-subarrow' ).first()
						);
					}
				});
			}

			//Smooth scrolling enable.
			if ( self.options['smooth_scroll_on'] == 'on' ) {
				jQuery( this.menuWrap +' '+ self.linkElement).on( 'click', function(e) {
					self.smoothScrollToLocation(e);
				});
			}

			//Open submenu on click.
			if ( self.options[ 'desktop_submenu_open_on_click' ] == 'on' ) {

				jQuery( self.menuWrap + ' li.rmp-menu-item-has-children a.rmp-menu-item-link' ).on( 'click', function( e ) {

					if ( jQuery(window).width() > self.hamburgerBreakpoint ) {
						e.stopPropagation();
						e.preventDefault();
						e.stopImmediatePropagation();
						var submenu = jQuery(this).siblings('.rmp-submenu');
						if ( ! submenu.length || jQuery(this).attr( 'data-click') ) {
							window.location = this.href;
						} else {
							jQuery(this).attr( 'data-click', true );
							submenu.addClass( 'rmp-submenu-animate-on' );
							submenu.show();
							return false;
						}
					}

				});
			}

			/* Desktop menu : Hide on scroll down / Show on scroll Up */
			if ( self.options['use_desktop_menu'] == 'on' && self.options['desktop_menu_hide_and_show'] == 'on' ) {
				var lastScrollTop, currentScrollPos = 0;
				var desktop_header = jQuery(self.container);

				if ( self.options['use_header_bar'] == 'on') {
					desktop_header = jQuery(self.headerBar);
				}

				jQuery( window ).scroll( function () {

					if ( jQuery(window).width() > self.hamburgerBreakpoint ) {
						var desktop_header_height = desktop_header.height();
						currentScrollPos = jQuery(window).scrollTop();
						if ( lastScrollTop < currentScrollPos && currentScrollPos > desktop_header_height) {
							desktop_header.fadeOut(100);
							desktop_header.css({
								'transform' : 'translateY( -'+ desktop_header_height +'px )'
							});
						} else {
							desktop_header.fadeIn(100);
							desktop_header.css( { 'transform' : '' } );
						}

						lastScrollTop = currentScrollPos;
					}
				});
			}

			// Change the color of header on scrolling.
			if ( 'on' == self.options['use_header_bar'] && 'on' == self.options['header_bar_scroll_enable'] ) {
				jQuery( window ).scroll( function () {
					const headerBar = jQuery( self.headerBar );
					if ( jQuery(window).scrollTop() > headerBar.height() ) {
						headerBar.addClass('header-scolling-bg-color');
					} else {
						headerBar.removeClass('header-scolling-bg-color');
					}
				});
			}

			// Add rmp-topmenu-active class to current menu item on load
			this.setActiveMenuItemOnLoad();

		}

		// Add rmp-topmenu-active class to current menu item on load
		setActiveMenuItemOnLoad() {
			const currentURL = window.location.href;
			const menuItems = jQuery(this.menuWrap).find('a');

			menuItems.each(function() {
				if (this.href === currentURL) {
					jQuery(this).closest('.rmp-menu-item-has-children').addClass(RmpMenu.activeTopMenuClass);
					return false; // Exit the loop once we've found and marked the current item
				}
			});
		}

		/**
		 * Set push translate for toggle and page wrapper.
		 */
		setWrapperTranslate() {

			let translate,translateContainer;
			switch( this.options['menu_appear_from'] ) {
				case 'left':
					translate = 'translateX(' + this.menuWidth() + 'px)';
					translateContainer = 'translateX(-' + this.menuWidth() + 'px)';
					break;
				case 'right':
					translate = 'translateX(-' + this.menuWidth() + 'px)';
					translateContainer = 'translateX(' + this.menuWidth() + 'px)';
					break;
				case 'top':
					translate = 'translateY(' + this.wrapperHeight() + 'px)';
					translateContainer = 'translateY(-' + this.menuHeight() + 'px)';
					break;
				case 'bottom':
					translate = 'translateY(-' + this.menuHeight() + 'px)';
					translateContainer = 'translateY(' + this.menuHeight() + 'px)';
					break;
			}

			if ( this.options['animation_type'] == 'push' ) {

				jQuery(this.pageWrapper).css( { 'transform':translate } );

				//If push Wrapper has body element then handle menu position.
				if	( 'body' == this.pageWrapper ) {
					jQuery( this.container ).css( { 'transform' : translateContainer } );
				}
			}

			if ( this.options['button_push_with_animation'] == 'on' ) {
				jQuery( this.trigger ).css( { 'transform' : translate } );
			}

		}

		/**
		 * Clear push translate on button and page wrapper.
		 */
		clearWrapperTranslate() {

			if ( this.options['animation_type'] == 'push' ) {
				jQuery(this.pageWrapper).css( { 'transform' : '' } );
			}

			if ( this.options['button_push_with_animation'] == 'on' ) {
				jQuery( this.trigger ).css( { 'transform' : '' } );
			}
		}

		/**
		 * Function to fadeIn the hamburger menu container.
		 */
		fadeMenuIn() {
			jQuery(this.container).fadeIn(this.animationSpeed);
		}

		/**
		 * Function to fadeOut the hamburger menu container.
		 */
		fadeMenuOut() {
			jQuery(this.container)
				.fadeOut(this.animationSpeed, function() {
					jQuery(this).css('display', '');
				});
		}

		/**
		 * Function is use to open the hamburger menu.
		 *
		 * @since 4.0.0
		 */
		openMenu() {
			var self = this;
			jQuery(this.trigger).addClass(RmpMenu.activeToggleClass);
			jQuery(this.container).addClass(RmpMenu.openContainerClass);

			if ( this.options['menu_overlay'] == 'on') {
				jQuery(this.pageOverlay).addClass(RmpMenu.activeToggleClass);
			}

			//this.pushMenuTrigger();

			if ( this.options['animation_type'] == 'fade'){
				this.fadeMenuIn();
			} else {
				this.setWrapperTranslate();
			}

			/**
			 * Fading items from slid.
			 */
			if ( self.options['fade_submenus'] == 'on' ) {

				jQuery( ".rmp-menu > li" ).each( function(index) {
					jQuery(this).show();
					jQuery(this).animate( { opacity: 0 }, 0 );

					if ( self.options['fade_submenus_side'] == 'left' ) {
						jQuery(this).animate( { 'margin-left':'-150px' } ,0 );
					}

					if ( self.options['fade_submenus_side'] == 'right' ) {
						jQuery(this).animate({'margin-left':'150px'},0);
					}

					jQuery( this ).delay( self.options[ 'fade_submenus_delay' ] * index ).animate({
						'margin-left': "0",
						'opacity': 1
					}, self.options['fade_submenus_delay'] );
				});
			}


			if ( self.options['use_slide_effect'] == 'on' ) {
				if( jQuery(window).width() <= self.hamburgerBreakpoint ) {
					jQuery( self.menuWrap ).promise().done( function () {
						self.originalHeight = jQuery( self.menuWrap ).height();
						jQuery( self.menuWrap ).css({'height': self.originalHeight});
					});
				}
			}

			// Disable the background scrolling.
			if ( 'on' == self.options['menu_disable_scrolling'] ) {
				jQuery('body').css('overflow','hidden');
			}

			this.isOpen = true;
		}

		/**
		 * Function is use to close the hamburger menu.
		 *
		 * @since 4.0.0
		 */
		closeMenu() {
			jQuery(this.trigger).removeClass(RmpMenu.activeToggleClass);
			jQuery(this.container).removeClass(RmpMenu.openContainerClass);

			if ( this.options['menu_overlay'] == 'on') {
				jQuery(this.pageOverlay).removeClass(RmpMenu.activeToggleClass);
			}

			if ( this.options['animation_type'] == 'fade') {
				this.fadeMenuOut();
			} else {
				this.clearWrapperTranslate();
			}

			// Release the blocking scrolling.
			if ( 'on' == this.options['menu_disable_scrolling'] ) {
				jQuery('body').css('overflow','auto');
			}

			this.isOpen = false;
		}

		/**
		 * Function is responsible for checking the menu is open or close.
		 *
		 * @since 4.0.0
		 * @param {Event} e
		 */
		triggerMenu() {
			this.isOpen ? this.closeMenu() : this.openMenu();
		}

		triggerSubArrow( subArrow ) {
			var self = this;
			var sub_menu = jQuery( subArrow ).parent().siblings( RmpMenu.subMenuClass );


			// Use sliding effect.
			if ( self.options['use_slide_effect'] == 'on' ) {
				if( jQuery(window).width() <= self.hamburgerBreakpoint ) {
					jQuery(self.container).find( '.rmp-submenu-open').removeClass('rmp-submenu-open');
					sub_menu.addClass('rmp-submenu-open');
					sub_menu.parentsUntil(self.menuWrap).addClass( 'rmp-submenu-open' );
					let current_depth = jQuery(subArrow).parents('.rmp-submenu').data('depth');
					current_depth = typeof current_depth == 'undefined' ? 1 : current_depth;
					let translation_amount = current_depth * 100;
					jQuery(self.menuWrap).css({'transform': 'translateX(-' + translation_amount + '%)'});
					jQuery(self.menuWrap).css({'height': sub_menu.height() + 'px'});
				}
			} else {

				//Accordion animation.
				if ( self.options['accordion_animation'] == 'on' ) {
					// Get Top Most Parent and the siblings.
					var top_siblings   = sub_menu.parents('.rmp-menu-item-has-children').last().siblings('.rmp-menu-item-has-children');
					var first_siblings = sub_menu.parents('.rmp-menu-item-has-children').first().siblings('.rmp-menu-item-has-children');

					// Close up just the top level parents to key the rest as it was.
					top_siblings.children('.rmp-submenu').slideUp(self.subMenuTransitionTime, 'linear').removeClass('rmp-submenu-open');

					// Set each parent arrow to inactive.
					top_siblings.each(function() {
						jQuery(this).find(self.subMenuArrow).first().html(self.options['inactive_toggle_contents']);
						jQuery(this).find(self.subMenuArrow).first().removeClass(RmpMenu.activeSubMenuArrowClass);
					});

					// Now Repeat for the current item siblings.
					first_siblings.children('.rmp-submenu').slideUp(self.subMenuTransitionTime, 'linear').removeClass('rmp-submenu-open');
					first_siblings.each(function() {
						jQuery(this).find(self.subMenuArrow).first().html(self.options['inactive_toggle_contents']);
						jQuery(this).find(self.subMenuArrow).first().removeClass(RmpMenu.activeSubMenuArrowClass);
					});
				}

				// Active sub menu as default behavior.
				if( sub_menu.hasClass('rmp-submenu-open') ) {
					sub_menu.slideUp(self.subMenuTransitionTime, 'linear',function() {
						jQuery(this).css( 'display', '' );
					} ).removeClass('rmp-submenu-open');
					jQuery( subArrow ).html( self.options['inactive_toggle_contents'] );
					jQuery(subArrow).removeClass(RmpMenu.activeSubMenuArrowClass);
				} else {
					sub_menu.slideDown(self.subMenuTransitionTime, 'linear').addClass( 'rmp-submenu-open' );
					jQuery( subArrow ).html(self.options['active_toggle_contents'] );
					jQuery(subArrow).addClass(RmpMenu.activeSubMenuArrowClass);
				}
			}
		}

		/**
		 * Function to add tranform style on trigger.
		 *
		 * @version 4.0.0
		 *
		 * @param {Event} e Event object.
		 */
		pushMenuTrigger( e ) {
			if ( 'on' == this.options['button_push_with_animation'] ) {
				jQuery( this.trigger ).css( { 'transform' : this.menuWidth() } );
			}
		}

		/**
		 * Returns the height of container.
		 *
		 * @version 4.0.0
		 *
		 * @return Number
		 */
		menuHeight() {
			return jQuery( this.container ).height();
		}

		/**
		 * Returns the width of the container.
		 *
		 * @version 4.0.0
		 *
		 * @return Number
		 */
		menuWidth() {
			return jQuery( this.container ).width();
		}

		wrapperHeight() {
			return jQuery( this.wrapper ).height();
		}

		backUpSlide( backButton ) {
			let translateTo = parseInt( jQuery( this.menuWrap )[0].style.transform.replace( /^\D+/g, '' ) ) - 100;
			jQuery( this.menuWrap ).css( { 'transform': 'translateX(-' + translateTo + '%)' } );
			let previousSubmenuHeight = jQuery( backButton ).parent( 'ul' ).parent( 'li' ).parent( '.rmp-submenu' ).height();
			if ( ! previousSubmenuHeight ) {
				jQuery( this.menuWrap ).css( { 'height': this.originalHeight } );
			} else {
				jQuery( this.menuWrap + this.menuId ).css( { 'height': previousSubmenuHeight + 'px' } );
			}
		}
	}

	/**
	 * Create multiple instance of menu and pass the options.
	 *
	 * @version 4.0.0
	 */
	for ( let index = 0; index < rmp_menu.menu.length; index++ ) {
		let rmp = new RmpMenu( rmp_menu.menu[index] );
	}

	// Twenty twenty theme support.
	if ( typeof twentytwenty != 'undefined' ) {
		jQuery( 'header#site-header' ).find('ul.primary-menu').removeClass('primary-menu');
	}

	jQuery('.rmp-mega-menu-container').each( function(){
		var menu_position = jQuery(this).parent().offset();
		var mega_menu_right_position = jQuery('body').width() - menu_position.left;
		var mega_menu_width = jQuery(this).width();
		if( jQuery(window).width() > rmp_menu.menu[0].tablet_breakpoint && mega_menu_width > mega_menu_right_position){
			var mega_menu_position = mega_menu_width - mega_menu_right_position;
			jQuery(this).css('margin-left', '-'+mega_menu_position+'px');
		}
	});

});
