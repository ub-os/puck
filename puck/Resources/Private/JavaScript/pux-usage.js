
// create pux link element
import Link from "~/Classes/Behaviors/Link.js";
import {$} from "~/General/Aliases.js";

const puxLink = document.createElement('pux-link')
puxLink.setAttribute('to', 'https://www.google.com',)
puxLink.innerText = 'pux link element'

console.dir(puxLink)

// create pux link element with jsx
const puxLinkJsx = <pux-link to={'https://www.google.com'}>pux link element jsx</pux-link>

// create generic pux element with link mixin via use-link attribute
const puxElWithUseAttr = <pux-el use-link={JSON.stringify({to: 'https://www.google.com'})}>pux element jsx with use link</pux-el>

// create generic pux element with link mixin via use function
const puxElWithAddedMixin = <pux-el>pux element with added mixin</pux-el>
puxElWithAddedMixin.use('link', {to: 'https://www.google.com'})

// create div element with link behavior by manually attaching mixin class
const divLink = <div>div element with attached mixin class</div>
const puxLinkOnDiv = new Link(divLink, {to: 'https://www.google.com'}).mount()

$('main').append(puxLink)
$('main').append(puxLinkJsx)
$('main').append(puxElWithUseAttr)
$('main').append(puxElWithAddedMixin)
$('main').append(divLink)

// disable pux link core functionality
puxLink.destroyCore()

// enable pux link core functionality (is done automatically when added to dom)
puxLink.mountCore()

// disable all pux element mixins
puxElWithAddedMixin.destroyMixins()

// enable all pux element mixins (is done automatically when added to dom)
puxElWithAddedMixin.mountMixins()

// disable pux element link mixin
puxElWithAddedMixin.mixins.link.destroy()

// enable pux element link mixin (is done automatically when added to dom, or when use attribute is added)
puxElWithAddedMixin.mixins.link.mount()

// disable pux element all mixins and core functionality
puxElWithAddedMixin.destroy()

// enable pux element all mixins and core functionality (is done automatically when added to dom)
puxElWithAddedMixin.mount()
