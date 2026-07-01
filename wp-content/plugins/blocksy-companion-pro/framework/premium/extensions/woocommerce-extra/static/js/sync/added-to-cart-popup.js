import {
	setRatioFor,
	responsiveClassesFor,
	getOptionFor,
} from 'blocksy-customizer-sync'

document.addEventListener('DOMContentLoaded', () => {
	if (
		!window.wc_add_to_cart_params ||
		!window.wc_add_to_cart_params.wc_ajax_url
	) {
		return
	}

	window.wc_add_to_cart_params.wc_ajax_url += '&wp_customize=on'
})

const elements = {
	added_to_cart_popup_show_price: '.ct-added-to-cart-product .price',
	added_to_cart_popup_show_description:
		'.ct-added-to-cart-product .ct-product-description',
	added_to_cart_popup_show_attributes:
		'.ct-added-to-cart-product .ct-product-attributes',
	added_to_cart_popup_show_shipping:
		'.ct-added-to-cart-product .ct-added-to-cart-popup-shipping',
	added_to_cart_popup_show_tax:
		'.ct-added-to-cart-product .ct-added-to-cart-popup-tax',
	added_to_cart_popup_show_total:
		'.ct-added-to-cart-product .ct-added-to-cart-popup-total',
	added_to_cart_popup_show_cart: '.ct-added-to-cart-popup-cart',
	added_to_cart_popup_show_continue: '.ct-added-to-cart-popup-continue',
	added_to_cart_popup_show_checkout: '.ct-added-to-cart-popup-checkout',
	added_to_cart_popup_suggested_products: '.ct-suggested-products ',
}

const handleTotalsVisibility = (popup) => {
	const show_shipping = getOptionFor('added_to_cart_popup_show_shipping')
	const show_tax = getOptionFor('added_to_cart_popup_show_tax')
	const show_total = getOptionFor('added_to_cart_popup_show_total')

	const totals = popup.querySelector('.ct-product-totals')

	if (!totals) {
		return
	}

	totals.hidden =
		show_shipping === 'no' && show_tax === 'no' && show_total === 'no'
}

const handleActionsVisibility = (popup) => {
	const show_cart = getOptionFor('added_to_cart_popup_show_cart')
	const show_continue = getOptionFor('added_to_cart_popup_show_continue')
	const show_checkout = getOptionFor('added_to_cart_popup_show_checkout')

	const actions = popup.querySelector('.ct-popup-actions')

	if (!actions) {
		return
	}

	const hideActions =
		show_cart === 'no' && show_continue === 'no' && show_checkout === 'no'

	if (hideActions) {
		actions.style.display = 'none'
	} else {
		actions.removeAttribute('style')
	}
}

const handleImage = (popup) => {
	const ratio = getOptionFor('added_to_cart_popup_image_ratio')

	const el = popup.querySelector(
		'.ct-added-to-cart-product .ct-media-container'
	)
	const showImage = getOptionFor('added_to_cart_popup_show_image')

	const imageContainer = popup.querySelector('.ct-added-to-cart-product')

	if (!el) {
		return
	}

	if (showImage === 'yes') {
		el.removeAttribute('style')
		imageContainer.classList.remove('no-image')
	} else {
		el.style.display = 'none'
		imageContainer.classList.add('no-image')
	}

	setRatioFor({
		ratio,
		el,
	})
}

const handleSuggestedProducts = (popup) => {
	const el = popup.querySelector('.ct-suggested-products')

	if (!el) {
		return
	}

	responsiveClassesFor('suggested_products_visibility', el)

	const flexyItems = popup.querySelector('.flexy-items')

	const productsType = getOptionFor('added_to_cart_popup_products_type')

	if (flexyItems) {
		flexyItems.dataset.products = productsType
	}

	;[...flexyItems.querySelectorAll('.ct-media-container')].map((image) => {
		const woo_card_layout = getOptionFor('woo_card_layout')

		const productImage = woo_card_layout.find(
			({ id }) => id === 'product_image'
		)

		const archiveRatio = '3/4'

		if (productImage && productImage['ratio']) {
			archiveRatio = productImage['ratio']
		}

		const ratio = productsType === 'inline' ? '1/1' : archiveRatio

		setRatioFor({
			ratio,
			el: image,
		})
	})
}

const renderAddedToCartPopup = (popup = null) => {
	if (!popup) {
		popup = document.querySelector('#ct-added-to-cart-popup')
	}

	if (!popup) {
		return
	}

	const position = getOptionFor('added_to_cart_popup_position')
	const size = getOptionFor('added_to_cart_popup_size')

	Object.keys(elements).map((key) => {
		const el = popup.querySelector(elements[key])

		if (!el) {
			return
		}

		const value = getOptionFor(key)

		if (value === 'no') {
			el.style.display = 'none'
		} else {
			el.removeAttribute('style')
		}
	})

	popup.dataset.popupPosition = position
	popup.dataset.popupSize = size

	handleTotalsVisibility(popup)
	handleActionsVisibility(popup)
	handleImage(popup)
	handleSuggestedProducts(popup)
}

export const mountAddedToCartPopupSync = () => {
	ctEvents.on('ct:sync:added-to-cart-popup', (data) => {
		renderAddedToCartPopup(data.popup)
	})

	wp.customize.bind('change', (e) => {
		if (e.id.indexOf('added_to_cart_popup') !== 0) {
			return
		}

		renderAddedToCartPopup()
	})
}
