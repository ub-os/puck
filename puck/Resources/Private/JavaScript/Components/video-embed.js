import { $, $$, jsx } from '../General/Aliases';

$$('[data-video-embed]').forEach(element => {
    const data = JSON.parse(element.dataset.videoEmbed);
    const id = element.getAttribute('aria-controls');
    let src = '';
    let appended = false;
    switch (data.service) {
        case 'youtube':
            src = `https://www.youtube-nocookie.com/embed/${data.id}?autohide=1&controls=1&enablejsapi=1`;
            break;
        case 'vimeo':
            src = `https://player.vimeo.com/video/${data.id}?h=70f64fa69b&title=0&byline=0&portrait=0`;
    }
    element.addEventListener('click', () => {
        if (!appended) {
            appended = true
            document.getElementById(id).appendChild(
                <iframe
                    src={src}
                    width={data.width}
                    class={'e-media-embed__iframe'}
                    allowfullscreen={true}
                />
            );
        }
    });
});

