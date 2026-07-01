<?php

$options = [
	'label' => __('Added to Cart Popup', 'blocksy-companion'),
	'type' => 'ct-panel',
	'setting' => ['transport' => 'postMessage'],
	'inner-options' => [

		blocksy_rand_md5() => [
			'title' => __( 'General', 'blocksy-companion' ),
			'type' => 'tab',
			'options' => [

				'added_to_cart_popup_trigger' => [
					'label' => __( 'Trigger Popup On', 'blocksy-companion' ),
					'type' => 'ct-checkboxes',
					'design' => 'block',
					'view' => 'text',
					'allow_empty' => true,
					'value' => [
						'archive' => true,
						'single' => true,
					],
					'choices' => blocksy_ordered_keys([
						'archive' => __( 'Archive Page', 'blocksy-companion' ),
						'single' => __( 'Product Page', 'blocksy-companion' ),
					]),
				],

				'added_to_cart_popup_show_image' => [
					'label' => __('Image', 'blocksy-companion'),
					'type' => 'ct-switch',
					'value' => 'yes',
					'view' => 'text',
					'design' => 'inline',
					'divider' => 'top:full',
					'setting' => [ 'transport' => 'postMessage' ],
				],

				blocksy_rand_md5() => [
					'type' => 'ct-condition',
					'condition' => [ 'added_to_cart_popup_show_image' => 'yes' ],
					'options' => [

						'added_to_cart_popup_image_ratio' => [
							'label' => __('Image Ratio', 'blocksy-companion'),
							'type' => 'ct-ratio',
							'view' => 'inline',
							'value' => '3/4',
							// 'divider' => 'bottom:full',
							'sync' => 'live'
						],

						'added_to_cart_popup_image_width' => [
							'label' => __( 'Image Width', 'blocksy-companion' ),
							'type' => 'ct-slider',
							'value' => 20,
							'min' => 10,
							'max' => 50,
							'defaultUnit' => '%',
							'divider' => 'bottom',
							'setting' => [ 'transport' => 'postMessage' ],
						],

					],
				],

				'added_to_cart_popup_show_price' => [
					'label' => __('Price', 'blocksy-companion'),
					'type' => 'ct-switch',
					'value' => 'yes',
					'view' => 'text',
					'design' => 'inline',
					'setting' => [ 'transport' => 'postMessage' ],
				],

				'added_to_cart_popup_show_description' => [
					'label' => __('Description', 'blocksy-companion'),
					'type' => 'ct-switch',
					'value' => 'no',
					'view' => 'text',
					'design' => 'inline',
					'setting' => [ 'transport' => 'postMessage' ],
				],

				blocksy_rand_md5() => [
					'type' => 'ct-condition',
					'condition' => [ 'added_to_cart_popup_show_description' => 'yes' ],
					'options' => [
						'added_to_cart_popup_description_length' => [
							'label' => __('Description Length', 'blocksy-companion'),
							'type' => 'ct-number',
							'design' => 'inline',
							'value' => 20,
							'min' => 1,
							'max' => 300,
						],
					]
				],

				'added_to_cart_popup_show_cart' => [
					'label' => __('Cart Button', 'blocksy-companion'),
					'type' => 'ct-switch',
					'value' => 'yes',
					'view' => 'text',
					'design' => 'inline',
					'divider' => 'top',
					'setting' => [ 'transport' => 'postMessage' ],
				],

				'added_to_cart_popup_show_checkout' => [
					'label' => __('Checkout Button', 'blocksy-companion'),
					'type' => 'ct-switch',
					'value' => 'no',
					'view' => 'text',
					'design' => 'inline',
					'setting' => [ 'transport' => 'postMessage' ],
				],

				'added_to_cart_popup_show_continue' => [
					'label' => __('Continue Shopping Button', 'blocksy-companion'),
					'type' => 'ct-switch',
					'value' => 'yes',
					'view' => 'text',
					'design' => 'inline',
					'setting' => [ 'transport' => 'postMessage' ],
				],

				'added_to_cart_popup_show_attributes' => [
					'label' => __('Attributes', 'blocksy-companion'),
					'type' => 'ct-switch',
					'value' => 'yes',
					'view' => 'text',
					'design' => 'inline',
					'divider' => 'top',
					'setting' => [ 'transport' => 'postMessage' ],
				],

				'added_to_cart_popup_show_shipping' => [
					'label' => __('Shipping Info', 'blocksy-companion'),
					'type' => 'ct-switch',
					'value' => 'yes',
					'view' => 'text',
					'design' => 'inline',
					'setting' => [ 'transport' => 'postMessage' ],
				],

				'added_to_cart_popup_show_tax' => [
					'label' => __('Tax Info', 'blocksy-companion'),
					'type' => 'ct-switch',
					'value' => 'no',
					'view' => 'text',
					'design' => 'inline',
					'setting' => [ 'transport' => 'postMessage' ],
				],

				'added_to_cart_popup_show_total' => [
					'label' => __('Total Info', 'blocksy-companion'),
					'type' => 'ct-switch',
					'value' => 'yes',
					'view' => 'text',
					'design' => 'inline',
					'setting' => [ 'transport' => 'postMessage' ],
				],
			],
		],

		blocksy_rand_md5() => [
			'title' => __( 'Design', 'blocksy-companion' ),
			'type' => 'tab',
			'options' => [

				'added_to_cart_popup_title_font' => [
					'type' => 'ct-typography',
					'label' => __( 'Title Font', 'blocksy-companion' ),
					'value' => blocksy_typography_default_values([
						'size' => '16px',
					]),
					'setting' => [ 'transport' => 'postMessage' ],
				],

				'added_to_cart_popup_title_color' => [
					'label' => __( 'Title Color', 'blocksy-companion' ),
					'type'  => 'ct-color-picker',
					'design' => 'inline',
					'divider' => 'bottom',
					'setting' => [ 'transport' => 'postMessage' ],
					'value' => [
						'default' => [
							'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
						],
					],

					'pickers' => [
						[
							'title' => __( 'Initial', 'blocksy-companion' ),
							'id' => 'default',
							'inherit' => 'var(--theme-heading-2-color, var(--theme-headings-color))'
						],
					],
				],

				blocksy_rand_md5() => [
					'type' => 'ct-condition',
					'condition' => [ 'added_to_cart_popup_show_price' => 'yes' ],
					'options' => [

						'added_to_cart_popup_price_font' => [
							'type' => 'ct-typography',
							'label' => __( 'Price Font', 'blocksy-companion' ),
							'value' => blocksy_typography_default_values([
								'size' => '15px',
								'variation' => 'n7',
							]),
							'setting' => [ 'transport' => 'postMessage' ],
						],

						'added_to_cart_popup_price_color' => [
							'label' => __( 'Price Color', 'blocksy-companion' ),
							'type'  => 'ct-color-picker',
							'design' => 'inline',
							'divider' => 'bottom',
							'setting' => [ 'transport' => 'postMessage' ],
							'value' => [
								'default' => [
									'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
								],
							],

							'pickers' => [
								[
									'title' => __( 'Initial', 'blocksy-companion' ),
									'id' => 'default',
									'inherit' => 'var(--theme-text-color)'
								],
							],
						],

					],
				],

				blocksy_rand_md5() => [
					'type' => 'ct-condition',
					'condition' => [ 'added_to_cart_popup_show_image' => 'yes' ],
					'options' => [

						'added_to_cart_popup_image_radius' => [
							'label' => __( 'Image Border Radius', 'blocksy-companion' ),
							'type' => 'ct-spacing',
							'value' => blocksy_spacing_value(),
							'inputAttr' => [
								'placeholder' => '3'
							],
							'sync' => 'live',
						],

					],
				],

			],
		],

		'added_to_cart_popup_suggested_products' => [
			'label' => __('Suggested Products', 'blocksy-companion'),
			'type' => 'ct-switch',
			'value' => 'yes',
			'view' => 'text',
			'design' => 'inline',
			'divider' => 'top:full',
			'sync' => 'live',
		],

		blocksy_rand_md5() => [
			'type' => 'ct-condition',
			'condition' => [ 'added_to_cart_popup_suggested_products' => 'yes' ],
			'options' => [

				blocksy_rand_md5() => [
					'title' => __( 'General', 'blocksy-companion' ),
					'type' => 'tab',
					'options' => [

						'added_to_cart_popup_products_source' => [
							'label' => __('Source', 'blocksy-companion'),
							'type' => 'ct-select',
							'value' => 'related',
							'design' => 'block',
							'divider' => 'bottom',
							'choices' => blocksy_ordered_keys(

								[
									'related' => __('Related Products', 'blocksy-companion'),
									'recent' => __('Recently Viewed Products', 'blocksy-companion'),
									'upsell' => __('Upsell Products', 'blocksy-companion'),
									'cross_sell' => __('Cross-sell Products', 'blocksy-companion'),
								]

							),
						],

						blocksy_rand_md5() => [
							'type' => 'ct-group',
							'label' => __('Columns & Products', 'blocksy-companion'),
							'attr' => ['data-columns' => '2:medium'],
							'responsive' => true,
							'hasGroupRevertButton' => true,
							'options' => [

								'added_to_cart_popup_products_columns' => [
									'label' => false,
									'desc' => __('Number of columns', 'blocksy-companion'),
									'type' => 'ct-number',
									'value' => [
										'desktop' => 2,
										'tablet' => 2,
										'mobile' => 1,
										'__changed' => ['tablet', 'mobile']
									],
									'min' => 1,
									'max' => 6,
									'design' => 'block',
									'attr' => ['data-width' => 'full'],
									'responsive' => true,
									'skipResponsiveControls' => true,
									'sync' => 'live',
								],

								'added_to_cart_popup_products_number_of_items' => [
									'label' => false,
									'desc' => __('Number of products', 'blocksy-companion'),
									'type' => 'ct-number',
									'value' => 6,
									'min' => 1,
									'max' => 15,
									'design' => 'block',
									'attr' => ['data-width' => 'full'],
									'markAsAutoFor' => ['tablet', 'mobile'],
								],
							],
						],

						'added_to_cart_popup_products_autoplay' => [
							'type' => 'ct-switch',
							'label' => __('Autoplay', 'blocksy-companion'),
							'value' => 'yes',
							'divider' => 'top',
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [
								'added_to_cart_popup_products_autoplay' => 'yes',
							],
							'options' => [
								'added_to_cart_popup_products_autoplay_speed' => [
									'label' => __('Delay (Seconds)', 'blocksy-companion'),
									'desc' => __('Specify the amount of time (in seconds) to delay between automatically cycling an item.', 'blocksy-companion'),
									'type' => 'ct-number',
									'value' => 2,
									'min' => 1,
									'max' => 10,
									'design' => 'inline',
								],
							],
						],

						'added_to_cart_popup_products_type' => [
							'label' => __('Products Card Type', 'blocksy-companion'),
							'type' => 'ct-radio',
							'value' => 'inline',
							'view' => 'text',
							'design' => 'block',
							'divider' => 'top',
							'choices' => [
								'inline' => __('Type 1', 'blocksy-companion'),
								'block' => __('Type 2', 'blocksy-companion'),
							],
							'sync' => 'live',
						],

						'suggested_products_visibility' => [
							'label' => __('Products Visibility', 'blocksy-companion'),
							'type' => 'ct-visibility',
							'design' => 'block',
							'divider' => 'top',
							'setting' => ['transport' => 'postMessage'],
							'allow_empty' => true,

							'value' => blocksy_default_responsive_value([
								'desktop' => true,
								'tablet' => true,
								'mobile' => false,
							]),

							'choices' => blocksy_ordered_keys([
								'desktop' => __( 'Desktop', 'blocksy-companion' ),
								'tablet' => __( 'Tablet', 'blocksy-companion' ),
								'mobile' => __( 'Mobile', 'blocksy-companion' ),
							]),
						],

					],
				],

				blocksy_rand_md5() => [
					'title' => __( 'Design', 'blocksy-companion' ),
					'type' => 'tab',
					'options' => [

						'suggested_products_title_font' => [
							'type' => 'ct-typography',
							'label' => __( 'Title Font', 'blocksy-companion' ),
							'value' => blocksy_typography_default_values([
								'size' => '14px',
							]),
							'setting' => [ 'transport' => 'postMessage' ],
						],

						'suggested_products_title_color' => [
							'label' => __( 'Title Color', 'blocksy-companion' ),
							'type'  => 'ct-color-picker',
							'design' => 'inline',
							'divider' => 'bottom',
							'setting' => [ 'transport' => 'postMessage' ],
							'value' => [
								'default' => [
									'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
								],
							],

							'pickers' => [
								[
									'title' => __( 'Initial', 'blocksy-companion' ),
									'id' => 'default',
									'inherit' => 'var(--theme-heading-2-color, var(--theme-headings-color))'
								],
							],
						],

						'suggested_products_price_font' => [
							'type' => 'ct-typography',
							'label' => __( 'Price Font', 'blocksy-companion' ),
							'value' => blocksy_typography_default_values([
								'size' => '14px',
							]),
							'setting' => [ 'transport' => 'postMessage' ],
						],

						'suggested_products_price_color' => [
							'label' => __( 'Price Color', 'blocksy-companion' ),
							'type'  => 'ct-color-picker',
							'design' => 'inline',
							'divider' => 'bottom',
							'setting' => [ 'transport' => 'postMessage' ],
							'value' => [
								'default' => [
									'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
								],
							],

							'pickers' => [
								[
									'title' => __( 'Initial', 'blocksy-companion' ),
									'id' => 'default',
									'inherit' => 'var(--theme-text-color)'
								],
							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'added_to_cart_popup_products_type' => 'inline' ],
							'options' => [

								'suggested_products_image_radius_type_inline' => [
									'label' => __( 'Image Border Radius', 'blocksy-companion' ),
									'type' => 'ct-spacing',
									'value' => blocksy_spacing_value(),
									'inputAttr' => [
										'placeholder' => '100'
									],
									'sync' => 'live',
								],

							],
						],

						blocksy_rand_md5() => [
							'type' => 'ct-condition',
							'condition' => [ 'added_to_cart_popup_products_type' => 'block' ],
							'options' => [

								'suggested_products_image_radius_type_block' => [
									'label' => __( 'Image Border Radius', 'blocksy-companion' ),
									'type' => 'ct-spacing',
									'value' => blocksy_spacing_value([
										'top' => '3px',
										'left' => '3px',
										'right' => '3px',
										'bottom' => '3px',
									]),
									'sync' => 'live',
								],

							],
						],

					],
				],

			],
		],

		blocksy_rand_md5() => [
			'type' => 'ct-title',
			'label' => __( 'Popup Options', 'blocksy-companion' ),
		],

		blocksy_rand_md5() => [
			'title' => __( 'General', 'blocksy-companion' ),
			'type' => 'tab',
			'options' => [

				'added_to_cart_popup_open_animation' => [
					'label' => __('Popup Animation', 'blocksy-companion' ),
					'type' => 'ct-select',
					'value' => 'slide-right',
					'design' => 'block',
					'divider' => 'top:full',
					'choices' => blocksy_ordered_keys([
						'fade-in' => __('Fade in fade out', 'blocksy-companion'),
						'zoom-in' => __('Zoom in zoom out', 'blocksy-companion'),
						'slide-left' => __('Slide in from left', 'blocksy-companion'),
						'slide-right' => __('Slide in from right', 'blocksy-companion'),
						'slide-top' => __('Slide in from top', 'blocksy-companion'),
						'slide-bottom' => __('Slide in from bottom', 'blocksy-companion'),
					]),
					'sync' => 'live'
				],

				'added_to_cart_popup_entrance_speed' => [
					'label' => __( 'Animation Speed', 'blocksy-companion' ),
					'type' => 'ct-number',
					'design' => 'inline',
					'value' => 0.2,
					'min' => 0,
					'max' => 10,
					'step' => 0.1,
					'sync' => 'live'
				],

				blocksy_rand_md5() => [
					'type' => 'ct-condition',
					'condition' => [
						'added_to_cart_popup_open_animation' => 'slide-left|slide-right|slide-top|slide-bottom',
					],
					'options' => [

						'added_to_cart_popup_entrance_value' => [
							'label' => __( 'Entrance Value', 'blocksy-companion' ),
							'type' => 'ct-number',
							'design' => 'inline',
							'value' => 50,
							'min' => 0,
							'max' => 500,
							'sync' => 'live'
						],

					],
				],

				'added_to_cart_popup_size' => [
					'label' => __('Popup Size', 'blocksy-companion' ),
					'type' => 'ct-select',
					'value' => 'medium',
					'design' => 'block',
					'divider' => 'top:full',
					'sync' => 'live',
					'choices' => blocksy_ordered_keys([
						'small' => __('Small Size', 'blocksy-companion'),
						'medium' => __('Medium Size', 'blocksy-companion'),
						'large' => __('Large Size', 'blocksy-companion'),
						'custom' => __('Custom Size', 'blocksy-companion'),
					]),
				],

				blocksy_rand_md5() => [
					'type' => 'ct-condition',
					'condition' => ['added_to_cart_popup_size' => 'custom'],
					'options' => [

						'added_to_cart_popup_max_width' => [
							'label' => __( 'Max Width', 'blocksy-companion' ),
							'type' => 'ct-slider',
							'value' => '900px',
							'design' => 'block',
							'units' => [
								[ 'unit' => 'px','min' => 0, 'max' => 1500 ],
								[ 'unit' => 'vw', 'min' => 0, 'max' => 100 ],
								[ 'unit' => 'vh', 'min' => 0, 'max' => 100 ],
								[ 'unit' => 'em', 'min' => 0, 'max' => 100 ],
								[ 'unit' => 'rem', 'min' => 0, 'max' => 100 ],
							],
							'responsive' => true,
							'sync' => 'live'
						],

						'added_to_cart_popup_max_height' => [
							'label' => __( 'Max Height', 'blocksy-companion' ),
							'type' => 'ct-slider',
							'value' => '700px',
							'design' => 'block',
							'units' => [
								[ 'unit' => 'px','min' => 0, 'max' => 1500 ],
								[ 'unit' => 'vw', 'min' => 0, 'max' => 100 ],
								[ 'unit' => 'vh', 'min' => 0, 'max' => 100 ],
								[ 'unit' => 'em', 'min' => 0, 'max' => 100 ],
								[ 'unit' => 'rem', 'min' => 0, 'max' => 100 ],
							],
							'responsive' => true,
							'sync' => 'live'
						],

					]
				],

				'added_to_cart_popup_position' => [
					'label' => __('Popup Position', 'blocksy-companion' ),
					'type' => 'blocksy-position',
					'value' => 'bottom:right',
					'design' => 'block',
					'sync' => 'live',
					'divider' => 'top',
				],

				'added_to_cart_popup_visibility' => [
					'label' => __('Popup Visibility', 'blocksy-companion'),
					'type' => 'ct-visibility',
					'design' => 'block',
					'divider' => 'top',
					'setting' => ['transport' => 'postMessage'],
					'allow_empty' => true,

					'value' => blocksy_default_responsive_value([
						'desktop' => true,
						'tablet' => true,
						'mobile' => true,
					]),

					'choices' => blocksy_ordered_keys([
						'desktop' => __( 'Desktop', 'blocksy-companion' ),
						'tablet' => __( 'Tablet', 'blocksy-companion' ),
						'mobile' => __( 'Mobile', 'blocksy-companion' ),
					]),
				],

			],
		],

		blocksy_rand_md5() => [
			'title' => __( 'Design', 'blocksy-companion' ),
			'type' => 'tab',
			'options' => [

				'added_to_cart_popup_edges_offset' => [
					'label' => __( 'Popup Offset', 'blocksy-companion' ),
					'type' => 'ct-slider',
					'min' => 0,
					'max' => 300,
					'value' => 25,
					'responsive' => true,
					'setting' => [ 'transport' => 'postMessage' ],
				],

				'added_to_cart_popup_padding' => [
					'label' => __( 'Padding', 'blocksy-companion' ),
					'type' => 'ct-spacing',
					'divider' => 'top',
					'value' => blocksy_spacing_value(),
					'inputAttr' => [
						'placeholder' => '30'
					],
					'responsive' => true,
					'setting' => [ 'transport' => 'postMessage' ],
				],

				'added_to_cart_popup_border_radius' => [
					'label' => __( 'Border Radius', 'blocksy-companion' ),
					'sync' => 'live',
					'type' => 'ct-spacing',
					'divider' => 'top',
					'value' => blocksy_spacing_value(),
					'inputAttr' => [
						'placeholder' => '7'
					],
					'responsive' => true,
					'setting' => [ 'transport' => 'postMessage' ],
				],

				'added_to_cart_popup_shadow' => [
					'label' => __( 'Shadow', 'blocksy-companion' ),
					'type' => 'ct-box-shadow',
					'divider' => 'top',
					'responsive' => true,
					'setting' => [ 'transport' => 'postMessage' ],
					'value' => blocksy_box_shadow_value([
						'enable' => true,
						'h_offset' => 0,
						'v_offset' => 10,
						'blur' => 20,
						'spread' => 0,
						'inset' => false,
						'color' => [
							'color' => 'rgba(41, 51, 61, 0.1)',
						],
					])
				],

				'added_to_cart_popup_background' => [
					'label' => __( 'Popup Background', 'blocksy-companion' ),
					'type'  => 'ct-background',
					'design' => 'block:right',
					'divider' => 'top',
					'responsive' => true,
					'setting' => [ 'transport' => 'postMessage' ],
					'value' => blocksy_background_default_value([
						'backgroundColor' => [
							'default' => [
								'color' => 'var(--theme-palette-color-8)'
							],
						],
					])
				],

				'added_to_cart_popup_backdrop_background' => [
					'label' => __( 'Popup Backdrop', 'blocksy-companion' ),
					'type'  => 'ct-background',
					'design' => 'block:right',
					'divider' => 'top',
					'responsive' => true,
					'setting' => [ 'transport' => 'postMessage' ],
					'value' => blocksy_background_default_value([
						'backgroundColor' => [
							'default' => [
								'color' => 'rgba(18, 21, 25, 0.5)'
							],
						],
					])
				],

				'added_to_cart_popup_backdrop_background' => [
					'label' => __( 'Popup Backdrop', 'blocksy-companion' ),
					'type'  => 'ct-background',
					'design' => 'block:right',
					'divider' => 'top',
					'responsive' => true,
					'setting' => [ 'transport' => 'postMessage' ],
					'value' => blocksy_background_default_value([
						'backgroundColor' => [
							'default' => [
								'color' => 'rgba(18, 21, 25, 0.8)'
							],
						],
					])
				],

				'added_to_cart_popup_close_button_icon_size' => [
					'label' => __( 'Close Icon Size', 'blocksy-companion' ),
					'type' => 'ct-number',
					'design' => 'inline',
					'value' => 12,
					'min' => 5,
					'max' => 50,
					'divider' => 'top',
					'setting' => [ 'transport' => 'postMessage' ],
				],

				'added_to_cart_popup_close_button_color' => [
					'label' => __( 'Close Icon Color', 'blocksy-companion' ),
					'type'  => 'ct-color-picker',
					'design' => 'block',
					'divider' => 'top',
					'responsive' => true,
					'setting' => [ 'transport' => 'postMessage' ],
					'value' => [
						'default' => [
							'color' => 'rgba(0, 0, 0, 0.5)',
						],

						'hover' => [
							'color' => 'rgba(0, 0, 0, 0.8)',
						],
					],

					'pickers' => [
						[
							'title' => __( 'Initial', 'blocksy-companion' ),
							'id' => 'default',
						],

						[
							'title' => __( 'Hover', 'blocksy-companion' ),
							'id' => 'hover',
						],
					],
				],

			],
		],

	],
];
