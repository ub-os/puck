import {$, $$, jsx} from '../General/Aliases';

$$('[data-video-embed]').forEach(node => {
    const data = JSON.parse(node.dataset.videoEmbed);
    let src = '';
    switch (data.service) {
        case 'youtube':
            src = `https://www.youtube-nocookie.com/embed/${data.id}?autohide=1&controls=1&enablejsapi=1`;
            break;
        case 'vimeo':
            src = `https://player.vimeo.com/video/${data.id}?h=70f64fa69b&title=0&byline=0&portrait=0`;
    }
    node.addEventListener('click', () => {
        node.appendChild(
            <iframe
                src={src}
                width={data.width}
                class={'e-media-embed__iframe'}
                allowfullscreen={true}
            />
        );
    });
});

