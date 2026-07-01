<?php

if (! function_exists('is_woocommerce')) {
	return;
}

$storage = new \Blocksy\Extensions\WoocommerceExtra\Storage();


// Floating bar
$position = blocksy_expand_responsive_value(blocksy_get_theme_mod('floating_bar_position', 'top'));

$visibility = blocksy_expand_responsive_value(
	blocksy_get_theme_mod(
		'floatingBarVisibility',
		[
			'desktop' => true,
			'tablet' => true,
			'mobile' => true,
		]
	)
);

$floating_bar_height = [
	'desktop' => '70',
	'tablet' => '70',
	'mobile' => '70'
];

if (! $visibility['desktop']) {
	$floating_bar_height['desktop'] = '0';
}

if (! $visibility['tablet']) {
	$floating_bar_height['tablet'] = '0';
}

if (! $visibility['mobile']) {
	$floating_bar_height['mobile'] = '0';
}

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-drawer-canvas[data-floating-bar]',
	'variableName' => 'floating-bar-height',
	'value' => $floating_bar_height
]);

if (
	$position['desktop'] !== 'top'
	||
	$position['tablet'] !== 'top'
	||
	$position['mobile'] !== 'top'
) {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-floating-bar',
		'variableName' => 'top-position-override',
		'value' => [
			'desktop' => $position['desktop'] === 'top' ? 'var(--top-position)' : 'var(--false)',
			'tablet' => $position['tablet'] === 'top' ? 'var(--top-position)' : 'var(--false)',
			'mobile' => $position['mobile'] === 'top' ? 'var(--top-position)' : 'var(--false)'
		],
		'unit' => '',
	]);

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-floating-bar',
		'variableName' => 'translate-offset',
		'value' => [
			'desktop' => $position['desktop'] === 'top' ? '-70px' : '70px',
			'tablet' => $position['tablet'] === 'top' ? '-70px' : '70px',
			'mobile' => $position['mobile'] === 'top' ? '-70px' : '70px'
		],
		'unit' => '',
	]);
}

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('floatingBarFontColor'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '.ct-floating-bar .product-title, .ct-floating-bar .price',
			'variable' => 'theme-text-color'
		],
	],
]);

blocksy_output_background_css([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'selector' => '.ct-floating-bar',
	'value' => blocksy_get_theme_mod('floatingBarBackground',
		blocksy_background_default_value([
			'backgroundColor' => [
				'default' => [
					'color' => 'var(--theme-palette-color-8)'
				],
			],
		])
	)
]);

blocksy_output_box_shadow([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-floating-bar',
	'should_skip_output' => false,
	'value' => blocksy_get_theme_mod(
		'floatingBarShadow',
		blocksy_box_shadow_value([
			'enable' => true,
			'h_offset' => 0,
			'v_offset' => 10,
			'blur' => 20,
			'spread' => 0,
			'inset' => false,
			'color' => [
				'color' => 'rgba(44,62,80,0.15)',
			],
		])
	),
	'responsive' => true
]);


// filter canvas
blocksy_output_font_css([
	'font_value' => blocksy_get_theme_mod( 'filter_panel_widgets_font',
		blocksy_typography_default_values([
			// 'size' => '18px',
		])
	),
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '#woo-filters-panel .ct-widget > *:not(.widget-title)',
]);


blocksy_output_colors([
	'value' => blocksy_get_theme_mod('filter_panel_widgets_font_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'link_initial' => [ 'color' => 'var(--theme-text-color)' ],
		'link_hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'variables' => [
		'default' => [
			'selector' => '#woo-filters-panel .ct-sidebar > *',
			'variable' => 'theme-text-color'
		],

		'link_initial' => [
			'selector' => '#woo-filters-panel .ct-sidebar',
			'variable' => 'theme-link-initial-color'
		],

		'link_hover' => [
			'selector' => '#woo-filters-panel .ct-sidebar',
			'variable' => 'theme-link-hover-color'
		],
	],
	'responsive' => true
]);


$vertical_alignment = blocksy_get_theme_mod( 'filter_panel_content_vertical_alignment', 'flex-start' );

if ($vertical_alignment !== 'flex-start') {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '#woo-filters-panel',
		'variableName' => 'vertical-alignment',
		'unit' => '',
		'value' => $vertical_alignment,
	]);
}


$woocommerce_filter_type = blocksy_get_theme_mod( 'woocommerce_filter_type', 'type-1' );

