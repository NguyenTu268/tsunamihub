<?php

namespace Blocksy\Extensions\WoocommerceExtra;

class AddedToCartPopup {
	private $cart_info = [];

	public function __construct() {
		add_action('wp', function () {
			$added_to_cart_popup_show_recent_products = blocksy_get_theme_mod(
				'added_to_cart_popup_show_recent_products',
				'yes'
			);

			$added_to_cart_popup_products_source = blocksy_get_theme_mod(
				'added_to_cart_popup_products_source',
				'related'
			);

			if (
				$added_to_cart_popup_show_recent_products !== 'yes'
				||
				$added_to_cart_popup_products_source !== 'recent'
			) {
				return;
			}

			\Blocksy\Plugin::instance()
				->premium
				->recently_viewed_products
				->start_tracking();
		});

		add_action(
			'woocommerce_add_to_cart',
			function($cart_id, $product_id, $request_quantity, $variation_id, $variation, $cart_item_data ) {
				$this->cart_info = [
					'cart_id' => $cart_id,
					'product_id' => $product_id,
					'request_quantity' => $request_quantity,
					'variation_id' => $variation_id,
					'variation' => $variation,
					'cart_item_data' => $cart_item_data,
				];
			},
			10, 6
		);

		add_filter('woocommerce_add_to_cart_fragments', function($fragments) {
			if (empty($this->cart_info)) {
				return $fragments;
			}

			$content = blocksy_render_view(
				dirname(__FILE__) . '/view.php',
				[
					'cart_info' => $this->cart_info
				]
			);

			$fragments['__SKIP__blocksy-added-to-cart-popup'] = $content;

			return $fragments;
		});

		add_action(
			'wp_enqueue_scripts',
			function () {
				if (! function_exists('get_plugin_data')) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				$plugin_data = get_plugin_data(BLOCKSY__FILE__);

				wp_enqueue_style(
					'blocksy-pro-popup-styles',
					BLOCKSY_URL . 'framework/premium/static/bundle/popups.min.css',
					['ct-main-styles'],
					$plugin_data['Version']
				);
			}
		);

		add_filter(
			'blocksy_customizer_options:woocommerce:general:end',
			function ($opts) {
				$opts['has_added_to_cart_panel'] = blocksy_get_options(
					dirname(__FILE__) . '/customizer-options.php',
					[],
					false
				);

				return $opts;
			},
			55
		);

        add_filter('blocksy:general:ct-scripts-localizations', function ($data) {
			if (!function_exists('get_plugin_data')) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugin_data = get_plugin_data(BLOCKSY__FILE__);

			$data['dynamic_styles']['added_to_cart_popup'] = add_query_arg(
				'ver',
				$plugin_data['Version'],
				blocksy_cdn_url(
					BLOCKSY_URL . 'framework/premium/extensions/woocommerce-extra/static/bundle/added-to-cart-popup.min.css'
				)
			);

			return $data;
		});

		add_filter('blocksy:frontend:dynamic-js-chunks', function ($chunks) {
			$chunks[] = [
				'id' => 'blocksy_ext_woo_extra_added_to_cart_popup',
				'url' => blocksy_cdn_url(
					BLOCKSY_URL .
					'framework/premium/extensions/woocommerce-extra/static/bundle/added-to-cart-popup.js'
				),
				'trigger' => [
					'trigger' => 'jquery-event',
					'matchTarget' => false,
					'events' => [
						'added_to_cart'
					]
				],
				'selector' => 'body',
				'settings' => [
					'template' => blocksy_get_theme_mod(
						'added_to_cart_popup_trigger',
						[
							'archive' => true,
							'single' => true,
						]
					),
					'visibility' => blocksy_get_theme_mod(
						'added_to_cart_popup_visibility',
						[
							'desktop' => true,
							'tablet' => true,
							'mobile' => true,
						]
					)
				]
			];

			return $chunks;
		});
	}
}
