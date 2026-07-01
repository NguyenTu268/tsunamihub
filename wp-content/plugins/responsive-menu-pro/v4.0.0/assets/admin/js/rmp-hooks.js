/**
 * This is js hook scripts file for responsive menu.
 *
 * @file   This files defines the rmpHook object.
 * @author ExpressTech System.
 *
 * @since 4.0.0
 *
 * @package responsive-menu-pro
 */

'use strict';

/**
 * Hooks class.
 *
 * @type  {Object}
 *
 * @since 4.0.0
 */
const rmpHook = {
	hooks: [ ],
	isBreak: false,

	/**
	 * Function to register the hook.
	 *
	 * @since 4.0.0
	 *
	 * @param String   name     Hook Name.
	 * @param function callback Associated function.
	 */
	register: function( name, callback ) {

		if ( 'undefined' == typeof ( rmpHook.hooks[name] ) ) {
			rmpHook.hooks[name] = [ ];
		}

		rmpHook.hooks[name].push( callback );
	},

	/**
	 * Function to call the hook.
	 *
	 * @since 4.0.0
	 *
	 * @param String   name   Hook Name.
	 * @param function params Paramter list.
	 */
	call: function( name, params ) {

		if ( 'undefined' != typeof ( rmpHook.hooks[name] ) ) {
			for ( let i = 0; i < rmpHook.hooks[name].length; ++i ) {
				let output = rmpHook.hooks[name][i]( params );
				if ( false == output ) {
					rmpHook.isBreak = true;
					return false;
				}

				return output;
			}
		}

		return true;
	}
};

export default rmpHook;