// filter type - off-canvas
if ($woocommerce_filter_type === 'type-1') {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '#woo-filters-panel[data-behaviour*="side"]',
		'variableName' => 'side-panel-width',
		'responsive' => true,
		'unit' => '',
		'value' => blocksy_get_theme_mod('filter_panel_width', [
			'desktop' => '500px',
			'tablet' => '65vw',
			'mobile' => '90vw',
		])
	]);


	blocksy_output_background_css([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'selector' => '#woo-filters-panel[data-behaviour*="side"] .ct-panel-inner',
		'value' => blocksy_get_theme_mod('filter_panel_background',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => '#ffffff'
					],
				],
			])
		)
	]);


	blocksy_output_background_css([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'selector' => '#woo-filters-panel[data-behaviour*="side"]',
		'value' => blocksy_get_theme_mod('filter_panel_backgrop',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'rgba(18, 21, 25, 0.6)'
					],
				],
			])
		)
	]);


	$close_button_type = blocksy_get_theme_mod('filter_panel_close_button_type', 'type-1');

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('filter_panel_close_button_color'),
		'default' => [
			'default' => [ 'color' => 'rgba(0, 0, 0, 0.5)' ],
			'hover' => [ 'color' => 'rgba(0, 0, 0, 0.8)' ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'variables' => [
			'default' => [
				'selector' => '#woo-filters-panel .ct-toggle-close',
				'variable' => 'theme-icon-color'
			],

			'hover' => [
				'selector' => '#woo-filters-panel .ct-toggle-close:hover',
				'variable' => 'theme-icon-color'
			]
		],
	]);


	if ($close_button_type === 'type-2') {
		blocksy_output_colors([
			'value' => blocksy_get_theme_mod('filter_panel_close_button_border_color'),
			'default' => [
				'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			],
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'responsive' => true,
			'variables' => [
				'default' => [
					'selector' => '#woo-filters-panel .ct-toggle-close[data-type="type-2"]',
					'variable' => 'toggle-button-border-color'
				],

				'hover' => [
					'selector' => '#woo-filters-panel .ct-toggle-close[data-type="type-2"]:hover',
					'variable' => 'toggle-button-border-color'
				]
			],
		]);
	}


	if ($close_button_type === 'type-3') {
		blocksy_output_colors([
			'value' => blocksy_get_theme_mod('filter_panel_close_button_shape_color'),
			'default' => [
				'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			],
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'responsive' => true,
			'variables' => [
				'default' => [
					'selector' => '#woo-filters-panel .ct-toggle-close[data-type="type-3"]',
					'variable' => 'toggle-button-background'
				],

				'hover' => [
					'selector' => '#woo-filters-panel .ct-toggle-close[data-type="type-3"]:hover',
					'variable' => 'toggle-button-background'
				]
			],
		]);
	}


	if ($close_button_type !== 'type-1') {
		$filter_panel_close_button_border_radius = blocksy_get_theme_mod( 'filter_panel_close_button_border_radius', 5 );

		if ($filter_panel_close_button_border_radius !== 5) {
			$css->put(
				'#woo-filters-panel .ct-toggle-close',
				'--toggle-button-radius: ' . $filter_panel_close_button_border_radius . 'px'
			);
		}
	}


	$filter_panel_close_button_icon_size = blocksy_get_theme_mod( 'filter_panel_close_button_icon_size', 12 );

	if ($filter_panel_close_button_icon_size !== 12) {
		$css->put(
			'#woo-filters-panel .ct-toggle-close',
			'--theme-icon-size: ' . $filter_panel_close_button_icon_size . 'px'
		);
	}


	blocksy_output_box_shadow([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '#woo-filters-panel[data-behaviour*="side"]',
		'value' => blocksy_get_theme_mod('filter_panel_shadow', blocksy_box_shadow_value([
			'enable' => true,
			'h_offset' => 0,
			'v_offset' => 0,
			'blur' => 70,
			'spread' => 0,
			'inset' => false,
			'color' => [
				'color' => 'rgba(0, 0, 0, 0.35)',
			],
		])),
		'responsive' => true
	]);

	$panel_widgets_spacing = blocksy_get_theme_mod( 'panel_widgets_spacing', 60 );

	if ($panel_widgets_spacing !== 60) {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '#woo-filters-panel .ct-sidebar',
			'variableName' => 'sidebar-widgets-spacing',
			'value' => $panel_widgets_spacing,
		]);
	}
}


// filter type - drop-down
if ($woocommerce_filter_type === 'type-2') {
	$filter_panel_height_type = blocksy_get_theme_mod( 'filter_panel_height_type', 'auto' );

	if ($filter_panel_height_type === 'custom') {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '#woo-filters-panel[data-behaviour="drop-down"]',
			'variableName' => 'filter-panel-height',
			'responsive' => true,
			'unit' => '',
			'value' => blocksy_get_theme_mod('filter_panel_height', [
				'desktop' => '250px',
				'tablet' => '250px',
				'mobile' => '250px',
			])
		]);
	}

	$filter_panel_columns = blocksy_expand_responsive_value(blocksy_get_theme_mod(
		'filter_panel_columns',
		[
			'desktop' => 4,
			'tablet' => 2,
			'mobile' => 1
		]
	));

	$columns_for_output = [
		'desktop' => 'repeat(' . $filter_panel_columns['desktop'] . ', 1fr)',
		'tablet' => 'repeat(' . $filter_panel_columns['tablet'] . ', 1fr)',
		'mobile' => 'repeat(' . $filter_panel_columns['mobile'] . ', 1fr)'
	];

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '#woo-filters-panel[data-behaviour="drop-down"]',
		'variableName' => 'grid-template-columns',
		'value' => $columns_for_output,
		'unit' => ''
	]);
}


// single product share box
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('product_share_items_icon_color', []),
	'default' => [
		'default' => [ 'color' => 'var(--theme-text-color)' ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => blocksy_prefix_selector('.ct-share-box', 'product'),
			'variable' => 'theme-icon-color'
		],
		'hover' => [
			'selector' => blocksy_prefix_selector('.ct-share-box', 'product'),
			'variable' => 'theme-icon-hover-color'
		],
	],
]);


// Single product type 2
$product_view_stacked_columns = blocksy_get_theme_mod('product_view_stacked_columns', 2);

if ($product_view_stacked_columns !== 2) {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-stacked-gallery .ct-stacked-gallery-container',
		'variableName' => 'columns',
		'value' => $product_view_stacked_columns,
		'unit' => ''
	]);
}


$product_view_columns_top = blocksy_get_theme_mod('product_view_columns_top', 3);

if ($product_view_columns_top !== 3) {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-columns-top-gallery .woocommerce-product-gallery',
		'variableName' => 'columns',
		'value' => $product_view_columns_top,
		'unit' => ''
	]);
}


// variation swatches
$default_product_layout = blocksy_get_woo_archive_layout_defaults();

$render_layout_config = blocksy_get_theme_mod(
	'woo_card_layout',
	$default_product_layout
);

if (function_exists('blocksy_normalize_layout')) {
	$render_layout_config = blocksy_normalize_layout(
		$render_layout_config,
		$default_product_layout
	);
}

$has_swatches = false;

foreach ($render_layout_config as $layer) {
	if (! $layer['enabled']) {
		continue;
	}

	if ($layer['id'] === 'product_swatches') {
		$has_swatches = true;
	}
}

if ($has_swatches) {
	$archive_color_swatch_size = blocksy_get_theme_mod('archive_color_swatch_size', 25);

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-card-variation-swatches [data-swatches-type="color"]',
		'variableName' => 'swatch-size',
		'value' => $archive_color_swatch_size
	]);


	$archive_image_swatch_size = blocksy_get_theme_mod('archive_image_swatch_size', 25);

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-card-variation-swatches [data-swatches-type="image"]',
		'variableName' => 'swatch-size',
		'value' => $archive_image_swatch_size
	]);


	$archive_button_swatch_size = blocksy_get_theme_mod('archive_button_swatch_size', 25);

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-card-variation-swatches [data-swatches-type="button"]',
		'variableName' => 'swatch-size',
		'value' => $archive_button_swatch_size
	]);

	$archive_mixed_swatch_size = blocksy_get_theme_mod('archive_mixed_swatch_size', 25);

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-card-variation-swatches [data-swatches-type="mixed"]',
		'variableName' => 'swatch-size',
		'value' => $archive_mixed_swatch_size
	]);
}


