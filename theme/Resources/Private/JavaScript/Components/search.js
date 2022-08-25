import {$} from "../General/Aliases";

const el = {
    submit: $('[data-search-el="submit"]'),
    input: $('[data-search-el="input"]'),
    string: ''
};
if (el.submit && el.input) {
    el.input.on('change',e => {
        el.string = el.input.value;
        if (el.string.length > 2) {
            el.submit.classList.remove('-disabled');
        } else {
            el.submit.classList.add('-disabled');
        }
    });

    el.submit.on('click', e => {
        e.preventDefault();
        if ($('html').lang.substring(0,2) == 'en') {
            window.location = "/en/search/s/"+el.string;
        } else {
            window.location = "/suche/s/"+el.string;
        }
    });
}

