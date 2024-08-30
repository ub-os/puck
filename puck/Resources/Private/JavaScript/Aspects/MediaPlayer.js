let Plyr = class {}
//import Plyr from 'plyr'
import { $, $$, jsx } from '~/Utility/DomUtility'
import { Aspect } from "~/_nexus"

const plyrDefaultControls = ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen']
export default class MediaPlayer extends Aspect {
    static attributes = {
        provider: 'mp4',
        width: 1920,
        aspectRatio: 16 / 9,
        embedId: '',
        src: '',
        poster: '',
        lazyLoad: true,
        loaded: false,
        // if you want to use Plyr, set usePlyr to true and uncomment the Plyr import above and comment the empty Plyr definition
        /// also uncomment the css import in Resources/Private/Stylesheets/puck.sass
        usePlyr: false,
        controls: true,
        videoAttributes: '',
        plyrOptions: {}
    }

    load() {
        if (this.loaded) return
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
        this.loaded = true
        if (this.usePlyr) {
            let plyrOptions = this.plyrOptions
            if (this.controls) {
                plyrOptions = {
                    controls: plyrDefaultControls,
                    ...plyrOptions
                }
            }
            this.plyr = new Plyr(this.playerEl, plyrOptions)
        }
    }

    getHtml5PlayerEl() {
        const attributes = {}
        this.videoAttributes.split(' ').forEach(attr => {
            if (attr) attributes[attr] = ''
        })
        return (
            <div style={!this.usePlyr ? `padding-top: ${100 / this.aspectRatio}%;` : ''}>
                <video
                    style={'position: absolute; top: 0; left: 0; width: 100%; height: 100%;'}
                    id={`${this.el.id}-video`}
                    tabindex={'0'}
                    data-poster={this.poster}
                    {...attributes}>
                    <source
                        src={this.src}
                        type={'video/' + this.provider}
                        width={this.width} />
                </video>
            </div>
        )
    }

    getYoutubePlayerEl() {
        return (
            <div style={!this.usePlyr ? `padding-top: ${100 / this.aspectRatio}%;` : ''}>
                <iframe
                    style={'position: absolute; top: 0; left: 0; width: 100%; height: 100%;'}
                    id={`${this.el.id}-iframe`}
                    src={`https://www.youtube-nocookie.com/embed/${this.embedId}?autohide=1&controls=${this.controls}&enablejsapi=1`}
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

    initialized() {
        if (this.controls) this.videoAttributes += ' controls'
    }

    connected() {
        if (!this.lazyLoad) this.load()
        return this
    }

    disconnected() {
        if (this.plyr?.destroy) {
            this.plyr.destroy()
        }
    }
}