$single_color_swatch_size = blocksy_get_theme_mod('single_color_swatch_size', 30);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.variations_form.cart [data-swatches-type="color"]',
	'variableName' => 'swatch-size',
	'value' => $single_color_swatch_size
]);


$filter_widget_color_swatch_size = blocksy_get_theme_mod('filter_widget_color_swatch_size', 25);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-filter-widget[data-swatches-type="color"]',
	'variableName' => 'swatch-size',
	'value' => $filter_widget_color_swatch_size
]);


$single_image_swatch_size = blocksy_get_theme_mod('single_image_swatch_size', 35);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.variations_form.cart [data-swatches-type="image"]',
	'variableName' => 'swatch-size',
	'value' => $single_image_swatch_size
]);


$filter_widget_image_swatch_size = blocksy_get_theme_mod('filter_widget_image_swatch_size', 35);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-filter-widget[data-swatches-type="image"]',
	'variableName' => 'swatch-size',
	'value' => $filter_widget_image_swatch_size
]);


$single_button_swatch_size = blocksy_get_theme_mod('single_button_swatch_size', 35);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.variations_form.cart [data-swatches-type="button"]',
	'variableName' => 'swatch-size',
	'value' => $single_button_swatch_size
]);


$filter_widget_button_swatch_size = blocksy_get_theme_mod('filter_widget_button_swatch_size', 30);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-filter-widget[data-swatches-type="button"]',
	'variableName' => 'swatch-size',
	'value' => $filter_widget_button_swatch_size
]);


$single_mixed_swatch_size = blocksy_get_theme_mod('single_mixed_swatch_size', 30);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.variations_form.cart [data-swatches-type="mixed"]',
	'variableName' => 'swatch-size',
	'value' => $single_mixed_swatch_size
]);


$filter_widget_mixed_swatch_size = blocksy_get_theme_mod('filter_widget_mixed_swatch_size', 25);

blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-filter-widget[data-swatches-type="mixed"]',
	'variableName' => 'swatch-size',
	'value' => $filter_widget_mixed_swatch_size
]);



blocksy_output_colors([
	'value' => blocksy_get_theme_mod('color_swatch_border_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => 'rgba(0, 0, 0, 0.2)' ],
		'active' => [ 'color' => 'rgba(0, 0, 0, 0.2)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '[data-swatches-type="color"] .ct-swatch',
			'variable' => 'swatch-border-color'
		],

		'hover' => [
			'selector' => '[data-swatches-type="color"] > *:hover .ct-swatch',
			'variable' => 'swatch-border-color'
		],

		'active' => [
			'selector' => '[data-swatches-type="color"] > *.active .ct-swatch',
			'variable' => 'swatch-border-color'
		]
	],
]);


blocksy_output_colors([
	'value' => blocksy_get_theme_mod('image_swatch_border_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => 'var(--theme-palette-color-1)' ],
		'active' => [ 'color' => 'var(--theme-palette-color-1)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '[data-swatches-type="image"] .ct-swatch',
			'variable' => 'swatch-border-color'
		],

		'hover' => [
			'selector' => '[data-swatches-type="image"] > *:hover .ct-swatch',
			'variable' => 'swatch-border-color'
		],

		'active' => [
			'selector' => '[data-swatches-type="image"] > *.active .ct-swatch',
			'variable' => 'swatch-border-color'
		]
	],
]);


blocksy_output_colors([
	'value' => blocksy_get_theme_mod('button_swatch_text_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'active' => [ 'color' => '#ffffff' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '[data-swatches-type="button"] .ct-swatch',
			'variable' => 'swatch-button-text-color'
		],

		'hover' => [
			'selector' => '[data-swatches-type="button"] > *:hover .ct-swatch',
			'variable' => 'swatch-button-text-color'
		],

		'active' => [
			'selector' => '[data-swatches-type="button"] > *.active .ct-swatch',
			'variable' => 'swatch-button-text-color'
		]
	],
]);


blocksy_output_colors([
	'value' => blocksy_get_theme_mod('button_swatch_border_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => 'var(--theme-palette-color-1)' ],
		'active' => [ 'color' => 'var(--theme-palette-color-1)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '[data-swatches-type="button"] .ct-swatch',
			'variable' => 'swatch-button-border-color'
		],

		'hover' => [
			'selector' => '[data-swatches-type="button"] > *:hover .ct-swatch',
			'variable' => 'swatch-button-border-color'
		],

		'active' => [
			'selector' => '[data-swatches-type="button"] > *.active .ct-swatch',
			'variable' => 'swatch-button-border-color'
		]
	],
]);


blocksy_output_colors([
	'value' => blocksy_get_theme_mod('button_swatch_background_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'active' => [ 'color' => 'var(--theme-palette-color-1)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '[data-swatches-type="button"] .ct-swatch',
			'variable' => 'swatch-button-background-color'
		],

		'hover' => [
			'selector' => '[data-swatches-type="button"] > *:hover .ct-swatch',
			'variable' => 'swatch-button-background-color'
		],

		'active' => [
			'selector' => '[data-swatches-type="button"] > *.active .ct-swatch',
			'variable' => 'swatch-button-background-color'
		]
	],
]);


blocksy_output_colors([
	'value' => blocksy_get_theme_mod('mixed_swatch_border_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'hover' => [ 'color' => 'rgba(0, 0, 0, 0.2)' ],
		'active' => [ 'color' => 'rgba(0, 0, 0, 0.2)' ],
	],
	'css' => $css,
	'variables' => [
		'default' => [
			'selector' => '[data-swatches-type="mixed"] .ct-swatch',
			'variable' => 'swatch-border-color'
		],

		'hover' => [
			'selector' => '[data-swatches-type="mixed"] > *:hover .ct-swatch',
			'variable' => 'swatch-border-color'
		],

		'active' => [
			'selector' => '[data-swatches-type="mixed"] > *.active .ct-swatch',
			'variable' => 'swatch-border-color'
		]
	],
]);


