function extendClass(base, extension, exclude = ['length'], protoExclude = []) {
	const names = getAllPropertyNames(extension)
	const protoNames = getAllPropertyNames(extension.prototype)
	names.forEach(name => {
		if (exclude.includes(name)) return
		base[name] = extension[name]
	})
	protoNames.forEach(name => {
		if (protoExclude.includes(name)) return
		base.prototype[name] = extension.prototype[name]
	})
	return base
}

function mixClass(options, ...classes) {
	const base = class {}
	classes.forEach(extension => {
		extendClass(base, extension, options.exclude ?? ['length'], options.protoExclude ?? [])
	})
	return base
}

function getAllPropertyNames(object) {
	let obj = object
	const names = Object.getOwnPropertyNames(obj)
	while (
		Object.getPrototypeOf(obj) &&
		Object.getPrototypeOf(obj).name !== '' &&
		Object.getPrototypeOf(obj).constructor.name !== 'Object'
	) {
		const superNames = Object.getOwnPropertyNames(Object.getPrototypeOf(obj))
		names.push(...superNames)
		obj = Object.getPrototypeOf(obj)
	}
	return [...new Set(names)]
}

function throttle(f, delay) {
	let timer = 0
	return function (...args) {
		clearTimeout(timer)
		timer = setTimeout(() => f.apply(this, args), delay)
	}
}

export { extendClass, mixClass, getAllPropertyNames, throttle }
