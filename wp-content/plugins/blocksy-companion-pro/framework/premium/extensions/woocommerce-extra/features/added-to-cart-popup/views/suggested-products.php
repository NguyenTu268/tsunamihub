<?php

$products_type = blocksy_get_theme_mod('added_to_cart_popup_products_type', 'inline');
$products_source = blocksy_get_theme_mod('added_to_cart_popup_products_source', 'related');
$number_of_items = blocksy_get_theme_mod('added_to_cart_popup_products_number_of_items', 6);

$products_content = '';

if (! $display_descriptor['suggested_products']) {
	return;
}

$product_ids = [];

if ($product->get_type() === 'variation') {
	$product_id = $product->get_parent_id();
}

if ($products_source === 'related') {
	$product_ids = wc_get_related_products($product_id, $number_of_items);
} else if ($products_source === 'recent') {
	$product_ids = \Blocksy\RecentlyViewedProducts::get_recently_viewed_products();
} else if ($products_source === 'upsell') {
	$product_ids = $product->get_upsell_ids();
	
	if ($product->get_type() === 'variation') {
		$parent = wc_get_product($product->get_parent_id());
		$product_ids = $parent->get_upsell_ids();
	}

} else if ($products_source === 'cross_sell') {
	$product_ids = $product->get_cross_sell_ids();
}

$product_ids = array_filter(
	$product_ids,
	function ($_product_id) use ($product_id) {
		return $product_id !== $_product_id;
	}
);

$product_ids = array_slice($product_ids, 0, $number_of_items);

if (empty($product_ids)) {
	return;
}

$products_section_title = '';

if ($products_source === 'related') {
	$products_section_title = __('Related Products', 'blocksy-companion');
} else if ($products_source === 'recent') {
	$products_section_title = __('Recently Viewed', 'blocksy-companion');
}

$arrows = '<span class="ct-slider-arrows">
	<span class="ct-arrow-prev">
	<svg width="8" height="8" fill="currentColor" viewBox="0 0 8 8">
	<path d="M5.05555,8L1.05555,4,5.05555,0l.58667,1.12-2.88,2.88,2.88,2.88-.58667,1.12Z"></path>
	</svg>
	</span>

	<span class="ct-arrow-next">
	<svg width="8" height="8" fill="currentColor" viewBox="0 0 8 8">
	<path d="M2.35778,6.88l2.88-2.88L2.35778,1.12,2.94445,0l4,4-4,4-.58667-1.12Z"></path>
	</svg>
	</span>
	</span>';

$products_section_title = blocksy_html_tag(
	'h6',
	[],
	$products_section_title .
	$arrows
);

$products_loop = array_reduce(
	$product_ids,
	function ($html, $product_id) use ($products_type, $is_cutomize_preview) {
		$product = wc_get_product($product_id);

		if (! $product) {
			return $html;
		}

		$html_atts = [
			'href' => apply_filters(
				'woocommerce_loop_product_link',
				get_permalink($product->get_id()),
				$product
			),
			'aria-label' => strip_tags($product->get_name()),
		];

		if (
			blocksy_get_theme_mod('woo_archive_affiliate_image_link', 'no') === 'yes'
			&&
			$product->get_type() === 'external'
		) {
			$open_in_new_tab = blocksy_get_theme_mod(
				'woo_archive_affiliate_image_link_new_tab',
				'no'
			) === 'yes' ? '_blank' : '_self';

			$html_atts['href'] = $product->get_product_url();
			$html_atts['target'] = $open_in_new_tab;
		}

		$gallery_images = blocksy_product_get_gallery_images($product);

		if ($product->get_type() === 'variation') {
			$variation_main_image = $product->get_image_id();

			if ($variation_main_image) {
				if (! in_array($variation_main_image, $gallery_images)) {
					$gallery_images[0] = $variation_main_image;
				}

				$gallery_images = array_merge(
					[$variation_main_image],
					array_diff($gallery_images, [$variation_main_image])
				);
			}
		}

		$image_size = 'thumbnail';
		$image_ratio = '1/1';

		if ($products_type === 'block') {
			$image_size = 'medium';
			$image_ratio = '1/1';

			$default_product_layout = blocksy_get_woo_archive_layout_defaults();

			$render_layout_config = blocksy_get_theme_mod(
				'woo_card_layout',
				$default_product_layout
			);

			$render_layout_config = blocksy_normalize_layout(
				$render_layout_config,
				$default_product_layout
			);

			$attr = [];

			foreach ($render_layout_config as $layout) {
				if (! $layout['enabled'] ) {
					continue;
				}

				if ($layout['id'] === 'product_image') {
					$attr = $layout;
					break;
				}
			}

			$image_ratio = apply_filters(
				'blocksy:woocommerce:product-card:thumbnail:ratio',
				blocksy_get_woocommerce_ratio([
					'key' => 'archive_thumbnail',
					'cropping' => blocksy_akg(
						'blocksy_woocommerce_archive_thumbnail_cropping',
						$attr,
						'predefined'
					)
				]),
				$product->get_id()
			);

			$image_size = 'woocommerce_archive_thumbnail';
		}

		if ($is_cutomize_preview) {
			$image_size = 'woocommerce_archive_thumbnail';
		}

		$product_image = blocksy_media([
			'no_image_type' => 'woo',
			'attachment_id' => $gallery_images[0],
			'post_id' => $product->get_id(),
			'size' => $image_size,
			'ratio' => $image_ratio,
			'tag_name' => 'a',
			'html_atts' => $html_atts,
			'lazyload' => false
		]);

		$product_title = blocksy_html_tag(
			'h2',
			[
				'class' => 'woocommerce-loop-product__title',
			],
			blocksy_html_tag(
				'a',
				[
					'href' => $product->get_permalink(),
				],
				$product->get_title()
			)
		);

		$product_price = blocksy_html_tag(
			'span',
			[
				'class' => 'price',
			],
			$product->get_price_html()
		);

		$html .= blocksy_html_tag(
			'div',
			[
				'class' => 'flexy-item',
			],
			$product_image .
			blocksy_html_tag(
				'section',
				[],
				$product_title .
				$product_price
			)
		);

		return $html;
	},
	''
);

$products_loop = blocksy_html_tag(
	'div',
	array_merge(
		[
			'class' => 'flexy-container',
			'data-flexy' => 'no',
		],
		blocksy_get_theme_mod('added_to_cart_popup_products_autoplay', 'yes') === 'yes' ? [
			'data-autoplay' => blocksy_get_theme_mod('added_to_cart_popup_products_autoplay_speed', 2)
		] : []
	),
	blocksy_html_tag(
		'div',
		[
			'class' => 'flexy',
		],
		blocksy_html_tag(
			'div',
			[
				'class' => 'flexy-view',
				'data-flexy-view' => 'boxed',
			],
			blocksy_html_tag(
				'div',
				[
					'data-products' => $products_type,
					'class' => 'flexy-items',
				],
				$products_loop
			)
		)
	)
);

do_action('blocksy:ext:woocommerce-extra:added-to-cart:suggested_products:before');

echo blocksy_html_tag(
	'div',
	[
		'class' => 'ct-suggested-products' . ' ' . blocksy_visibility_classes(
			blocksy_get_theme_mod('suggested_products_visibility', [
				'desktop' => true,
				'tablet' => true,
				'mobile' => false,
			])
		),
	],
	$products_section_title . $products_loop
);

do_action('blocksy:ext:woocommerce-extra:added-to-cart:suggested_products:after');