// shipping progress bar
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('shipping_progress_bar_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'active' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'active_2' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	// 'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '[class*="ct-shipping-progress"]',
			'variable' => 'product-progress-bar-initial-color'
		],
		'active' => [
			'selector' => '[class*="ct-shipping-progress"]',
			'variable' => 'product-progress-bar-active-color'
		],

		'active_2' => [
			'selector' => '[class*="ct-shipping-progress"]',
			'variable' => 'product-progress-bar-active-color-2'
		],
	],
]);


// stock scarcity bar
blocksy_output_responsive([
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'selector' => '.ct-product-stock-scarcity',
	'variableName' => 'product-progress-bar-height',
	'value' => blocksy_get_theme_mod('stock_scarcity_bar_height', 5),
	'unit' => 'px'
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('stock_scarcity_bar_color'),
	'default' => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'active' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		'active_2' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	// 'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '.ct-product-stock-scarcity',
			'variable' => 'product-progress-bar-initial-color'
		],
		'active' => [
			'selector' => '.ct-product-stock-scarcity',
			'variable' => 'product-progress-bar-active-color'
		],

		'active_2' => [
			'selector' => '.ct-product-stock-scarcity',
			'variable' => 'product-progress-bar-active-color-2'
		],
	],
]);


// new badge
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('newBadgeColor'),
	'default' => [
		'text' => [ 'color' => '#ffffff' ],
		'background' => [ 'color' => '#35a236' ],
	],
	'css' => $css,
	'variables' => [
		'text' => [
			'selector' => '.ct-woo-badge-new',
			'variable' => 'badge-text-color'
		],

		'background' => [
			'selector' => '.ct-woo-badge-new',
			'variable' => 'badge-background-color'
		],
	],
]);


// featured badge
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('featuredBadgeColor'),
	'default' => [
		'text' => [ 'color' => '#ffffff' ],
		'background' => [ 'color' => '#de283f' ],
	],
	'css' => $css,
	'variables' => [
		'text' => [
			'selector' => '.ct-woo-badge-featured',
			'variable' => 'badge-text-color'
		],

		'background' => [
			'selector' => '.ct-woo-badge-featured',
			'variable' => 'badge-background-color'
		],
	],
]);


// quick view
$has_quick_view = $storage->get_settings()['features']['quick-view'];

if ($has_quick_view) {
	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-quick-view-card',
		'variableName' => 'theme-normal-container-max-width',
		'value' => blocksy_get_theme_mod('woocommerce_quick_view_width', 1050),
		'unit' => 'px'
	]);

	blocksy_output_font_css([
		'font_value' => blocksy_get_theme_mod(
			'quickViewProductTitleFont',
			blocksy_typography_default_values([
				// 'size' => '30px',
			])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-quick-view-card .product_title'
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('quick_view_title_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-quick-view-card .entry-summary .product_title',
				'variable' => 'theme-heading-color'
			],
		],
	]);

	blocksy_output_font_css([
		'font_value' => blocksy_get_theme_mod(
			'quickViewProductPriceFont',
			blocksy_typography_default_values([
				// 'size' => '30px',
			])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-quick-view-card .entry-summary .price'
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('quick_view_price_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-quick-view-card .entry-summary .price',
				'variable' => 'theme-text-color'
			],
		],
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('quick_view_description_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-quick-view-card .woocommerce-product-details__short-description',
				'variable' => 'theme-text-color'
			],
		],
	]);



	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('quick_view_add_to_cart_text'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-quick-view-card .entry-summary .single_add_to_cart_button',
				'variable' => 'theme-button-text-initial-color'
			],

			'hover' => [
				'selector' => '.ct-quick-view-card .entry-summary .single_add_to_cart_button',
				'variable' => 'theme-button-text-hover-color'
			],
		],
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('quick_view_add_to_cart_background'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-quick-view-card .entry-summary .single_add_to_cart_button',
				'variable' => 'theme-button-background-initial-color'
			],

			'hover' => [
				'selector' => '.ct-quick-view-card .entry-summary .single_add_to_cart_button',
				'variable' => 'theme-button-background-hover-color'
			],
		],
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('quick_view_view_cart_button_text'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-quick-view-card .entry-summary .ct-cart-actions .added_to_cart',
				'variable' => 'theme-button-text-initial-color'
			],

			'hover' => [
				'selector' => '.ct-quick-view-card .entry-summary .ct-cart-actions .added_to_cart',
				'variable' => 'theme-button-text-hover-color'
			],
		],
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('quick_view_view_cart_button_background'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-quick-view-card .entry-summary .ct-cart-actions .added_to_cart',
				'variable' => 'theme-button-background-initial-color'
			],

			'hover' => [
				'selector' => '.ct-quick-view-card .entry-summary .ct-cart-actions .added_to_cart',
				'variable' => 'theme-button-background-hover-color'
			],
		],
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('quick_view_product_page_button_text'),
		'default' => [
			'default' => [ 'color' => 'var(--theme-text-color)' ],
			'hover' => [ 'color' => 'var(--theme-text-color)' ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-quick-view-card .entry-summary .ct-quick-more',
				'variable' => 'theme-button-text-initial-color'
			],

			'hover' => [
				'selector' => '.ct-quick-view-card .entry-summary .ct-quick-more',
				'variable' => 'theme-button-text-hover-color'
			],
		],
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('quick_view_product_page_button_background'),
		'default' => [
			'default' => [ 'color' => 'rgba(224,229,235,0.6)' ],
			'hover' => [ 'color' => 'rgba(224,229,235,1)' ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-quick-view-card .entry-summary .ct-quick-more',
				'variable' => 'theme-button-background-initial-color'
			],

			'hover' => [
				'selector' => '.ct-quick-view-card .entry-summary .ct-quick-more',
				'variable' => 'theme-button-background-hover-color'
			],
		],
	]);



	blocksy_output_box_shadow([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-quick-view-card',
		'value' => blocksy_get_theme_mod('quick_view_shadow', blocksy_box_shadow_value([
			'enable' => true,
			'h_offset' => 0,
			'v_offset' => 50,
			'blur' => 100,
			'spread' => 0,
			'inset' => false,
			'color' => [
				'color' => 'rgba(18, 21, 25, 0.5)',
			],
		])),
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-quick-view-card',
		'property' => 'theme-border-radius',
		'value' => blocksy_get_theme_mod( 'quick_view_radius',
			blocksy_spacing_value()
		),
		'empty_value' => 7
	]);

	blocksy_output_background_css([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-quick-view-card',
		'value' => blocksy_get_theme_mod('quick_view_background',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'var(--theme-palette-color-8)'
					],
				],
			])
		)
	]);

	blocksy_output_background_css([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.quick-view-modal',
		'value' => blocksy_get_theme_mod('quick_view_backdrop',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'rgba(18, 21, 25, 0.8)'
					],
				],
			])
		)
	]);
}


