import {scrollTo} from '../General/Functions.js';
import Listeners from "./Listeners.js";

// CLASS SmoothHashLinks
//# smooth scroll to hash link targets instead of jumping
//# configurable vertical offset on scroll target for sticky header etc.

export default class LinkManager {
    windowLocation = document.puckApp?.location || window.location
    constructor({
        root = document.body,
        scrollOffset = 50,
        scrollTopOnCurrentLink = true,
        addLocalClass = true,
        addExternalClass = true,
    }) {
        Object.assign(this, {
            root,
            scrollOffset,
            scrollTopOnCurrentLink,
            addLocalClass,
            addExternalClass
        })
    }

    isCurrentLink(el) {
        return !el.hash && (el.href === this.windowLocation.href || el.href === this.windowLocation.pathname || el.dataset.hxGet === this.windowLocation.href || el.dataset.hxGet === this.windowLocation.pathname)
    }

    isCurrentHashLink(el) {
        return el.hash.substring(1).split('?')[0] && el.pathname === this.windowLocation.pathname
    }

    mount() {
        this.listeners = new Listeners();
        console.log('check location in LinkManager', this.windowLocation)
        this.root.querySelectorAll('a').forEach(el => {
            const isCurrentLink = this.isCurrentLink(el);
            const isCurrentHashLink = this.isCurrentHashLink(el);
            if (this.addLocalClass && el.hostname === this.windowLocation.hostname) {
                el.classList.add('-local');
            }
            if (this.addExternalClass && el.hostname !== this.windowLocation.hostname) {
                el.classList.add('-external');
            }
            // skip listeners for links with aria-controls attribute, they should be handled by other classes
            if (el.getAttribute('aria-controls')) return;
            if (this.scrollTopOnCurrentLink && isCurrentLink) {
                this.listeners.add(el, 'click', e => {
                    e.preventDefault();
                    window.scrollTo({
                        top: 0,
                        left: 0,
                        behavior: 'smooth'
                    });
                })
                return
            }
            if (isCurrentHashLink) {
                const id = el.hash.substring(1).split('?')[0];
                if (!id) return
                const anchor = document.getElementById(id);
                if (!anchor) return
                this.listeners.add(el, 'click', e => {
                    e.preventDefault();
                    if (anchor.hasAttribute('data-menu-anchor')) {
                        scrollTo(anchor.nextElementSibling, this.scrollOffset);
                    } else {
                        scrollTo(anchor, this.scrollOffset);
                    }
                    history.replaceState(history.state, '', el.href);
                })
            }
        })
        // scroll to hash id on page load
        window.requestAnimationFrame(() => {
            const id = this.windowLocation.hash.substring(1).split('?')[0];
            if (id) scrollTo(id, this.scrollOffset);
        });
        return this
    }

    destroy() {
        this.listeners.destroy()
    }
}
