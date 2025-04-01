function minimizeContentPreview() {
	console.log('minimize content preview script loaded')
	const template = document.getElementById('ubos-minimize-content-preview-button-template')
	const pageColumnHeaders = document.querySelectorAll('.t3-page-column-header-icons')
	pageColumnHeaders.forEach(header => {
		const button = template.content.cloneNode(true).firstElementChild
		const tbody = header.closest('tbody')
		console.log(tbody)
		header.prepend(button)
		console.log(button)
		button.addEventListener('click', () => {
			console.log(button)
			button.dataset.minimized = button.dataset.minimized === '0' ? '1' : '0'
			tbody.querySelectorAll('.t3-page-ce-element').forEach(element => {
				console.log(element)
				if (button.dataset.minimized === '1') {
					element.classList.add('-minimized-preview')
				} else {
					element.classList.remove('-minimized-preview')
				}
			})
			tbody.querySelectorAll('[data-minimized]').forEach(subButton => {
				subButton.dataset.minimized = button.dataset.minimized
			})
		})
	})
}

minimizeContentPreview()