// added to cart popup
$has_added_to_cart_popup = $storage->get_settings()['features']['added-to-cart-popup'];

if ($has_added_to_cart_popup) {

	$image_width = blocksy_get_theme_mod('added_to_cart_popup_image_width', 20);

	if ($image_width !== 20) {
		$css->put(
			'#ct-added-to-cart-popup',
			'--product-image-width: ' . $image_width . '%'
		);
	}

	blocksy_output_font_css([
		'font_value' => blocksy_get_theme_mod(
			'added_to_cart_popup_title_font',
			blocksy_typography_default_values([
				'size' => '16px',
			])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-added-to-cart-product .woocommerce-loop-product__title'
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('added_to_cart_popup_title_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-added-to-cart-product .woocommerce-loop-product__title',
				'variable' => 'theme-heading-color'
			],
		],
	]);

	blocksy_output_font_css([
		'font_value' => blocksy_get_theme_mod(
			'added_to_cart_popup_price_font',
			blocksy_typography_default_values([
				'size' => '15px',
				'variation' => 'n7',
			])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-added-to-cart-product .price'
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('added_to_cart_popup_price_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-added-to-cart-product .price',
				'variable' => 'theme-text-color'
			],
		],
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-added-to-cart-product .ct-media-container',
		'property' => 'theme-border-radius',
		'value' => blocksy_get_theme_mod(
			'added_to_cart_popup_image_radius',
			blocksy_spacing_value()
		)
	]);


	// popup
	$popup_size = blocksy_get_theme_mod('added_to_cart_popup_size', 'large');

	if ($popup_size === 'custom') {
		$popup_max_width = blocksy_get_theme_mod('added_to_cart_popup_max_width', '900px');

		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '#ct-added-to-cart-popup[data-popup-size="custom"]',
			'variableName' => 'popup-max-width',
			'unit' => '',
			'value' => $popup_max_width,
		]);

		$popup_max_height = blocksy_get_theme_mod('added_to_cart_popup_max_height', '700px');

		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '#ct-added-to-cart-popup[data-popup-size="custom"]',
			'variableName' => 'popup-max-height',
			'unit' => '',
			'value' => $popup_max_height,
		]);

		$css->put(
			'#ct-added-to-cart-popup[data-popup-size="custom"]',
			'--popup-height: 100%'
		);
	}


	$popup_entrance_speed = blocksy_get_theme_mod('added_to_cart_popup_entrance_speed', 0.2);

	$css->put(
		'#ct-added-to-cart-popup',
		'--popup-entrance-speed: ' . $popup_entrance_speed . 's'
	);


	$popup_entrance_value = blocksy_get_theme_mod('added_to_cart_popup_entrance_value', 50);

	if ($popup_entrance_value !== 50) {
		$css->put(
			'#ct-added-to-cart-popup',
			'--popup-entrance-value: ' . $popup_entrance_value . 'px'
		);
	}


	$popup_edges_offset = blocksy_get_theme_mod('added_to_cart_popup_edges_offset', 25);

	if ($popup_edges_offset !== 25) {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '#ct-added-to-cart-popup',
			'variableName' => 'popup-edges-offset',
			'value' => $popup_edges_offset,
			'unit' => 'px'
		]);
	}


	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '#ct-added-to-cart-popup',
		'property' => 'popup-padding',
		'value' => blocksy_get_theme_mod(
			'added_to_cart_popup_padding',
			blocksy_spacing_value()
		),
		'empty_value' => 30
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '#ct-added-to-cart-popup',
		'property' => 'popup-border-radius',
		'value' => blocksy_get_theme_mod(
			'added_to_cart_popup_border_radius',
			blocksy_spacing_value()
		),
		'empty_value' => 7
	]);

	blocksy_output_box_shadow([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '#ct-added-to-cart-popup',
		'value' => blocksy_get_theme_mod('added_to_cart_popup_shadow',
			blocksy_box_shadow_value([
				'enable' => true,
				'h_offset' => 0,
				'v_offset' => 10,
				'blur' => 20,
				'spread' => 0,
				'inset' => false,
				'color' => [
					'color' => 'rgba(41, 51, 61, 0.1)',
				],
			])),
		'variableName' => 'popup-box-shadow',
		'responsive' => true
	]);

	$icon_size = blocksy_get_theme_mod( 'added_to_cart_popup_close_button_icon_size', 12 );

	$css->put(
		'#ct-added-to-cart-popup .ct-toggle-close',
		'--theme-icon-size: ' . $icon_size . 'px'
	);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('added_to_cart_popup_close_button_color'),
		'default' => [
			'default' => [ 'color' => 'rgba(0, 0, 0, 0.5)' ],
			'hover' => [ 'color' => 'rgba(0, 0, 0, 0.8)' ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'variables' => [
			'default' => [
				'selector' => '#ct-added-to-cart-popup .ct-toggle-close',
				'variable' => 'theme-icon-color'
			],

			'hover' => [
				'selector' => '#ct-added-to-cart-popup .ct-toggle-close:hover',
				'variable' => 'theme-icon-color'
			]
		],
	]);



	blocksy_output_background_css([
		'selector' => '#ct-added-to-cart-popup .ct-popup-inner > article',
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'value' => blocksy_get_theme_mod('added_to_cart_popup_background',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'var(--theme-palette-color-8)'
					],
				],
			])
		)
	]);

	blocksy_output_background_css([
		'selector' => '#ct-added-to-cart-popup',
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'value' => blocksy_get_theme_mod('added_to_cart_popup_backdrop_background',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'rgba(18, 21, 25, 0.5)'
					],
				],
			])
		)
	]);


	// suggested products
	$slideshow_columns = blocksy_get_theme_mod('added_to_cart_popup_products_columns', [
		'desktop' => 2,
		'tablet' => 2,
		'mobile' => 1,
	]);

	$slideshow_columns = blocksy_expand_responsive_value($slideshow_columns);

	$slideshow_columns['desktop'] = 'calc(100% / ' . $slideshow_columns['desktop'] . ')';
	$slideshow_columns['tablet'] = 'calc(100% / ' . $slideshow_columns['tablet'] . ')';
	$slideshow_columns['mobile'] = 'calc(100% / ' . $slideshow_columns['mobile'] . ')';

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '#ct-added-to-cart-popup',
		'variableName' => 'grid-columns-width',
		'value' => $slideshow_columns,
		'unit' => ''
	]);


	blocksy_output_font_css([
		'font_value' => blocksy_get_theme_mod(
			'suggested_products_title_font',
			blocksy_typography_default_values([
				'size' => '14px',
			])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-suggested-products [data-products] .woocommerce-loop-product__title'
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('suggested_products_title_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-suggested-products [data-products] .woocommerce-loop-product__title',
				'variable' => 'theme-heading-color'
			],
		],
	]);

	blocksy_output_font_css([
		'font_value' => blocksy_get_theme_mod(
			'suggested_products_price_font',
			blocksy_typography_default_values([
				'size' => '14px',
			])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-suggested-products [data-products] .price'
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('suggested_products_price_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-suggested-products [data-products] .price',
				'variable' => 'theme-text-color'
			],
		],
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-suggested-products [data-products="inline"]',
		'property' => 'theme-border-radius',
		'value' => blocksy_get_theme_mod(
			'suggested_products_image_radius_type_inline',
				blocksy_spacing_value()
		),
		'empty_value' => 100
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-suggested-products [data-products="block"]',
		'property' => 'theme-border-radius',
		'value' => blocksy_get_theme_mod(
			'suggested_products_image_radius_type_block',
			blocksy_spacing_value([
				'top' => '3px',
				'left' => '3px',
				'right' => '3px',
				'bottom' => '3px',
			]),
		)
	]);

}


