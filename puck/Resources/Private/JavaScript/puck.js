import Logger from '~/Service/Logger'

if (!window.puckApp) {
    import("~/exe.js").then((data) => {
        Logger.console.log('puck main executed', data)
    });
}