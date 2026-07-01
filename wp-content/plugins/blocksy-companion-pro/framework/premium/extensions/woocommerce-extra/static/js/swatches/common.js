import { handleOutofStock } from './out-of-stock'

const shareNetworks = {
	facebook: 'u',
	twitter: 'url',
	// pinterest: '',
	linkedin: 'url',
	reddit: 'url',
	hacker_news: 'u',
	vk: 'url',
	ok: 'st.shareUrl',
	telegram: 'url',
	viber: 'text',
	whatsapp: 'text',
	flipboard: 'url',
	email: 'body',
	line: 'url',
}

export const computeSwatch = (el) => {
	;[...el.querySelectorAll('.ct-variation-swatches')].map((swatchesEl) => {
		const select = swatchesEl.querySelector('select')

		if (!select) {
			return
		}

		const maybeActive = swatchesEl.querySelector('.active')

		if (maybeActive) {
			maybeActive.classList.remove('active')

			const labelContainer = document.querySelector(
				`[for="${select.dataset.attribute_name.replace(
					'attribute_',
					''
				)}"]`
			)

			if (labelContainer) {
				labelContainer.textContent =
					labelContainer.textContent.split(':')?.[0].trim() ||
					labelContainer.textContent
			}
		}

		const selectValue = JSON.stringify(String(select.value))

		if (
			selectValue &&
			swatchesEl.querySelector(`[data-value=${selectValue}]`)
		) {
			const label = select.querySelector(
				`[value=${selectValue}]`
			).textContent

			const labelContainer = document.querySelector(
				`[for="${select.dataset.attribute_name.replace(
					'attribute_',
					''
				)}"]`
			)

			if (labelContainer) {
				labelContainer.textContent = `${
					labelContainer.textContent.split(':')?.[0].trim() ||
					labelContainer.textContent
				}: ${label}`
			}

			swatchesEl
				.querySelector(`[data-value=${selectValue}]`)
				.classList.add('active')
		}

		const withUrlGeneration = el.closest('.ct-has-swatches-url')

		const urlParams = new URLSearchParams(window.location.search)
		urlParams.delete(select.name)
		;[...swatchesEl.querySelectorAll('[data-value]')].map(
			(singleSwatchEl) => {
				singleSwatchEl.classList.remove('active')

				const swatchValue = JSON.stringify(
					String(singleSwatchEl.dataset.value)
				)

				if (swatchValue === selectValue) {
					singleSwatchEl.classList.add('active')

					urlParams.set(select.name, JSON.parse(selectValue))
				}
			}
		)

		handleOutofStock(el)

		if (!withUrlGeneration) {
			return
		}

		if (!urlParams.toString()) {
			window.history.replaceState({}, '', window.location.pathname)
			return
		}

		window.history.replaceState(
			{},
			'',
			`${window.location.pathname}?${urlParams.toString()}`
		)

		const shareButtons = document.querySelectorAll('.ct-share-box a')
		shareButtons.forEach((button) => {
			const { network } = button.dataset

			if (network && shareNetworks?.[network]) {
				const url = new URL(button.href)

				if (url.searchParams.has(shareNetworks[network])) {
					url.searchParams.set(
						shareNetworks[network],
						window.location.href
					)
				}

				button.href = url.toString()
			}
		})
	})
}