// compare view
$has_compare = $storage->get_settings()['features']['compareview'];

if ($has_compare) {
	blocksy_output_box_shadow([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '#ct-compare-modal .ct-container',
		'value' => blocksy_get_theme_mod('compare_modal_shadow', blocksy_box_shadow_value([
			'enable' => true,
			'h_offset' => 0,
			'v_offset' => 50,
			'blur' => 100,
			'spread' => 0,
			'inset' => false,
			'color' => [
				'color' => 'rgba(18, 21, 25, 0.5)',
			],
		])),
		'responsive' => true
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '#ct-compare-modal .ct-container',
		'property' => 'theme-border-radius',
		'value' => blocksy_get_theme_mod( 'compare_modal_radius',
			blocksy_spacing_value()
		),
		'empty_value' => 7
	]);

	blocksy_output_background_css([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'selector' => '#ct-compare-modal .ct-container',
		'value' => blocksy_get_theme_mod('compare_modal_background',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'var(--theme-palette-color-8)'
					],
				],
			])
		)
	]);

	blocksy_output_background_css([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'selector' => '#ct-compare-modal',
		'value' => blocksy_get_theme_mod('compare_modal_backdrop',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'rgba(18, 21, 25, 0.8)'
					],
				],
			])
		)
	]);


	// compare table layers
	$render_layout_config = blocksy_get_theme_mod('product_compare_layout', [
		[
			'id' => 'product_main',
			'enabled' => true,
		],
		[
			'id' => 'product_title',
			'enabled' => true,
		],
		[
			'id' => 'product_price',
			'enabled' => true,
		],
		[
			'id' => 'product_description',
			'enabled' => true,
		],
		[
			'id' => 'product_attributes',
			'enabled' => true,
			'product_attributes_source' => 'all',
		],
		[
			'id' => 'product_availability',
			'enabled' => true,
		],
		[
			'id' => 'product_add_to_cart',
			'enabled' => true,
		],
	]);

	foreach ($render_layout_config as $layer) {
		if (! $layer['enabled'] ) {
			continue;
		}

		$selectors_map = [
			'product_brands' => '.ct-compare-column > .ct-product-brands',
		];

		if ($layer['id'] === 'product_brands') {
			$brand_logo_size = blocksy_akg('brand_logo_size', $layer, 60);

			if ($brand_logo_size !== 60) {
				blocksy_output_responsive([
					'css' => $css,
					'tablet_css' => $tablet_css,
					'mobile_css' => $mobile_css,
					'selector' => $selectors_map[$layer['id']],
					'variableName' => 'product-brand-logo-size',
					'value' => $brand_logo_size,
				]);
			}

			$brand_logo_gap = blocksy_akg('brand_logo_gap', $layer, 10);

			if ($brand_logo_gap !== 10) {
				blocksy_output_responsive([
					'css' => $css,
					'tablet_css' => $tablet_css,
					'mobile_css' => $mobile_css,
					'selector' => $selectors_map[$layer['id']],
					'variableName' => 'product-brands-gap',
					'value' => $brand_logo_gap,
				]);
			}
		}
	}


	// compare bar
	$has_product_compare_bar = blocksy_get_theme_mod('product_compare_bar', 'no');

	if ($has_product_compare_bar === 'yes') {
		$product_compare_bar_height = blocksy_expand_responsive_value(
			blocksy_get_theme_mod('product_compare_bar_height', 70)
		);

		$product_compare_bar_visibility = blocksy_expand_responsive_value(blocksy_get_theme_mod(
			'product_compare_bar_visibility',
			[
				'desktop' => true,
				'tablet' => true,
				'mobile' => true,
			]
		));

		if (
			! isset($product_compare_bar_visibility['desktop'])
			||
			! $product_compare_bar_visibility['desktop']
		) {
			$product_compare_bar_height['desktop'] = '0';
		}

		if (
			! isset($product_compare_bar_visibility['tablet'])
			||
			! $product_compare_bar_visibility['desktop']
		) {
			$product_compare_bar_height['tablet'] = '0';
		}

		if (
			! isset($product_compare_bar_visibility['mobile'])
			||
			! $product_compare_bar_visibility['desktop']
		) {
			$product_compare_bar_height['mobile'] = '0';
		}

		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '.ct-drawer-canvas[data-compare-bar]',
			'variableName' => 'compare-bar-height',
			'value' => $product_compare_bar_height
		]);

		blocksy_output_colors([
			'value' => blocksy_get_theme_mod('product_compare_bar_button_font_color'),
			'default' => [
				'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			],
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'variables' => [
				'default' => [
					'selector' => '.ct-compare-bar',
					'variable' => 'theme-button-text-initial-color'
				],

				'hover' => [
					'selector' => '.ct-compare-bar',
					'variable' => 'theme-button-text-hover-color'
				],
			],
			'responsive' => true
		]);

		blocksy_output_colors([
			'value' => blocksy_get_theme_mod('product_compare_bar_button_background_color'),
			'default' => [
				'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			],
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'variables' => [
				'default' => [
					'selector' => '.ct-compare-bar',
					'variable' => 'theme-button-background-initial-color'
				],

				'hover' => [
					'selector' => '.ct-compare-bar',
					'variable' => 'theme-button-background-hover-color'
				],
			],
			'responsive' => true
		]);

		blocksy_output_background_css([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'responsive' => true,
			'selector' => '.ct-compare-bar',
			'value' => blocksy_get_theme_mod('product_compare_bar_background',
				blocksy_background_default_value([
					'backgroundColor' => [
						'default' => [
							'color' => 'var(--theme-palette-color-4)',
						],
					],
				])
			)
		]);
	}
}


