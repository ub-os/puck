let Plyr = class {}
//import Plyr from 'plyr'
import { $, $$, jsx } from '~/General/Aliases'
import Listeners from "~/Classes/Listeners"
import AbstractBehavior from "~/Classes/Behaviors/AbstractBehavior"

export default class MediaPlayer extends AbstractBehavior {
    static props = {
        provider: 'mp4',
        width: 1920,
        aspectRatio: 16 / 9,
        embedId: '',
        filePath: '',
        poster: '',
        lazyLoad: true,
        // if you want to use Plyr, set usePlyr to true and uncomment the Plyr import above and comment the empty Plyr definition
        /// also uncomment the css import in Resources/Private/Stylesheets/styles.sass
        usePlyr: false,
        controls: true,
        options: {}
    }
    addPlayerEl() {
        if (!this.playerElAdded) {
            switch (this.provider) {
                case 'youtube':
                    this.playerEl = this.getYoutubePlayerEl()
                    break
                case 'vimeo':
                    this.playerEl = this.getVimeoPlayerEl()
                    break
                default:
                    this.playerEl = this.getHtml5PlayerEl()
            }
            this.el.append(this.playerEl)
            this.playerElAdded = true
            if (this.usePlyr) {
                if (this.options.controls === true || this.options.controls === 1) {
                    this.options.controls = this.plyrDefaultControls
                }
                this.plyr = new Plyr(this.playerEl, this.options)
            }
        }
    }
    getHtml5PlayerEl() {
        const el = (
            <video
                id={`${this.el.id}-video`}
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
                el.setAttribute(option, this.options[option])
            }
        }
        return el
    }

    getYoutubePlayerEl() {
        return (
            <div style={!this.usePlyr ? `padding-top: ${100 / this.aspectRatio}%;` : ''}>
                <iframe
                    style={'position: absolute; top: 0; left: 0; width: 100%; height: 100%;'}
                    id={`${this.el.id}-iframe`}
                    src={`https://www.youtube-nocookie.com/embed/${this.embedId}?autohide=1&controls=${this.options.controls}&enablejsapi=1`}
                    width={this.width}
                    allowFullScreen={true}
                />
            </div>
        )
    }

    getVimeoPlayerEl() {
        return (
            <div style={!this.usePlyr ? `padding-top: ${100 / this.aspectRatio}%;` : ''}>
                <iframe
                    style={'position: absolute; top: 0; left: 0; width: 100%; height: 100%;'}
                    id={`${this.el.id}-iframe`}
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
        this.playerElAdded = false
        this.toggles = document.querySelectorAll(`[aria-controls="${this.el.id}"], [data-controls="${this.el.id}"]`)
        this.listeners = new Listeners()
        this.options = {
            controls: this.controls,
            ...this.options
        }
        if (this.lazyLoad) {
            this.toggles.forEach(toggle => {
                this.listeners.add(toggle, 'click', e => {
                    this.addPlayerEl()
                })
            })
        } else {
            this.addPlayerEl()
        }
        return this
    }

    destroy() {
        if (this.listeners) {
            this.listeners.destroy()
        }
        if (this.plyr?.destroy) {
            this.plyr.destroy()
        }
    }
}

