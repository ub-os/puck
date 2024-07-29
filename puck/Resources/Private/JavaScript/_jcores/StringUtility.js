function kebabCase(string) {
    const upper = /(?<!\p{Uppercase_Letter})\p{Uppercase_Letter}|\p{Uppercase_Letter}(?!\p{Uppercase_Letter})/gu;
    return string.replace(upper, "-$&").replace(/^-/, "").toLowerCase();
}

function camelCase(string) {
    return string.replace(/-([a-z])/g, function (g) {
        return g[1].toUpperCase();
    });
}

export {
    kebabCase,
    camelCase,
}