// size guide
$has_size_guide = $storage->get_settings()['features']['product-size-guide'];

if ($has_size_guide) {

	$size_guide_placement = blocksy_get_theme_mod('size_guide_placement', 'modal');

	$size_guide_background_selector = '#ct-size-guide-modal .ct-container';

	// modal
	if ($size_guide_placement === 'modal') {

		blocksy_output_box_shadow([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '#ct-size-guide-modal .ct-container',
			'value' => blocksy_get_theme_mod('size_guide_modal_shadow', blocksy_box_shadow_value([
				'enable' => true,
				'h_offset' => 0,
				'v_offset' => 50,
				'blur' => 100,
				'spread' => 0,
				'inset' => false,
				'color' => [
					'color' => 'rgba(18, 21, 25, 0.5)',
				],
			])),
			'responsive' => true
		]);

		blocksy_output_spacing([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '#ct-size-guide-modal .ct-container',
			'property' => 'theme-border-radius',
			'value' => blocksy_get_theme_mod( 'size_guide_modal_radius',
				blocksy_spacing_value()
			),
			'empty_value' => 7
		]);
	}


	// panel
	if ($size_guide_placement === 'panel') {

		$size_guide_background_selector = '#ct-size-guide-modal .ct-panel-inner';

		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '#ct-size-guide-modal',
			'variableName' => 'side-panel-width',
			'unit' => '',
			'value' => blocksy_get_theme_mod('size_guide_side_panel_width', [
				'desktop' => '700px',
				'tablet' => '65vw',
				'mobile' => '90vw',
			])
		]);

		blocksy_output_box_shadow([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '#ct-size-guide-modal .ct-panel-inner',
			'value' => blocksy_get_theme_mod('size_guide_panel_shadow', blocksy_box_shadow_value([
				'enable' => true,
				'h_offset' => 0,
				'v_offset' => 0,
				'blur' => 70,
				'spread' => 0,
				'inset' => false,
				'color' => [
					'color' => 'rgba(0, 0, 0, 0.35)',
				],
			])),
			'responsive' => true
		]);
	}

	blocksy_output_background_css([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'selector' => $size_guide_background_selector,
		'value' => blocksy_get_theme_mod('size_guide_modal_background',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'var(--theme-palette-color-8)'
					],
				],
			])
		)
	]);

	blocksy_output_background_css([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'selector' => '#ct-size-guide-modal',
		'value' => blocksy_get_theme_mod('size_guide_modal_backdrop',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => 'rgba(18, 21, 25, 0.8)'
					],
				],
			])
		)
	]);

	// close button
	$size_guide_close_button_icon_size = blocksy_get_theme_mod( 'size_guide_close_button_icon_size', 12 );

	if ($size_guide_close_button_icon_size !== 12) {
		$css->put(
			'#ct-size-guide-modal .ct-toggle-close',
			'--theme-icon-size: ' . $size_guide_close_button_icon_size . 'px'
		);
	}

	$close_button_type = blocksy_get_theme_mod('size_guide_close_button_type', 'type-1');

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('size_guide_close_button_color'),
		'default' => [
			'default' => [ 'color' => 'rgba(0, 0, 0, 0.5)' ],
			'hover' => [ 'color' => 'rgba(0, 0, 0, 0.8)' ],
		],
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'responsive' => true,
		'variables' => [
			'default' => [
				'selector' => '#ct-size-guide-modal .ct-toggle-close',
				'variable' => 'theme-icon-color'
			],

			'hover' => [
				'selector' => '#ct-size-guide-modal .ct-toggle-close:hover',
				'variable' => 'theme-icon-color'
			]
		],
	]);

	if ($close_button_type === 'type-2') {
		blocksy_output_colors([
			'value' => blocksy_get_theme_mod('size_guide_close_button_border_color'),
			'default' => [
				'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			],
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'responsive' => true,
			'variables' => [
				'default' => [
					'selector' => '#ct-size-guide-modal .ct-toggle-close[data-type="type-2"]',
					'variable' => 'toggle-button-border-color'
				],

				'hover' => [
					'selector' => '#ct-size-guide-modal .ct-toggle-close[data-type="type-2"]:hover',
					'variable' => 'toggle-button-border-color'
				]
			],
		]);
	}

	if ($close_button_type === 'type-3') {
		blocksy_output_colors([
			'value' => blocksy_get_theme_mod('size_guide_close_button_shape_color'),
			'default' => [
				'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
				'hover' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
			],
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'responsive' => true,
			'variables' => [
				'default' => [
					'selector' => '#ct-size-guide-modal .ct-toggle-close[data-type="type-3"]',
					'variable' => 'toggle-button-background'
				],

				'hover' => [
					'selector' => '#ct-size-guide-modal .ct-toggle-close[data-type="type-3"]:hover',
					'variable' => 'toggle-button-background'
				]
			],
		]);
	}

	if ($close_button_type !== 'type-1') {
		$size_guide_close_button_border_radius = blocksy_get_theme_mod( 'size_guide_close_button_border_radius', 5 );

		if ($size_guide_close_button_border_radius !== 5) {
			$css->put(
				'#ct-size-guide-modal .ct-toggle-close',
				'--toggle-button-radius: ' . $size_guide_close_button_border_radius . 'px'
			);
		}
	}
}




