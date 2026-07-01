import { setRatioFor } from 'blocksy-customizer-sync'

export const mountCompareSync = () => {
	wp.customize('product_compare_layout', (val) => {
		val.bind((to) => {
			to.forEach((layout) => {
				if (layout['id'] === 'product_main') {
					const images = document.querySelectorAll(
						'.ct-compare-column .thumb_class'
					)

					images.forEach((el) => {
						setRatioFor({
							ratio: layout['compare_image_ratio'],
							el,
						})
					})
				}
			})
		})
	})
}

export const collectVariablesForCompareLayers = (v) => {
	let variables = []
	v.map((layer) => {
		let selectorsMap = {
			product_brands: '.ct-compare-column > .ct-product-brands',
		}

		if (layer['id'] === 'product_brands') {
			variables = [
				...variables,

				{
					selector: selectorsMap[layer.id],
					variable: 'product-brand-logo-size',
					responsive: true,
					unit: 'px',
					extractValue: () => {
						return layer.brand_logo_size || 60
					},
				},

				{
					selector: selectorsMap[layer.id],
					variable: 'product-brands-gap',
					responsive: true,
					unit: 'px',
					extractValue: () => {
						return layer.brand_logo_gap || 10
					},
				},
			]
		}
	})

	return variables
}
