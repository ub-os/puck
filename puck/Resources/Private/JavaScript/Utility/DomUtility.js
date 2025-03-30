
// jsx pragma method
const jsx = (tag, props, ...children) => {
	const element = document.createElement(tag)

	Object.entries(props || {}).forEach(([name, value]) => {
		if (name.startsWith('on') && name.toLowerCase() in window)
			element.addEventListener(name.toLowerCase().substring(2), value)
		else element.setAttribute(name, value.toString())
	})

	children.forEach(child => {
		element.appendChild(
			child.nodeName === undefined
				? document.createTextNode(child.toString())
				: child,
		)
	})
	return element
}

const noDragClick = (element, callbackFunc, delta = 6) => {
	let startX
	let startY
	const mouseDownHandler = event => {
		startX = event.pageX
		startY = event.pageY
	}
	const mouseUpHandler = event => {
		const diffX = Math.abs(event.pageX - startX)
		const diffY = Math.abs(event.pageY - startY)
		if (diffX < delta && diffY < delta) {
			callbackFunc(event)
		}
	}
	element.addEventListener('mousedown', mouseDownHandler)
	element.addEventListener('mouseup', mouseUpHandler)
	return {
		remove: () => {
			element.removeEventListener('mousedown', mouseDownHandler)
			element.removeEventListener('mouseup', mouseUpHandler)
		},
	}
}

const scrollTo = (target, offset = 0) => {
	if (target && getComputedStyle(target).position !== 'fixed') {
		const height =
			target.getBoundingClientRect().top +
			document.documentElement.scrollTop -
			offset
		window.scrollTo({
			top: height,
			left: 0,
			behavior: 'smooth',
		})
	}
}
const tryViewTransition = callback => {
	if (document.startViewTransition) {
		document.startViewTransition(callback)
	} else {
		callback()
	}
}

export {
	jsx,
	noDragClick,
	scrollTo,
	tryViewTransition,
}
