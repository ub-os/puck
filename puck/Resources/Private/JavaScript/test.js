
const host = document.getElementById('host')
window.test = {}


window.test.addRemote = () => {
    console.log('add remote')
    const remote = document.createElement('div')
    remote.setAttribute('data-connect', 'test.remote#host')
    document.body.appendChild(remote)
}

window.test.addDescendant = () => {
    console.log('add descendant')
    const descendant = document.createElement('div')
    descendant.setAttribute('data-connect', 'test.descendant')
    host.appendChild(descendant)
}

window.test.removeRemote = () => {
    console.log('remove remote')
    const remote = document.querySelector('[data-connect="test.remote#host"]')
    remote.remove()
}

window.test.removeDescendant = () => {
    console.log('remove descendant')
    const descendant = document.querySelector('[data-connect="test.descendant"]')
    descendant.remove()
}

window.test.removeHost = () => {
    console.log('remove host')
    host.remove()
}

window.test.addHost = () => {
    console.log('add host')
    document.body.appendChild(host)
}

window.test.removeHostConnectToken = () => {
    console.log('remove host connect token')
    host.setAttribute('data-connect', '')
}

window.test.addHostConnectToken = () => {
    console.log('add host connect token')
    host.setAttribute('data-connect', 'test')
}

window.test.aspect = () => host.nxs_tm_host?.get('test')

