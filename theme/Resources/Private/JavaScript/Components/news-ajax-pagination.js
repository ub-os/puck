import {$, $$, jsx} from '../General/Aliases';

$$('[id^="news-container"] .page-navigation a').forEach((el) => {
  paginationLink(el)
});

function paginationLink(node) {
  const ajaxUrl = node.getAttribute('data-link');
  const container = 'news-container-' + node.getAttribute('data-container');
  node.onclick = function(e) {
    if (ajaxUrl !== undefined && ajaxUrl !== '') {
      e.preventDefault();
      fetch(ajaxUrl).then(function(response) {
        return response.text().then(function(text) {
          const el = $(`#${container}`);
          const height = el.getBoundingClientRect().top + document.documentElement.scrollTop - 48;
          el.outerHTML = text;
          window.scrollTo({
            top: height,
            behavior: 'smooth',
            left: 0,
          })
          $$(`#${container} .page-navigation a`).forEach((link) => {
            paginationLink(link)
          });
        });
      });
    }
  };
}

