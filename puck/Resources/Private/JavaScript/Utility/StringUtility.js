function kebabCase(string) {
    const upper = /(?<!\p{Uppercase_Letter})\p{Uppercase_Letter}|\p{Uppercase_Letter}(?!\p{Uppercase_Letter})/gu;
    return string.replace(upper, "-$&").replace(/^-/, "").toLowerCase();
}

function camelCase(string) {
    return string.replace(/-([a-z])/g, function (g) {
        return g[1].toUpperCase();
    });
}

function jsonParse(val) {
    let obj = {}
    try {
        obj = JSON.parse(val || '{}')
    } catch (e) {
        return {}
    }
    return obj
}

export {
    kebabCase,
    camelCase,
    jsonParse
}