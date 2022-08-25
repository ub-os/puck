import {$, $$} from '../General/Aliases';

function wrap(el, wrapper) {
  el.parentNode.insertBefore(wrapper, el);
  wrapper.appendChild(el);
}

$$('[data-rich]').forEach(el => {
  el.$$('a').forEach( a => {
    a.classList.add( location.hostname === a.hostname || !a.hostname.length ? '-local' : '-external' );
  });
  el.$$('table').forEach(table => {
    const wrapper = document.createElement('div');
    wrapper.classList.add('table-wrapper');
    wrap(table, wrapper);
  });
});
