<?php

namespace Blocksy\Extensions\WoocommerceExtra;

class FiltersTaxonomiesProductsLookupStore {
	private $lookup_table_name = '';

	private $hierarchy_children_first_cache = [];

	public function __construct($table_name) {
		$this->lookup_table_name = $table_name;

		new TaxonomiesProductsLookupHooks($this);
	}

	public function create_data_for_product($product) {
		if (! is_a($product, \WC_Product::class)) {
			$product = WC()->call_function('wc_get_product', $product);
		}

		if (! $product) {
			return;
		}

		$this->delete_data_for([
			'column' => 'product_id',
			'value' => $product->get_id()
		]);

		$this->create_data_for($product);
	}

	public function delete_data_for($args = []) {
		$args = wp_parse_args($args, [
			'column' => 'product_id',
			'value' => 0
		]);

		global $wpdb;

		$sql = $wpdb->prepare(
			'DELETE FROM ' . $this->lookup_table_name . ' WHERE %i = %d',
			$args['column'],
			$args['value']
		);

		$wpdb->query($sql);
	}

	private function create_data_for(\WC_Product $product) {
		if ($this->is_variation($product)) {
			return;
		}

		if ($product->get_catalog_visibility() === 'hidden') {
			return;
		}

		$this->create_data_for_simple_product($product);
	}

	private function is_variation(\WC_Product $product) {
		return is_a($product, \WC_Product_Variation::class);
	}

	private function create_data_for_simple_product(\WC_Product $product) {
		$all_taxonomies = blc_get_ext('woocommerce-extra')
			->utils
			->get_product_taxonomies();

		global $sitepress;

		if ($sitepress) {
			remove_filter('get_terms_args', array($sitepress, 'get_terms_args_filter'), 10, 2);
			remove_filter('get_term', array($sitepress, 'get_term_adjust_id'), 1, 1);
			remove_filter('terms_clauses', array($sitepress, 'terms_clauses'), 10, 3);
		}

		foreach ($all_taxonomies as $taxonomy) {
			$term_ids = wp_get_object_terms(
				$product->get_id(),
				$taxonomy,
				['fields' => 'ids']
			);

			if (empty($term_ids)) {
				continue;
			}

			if (is_taxonomy_hierarchical($taxonomy)) {
				$full_term_ids = [];

				foreach ($term_ids as $term_id) {
					$full_term_ids[] = $term_id;

					$full_term_ids = array_merge(
						$full_term_ids,
						$this->get_parents_for_term($taxonomy, $term_id)
					);
				}

				$term_ids = array_unique($full_term_ids);
			}

			foreach ($term_ids as $term_id) {
				$this->insert_lookup_table_data(
					$product->get_id(),
					$taxonomy,
					$term_id
				);
			}
		}

		if ($sitepress) {
			add_filter('terms_clauses', array($sitepress, 'terms_clauses'), 10, 3);
			add_filter('get_term', array($sitepress, 'get_term_adjust_id'), 1, 1);
			add_filter('get_terms_args', array($sitepress, 'get_terms_args_filter' ), 10, 2);
		}
	}

	private function insert_lookup_table_data(int $product_id, string $taxonomy, int $term_id) {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				'INSERT INTO ' . $this->lookup_table_name . ' (
					product_id,
					taxonomy,
					term_id)
					VALUES
					(%d, %s, %d)',
				$product_id,
				$taxonomy,
				$term_id
			)
		);
	}

	private function get_parents_for_term($taxonomy, $term_id) {
		$parents = [];

		while (isset($this->get_hierarchy_children_first($taxonomy)[$term_id])) {
			$term_id = $this->get_hierarchy_children_first($taxonomy)[$term_id];
			$parents[] = $term_id;
		}

		return $parents;
	}

	private function get_hierarchy_children_first($taxonomy) {
		if (isset($this->hierarchy_children_first_cache[$taxonomy])) {
			return $this->hierarchy_children_first_cache[$taxonomy];
		}

		$term_hierarchy = _get_term_hierarchy($taxonomy);

		$hierarchy_children_first = [];

		foreach ($term_hierarchy as $parent_id => $children) {
			foreach ($children as $child) {
				$hierarchy_children_first[$child] = $parent_id;
			}
		}

		$this->hierarchy_children_first_cache[
			$taxonomy
		] = $hierarchy_children_first;

		return $hierarchy_children_first;
	}
}

