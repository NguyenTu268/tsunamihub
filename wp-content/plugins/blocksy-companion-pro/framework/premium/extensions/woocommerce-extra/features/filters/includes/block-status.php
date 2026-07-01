<?php

namespace Blocksy\Extensions\WoocommerceExtra;

class StatusBlock {
	public function __construct() {
        add_action('init', [$this, 'blocksy_status_filter_block']);

		add_filter('blocksy:gutenberg-blocks-data', function ($data) {
            $options = blocksy_akg(
				'options',
				blocksy_get_variables_from_file(
					dirname(__FILE__) . '/options/status.php',
					['options' => []]
				)
			);

			$options_name = 'status_filter';
			$data[$options_name] = $options;

			$filter = Filters::get_filter_instance('status_filter');

			$data['blocksy_woo_extra_status_filters'] = [
				'status_counts' => $filter->get_status_counts(),
                'status_labels' => $filter::get_status_options(),
			];

			return $data;
		});
    }

	public function blocksy_status_filter_block() {
		register_block_type('blocksy/woocommerce-status-filter', [
			'render_callback' => function ($attributes, $content, $block) {
				if (
					! is_woocommerce()
					&&
					! wp_doing_ajax()
					||
					is_singular()
				) {
					return '';
				}

				$statuses = StatusFilter::get_status_options();
				$status_values = [];

				foreach ($statuses as $key => $status) {
					$status_values[] = [
						'id' => $key,
						'label' => $status,
						'enabled' => true,
					];
				}

				$attributes = wp_parse_args($attributes, [
                    'layout' => 'list',
					'showCounters' => true,
                    'showCheckboxes' => true,
					'showResetButton' => false,
                    'statuses' => $status_values,
				]);
				
				$filter = Filters::get_filter_instance('status_filter');

				$presenter = new FilterPresenter($filter);
				return $presenter->render($attributes);
			},
		]);
	}
}
