import {$, $$} from '../General/Aliases';

$$('[data-video-embed]').forEach(node => {
    const service = node.getAttribute("data-video-embed");
    const iframe = document.createElement("iframe");
    const embedId = node.getAttribute("data-embed-id");
    const width = node.getAttribute("data-width");
    switch (service) {
        case 'youtube':
            iframe.src = `https://www.youtube-nocookie.com/embed/${embedId}?autohide=1&controls=1&enablejsapi=1`;
            break;
        case 'vimeo':
            iframe.src = `https://player.vimeo.com/video/${embedId}?h=70f64fa69b&title=0&byline=0&portrait=0`;
    }
    iframe.width = width;
    iframe.classList.add('e-media-embed__iframe');
    iframe.setAttribute('allowfullscreen', 'true');
    node.addEventListener('click', () => {
        node.appendChild(iframe);
    });
});
