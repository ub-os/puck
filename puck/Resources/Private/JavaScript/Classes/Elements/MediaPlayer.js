import Modal from "./Modal.js";

let Plyr = class {}
//import Plyr from 'plyr';
import PropElement from "./PropElement.js";
import Listeners from "../Listeners";

export default class MediaPlayer extends PropElement {
    static props = {
        provider: 'mp4',
        width: 1920,
        aspectRatio: 16 / 9,
        embedId: '',
        filePath: '',
        poster: '',
        modal: true,
        lazyLoad: true,
        // if you want to use Plyr, set usePlyr to true and uncomment the Plyr import above and comment the empty Plyr definition
        /// also uncomment the css import in Resources/Private/Stylesheets/styles.sass
        usePlyr: false,
        controls: true,
        options: {}
    }
    constructor() {
        super()
    }

    addPlayerElement() {
        if (!this.playerElementAdded) {
            switch (this.provider) {
                case 'youtube':
                    this.playerElement = this.getYoutubePlayerElement()
                    break
                case 'vimeo':
                    this.playerElement = this.getVimeoPlayerElement()
                    break
                default:
                    this.playerElement = this.getHtml5PlayerElement()
            }
            this.append(this.playerElement)
            this.playerElementAdded = true
            if (this.usePlyr) {
                if (this.options.controls === true || this.options.controls === 1) {
                    this.options.controls = this.plyrDefaultControls
                }
                this.plyr = new Plyr(this.playerElement, this.options)
            }
        }
    }

    getHtml5PlayerElement() {
        const element = (
            <video
                id={`${this.id}-video`}
                tabindex={'0'}
                data-poster={this.poster+''} >
                <source
                    src={this.filePath}
                    type={'video/' + this.provider}
                    width={this.width} />
            </video>
        )
        for (let option of ['controls', 'playsinline', 'autoplay', 'loop', 'muted']) {
            if (this.options[option]) {
                element.setAttribute(option, this.options[option])
            }
        }
        return element
    }

    getYoutubePlayerElement() {
        return (
            <div style={!this.usePlyr ? `padding-top: ${100 / this.iframeAspectRatio}%;` : ''}>
                <iframe
                    style={'position: absolute; top: 0; left: 0; width: 100%; height: 100%;'}
                    id={`${this.id}-iframe`}
                    src={`https://www.youtube-nocookie.com/embed/${this.embedId}?autohide=1&controls=${this.options.controls}&enablejsapi=1`}
                    width={this.width}
                    allowFullScreen={true}
                />
            </div>
        )
    }

    getVimeoPlayerElement() {
        return (
            <div style={!this.usePlyr ? `padding-top: ${100 / this.iframeAspectRatio}%;` : ''}>
                <iframe
                    style={'position: absolute; top: 0; left: 0; width: 100%; height: 100%;'}
                    id={`${this.id}-iframe`}
                    src={`https://player.vimeo.com/video/${this.embedId}?h=70f64fa69b&title=0&byline=0&portrait=0`}
                    width={this.width}
                    allowfullscreen={true}
                />
            </div>
        )
    }

    mount() {
        super.mount()
        this.plyrDefaultControls = ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen']
        this.playerElementAdded = false
        this.toggles = document.querySelectorAll(`[aria-controls="${this.id}"], [data-controls="${this.id}"]`)
        this.options = {
            controls: this.controls,
            ...this.options
        }
        this.listeners = new Listeners()
        if (this.lazyLoad || this.modal) {
            this.toggles.forEach(toggle => {
                console.log(toggle)
                this.listeners.add(toggle, 'click', e => {
                    //e.preventDefault()
                    console.log('media toggle clicked')
                    this.addPlayerElement()
                })
            })
        } else {
            this.addPlayerElement()
        }
        return this
    }

    destroy() {
        if (this.listeners) {
            this.listeners.destroy()
        }
        if (this.plyr) {
            this.plyr.destroy()
        }
        console.log('destroying media player')
    }
}

window.customElements.define('pux-media-player', MediaPlayer);