// product archive additional action buttons
blocksy_output_colors([
	'value' => blocksy_get_theme_mod('additional_actions_button_icon_color'),
	'default' => [
		'default' => [ 'color' => 'var(--theme-text-color)' ],
		'hover' => [ 'color' => '#ffffff' ],

		'default_2' => [ 'color' => 'var(--theme-text-color)' ],
		'hover_2' => [ 'color' => 'var(--theme-palette-color-1)' ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '.ct-woo-card-extra[data-type="type-1"]',
			'variable' => 'theme-button-text-initial-color'
		],
		'hover' => [
			'selector' => '.ct-woo-card-extra[data-type="type-1"]',
			'variable' => 'theme-button-text-hover-color'
		],

		'default_2' => [
			'selector' => '.ct-woo-card-extra[data-type="type-2"]',
			'variable' => 'theme-button-text-initial-color'
		],
		'hover_2' => [
			'selector' => '.ct-woo-card-extra[data-type="type-2"]',
			'variable' => 'theme-button-text-hover-color'
		],
	],
]);

blocksy_output_colors([
	'value' => blocksy_get_theme_mod('additional_actions_button_background_color'),
	'default' => [
		'default' => [ 'color' => '#ffffff' ],
		'hover' => [ 'color' => 'var(--theme-palette-color-1)' ],

		'default_2' => [ 'color' => '#ffffff' ],
		'hover_2' => [ 'color' => '#ffffff' ],
	],
	'css' => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'responsive' => true,
	'variables' => [
		'default' => [
			'selector' => '.ct-woo-card-extra[data-type="type-1"]',
			'variable' => 'theme-button-background-initial-color'
		],
		'hover' => [
			'selector' => '.ct-woo-card-extra[data-type="type-1"]',
			'variable' => 'theme-button-background-hover-color'
		],

		'default_2' => [
			'selector' => '.ct-woo-card-extra[data-type="type-2"]',
			'variable' => 'theme-button-background-initial-color'
		],
		'hover_2' => [
			'selector' => '.ct-woo-card-extra[data-type="type-2"]',
			'variable' => 'theme-button-background-hover-color'
		],
	],
]);

// related slideshow columns
if (blocksy_get_theme_mod('woocommerce_related_products_slideshow', 'default') === 'slider') {
	$related_slideshow_columns = blocksy_get_theme_mod('woocommerce_related_products_slideshow_columns', [
		'desktop' => 4,
		'tablet' => 3,
		'mobile' => 1,
	]);

	$related_slideshow_columns = blocksy_expand_responsive_value($related_slideshow_columns);

	$related_slideshow_columns['desktop'] = 'calc(100% / ' . $related_slideshow_columns['desktop'] . ')';
	$related_slideshow_columns['tablet'] = 'calc(100% / ' . $related_slideshow_columns['tablet'] . ')';
	$related_slideshow_columns['mobile'] = 'calc(100% / ' . $related_slideshow_columns['mobile'] . ')';

	blocksy_output_responsive([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.related [data-products], .upsells [data-products]',
		'variableName' => 'grid-columns-width',
		'value' => $related_slideshow_columns,
		'unit' => ''
	]);
}


// waitlist
$has_product_waitlist = $storage->get_settings()['features']['product-waitlist'];

if ($has_product_waitlist) {

	$container_max_width = blocksy_get_theme_mod('waitlist_container_max_width', 100);

	if ($container_max_width !== 100) {
		blocksy_output_responsive([
			'css' => $css,
			'tablet_css' => $tablet_css,
			'mobile_css' => $mobile_css,
			'selector' => '.ct-product-waitlist',
			'variableName' => 'container-max-width',
			'value' => $container_max_width,
			'unit' => '%'
		]);
	}

	blocksy_output_font_css([
		'font_value' => blocksy_get_theme_mod(
			'waitlist_title_font',
			blocksy_typography_default_values([
				'size' => '16px',
			])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-waitlist-title'
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('waitlist_title_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-waitlist-title',
				'variable' => 'theme-heading-color'
			],
		],
	]);

	blocksy_output_font_css([
		'font_value' => blocksy_get_theme_mod(
			'waitlist_message_font',
			blocksy_typography_default_values([
				'size' => '15px',
			])
		),
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-waitlist-message'
	]);

	blocksy_output_colors([
		'value' => blocksy_get_theme_mod('waitlist_message_color'),
		'default' => [
			'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT') ],
		],
		'css' => $css,
		'variables' => [
			'default' => [
				'selector' => '.ct-product-waitlist p',
				'variable' => 'theme-text-color'
			],
		],
	]);

	blocksy_output_border([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-product-waitlist',
		'variableName' => 'container-border',
		'value' => blocksy_get_theme_mod('waitlist_form_border'),
		'skip_none' => true,
		'default' => [
			'width' => 2,
			'style' => 'solid',
			'color' => [
				'color' => 'var(--theme-border-color)',
			],
		],
		'responsive' => true,
	]);

	blocksy_output_background_css([
		'selector' => '.ct-product-waitlist',
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'value' => blocksy_get_theme_mod(
			'waitlist_form_background',
			blocksy_background_default_value([
				'backgroundColor' => [
					'default' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
					],
				],
			])
		),
		'responsive' => true,
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-product-waitlist',
		'property' => 'container-padding',
		'value' => blocksy_get_theme_mod(
			'waitlist_form_padding',
			blocksy_spacing_value()
		),
		'empty_value' => 30
	]);

	blocksy_output_spacing([
		'css' => $css,
		'tablet_css' => $tablet_css,
		'mobile_css' => $mobile_css,
		'selector' => '.ct-product-waitlist',
		'property' => 'container-border-radius',
		'value' => blocksy_get_theme_mod(
			'waitlist_form_border_radius',
			blocksy_spacing_value()
		),
		'empty_value' => 7
	]);
}