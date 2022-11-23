import {$, $$, jsx} from '../General/Aliases';

$$('.powermail_morestep').forEach(node => {
    const el = {
        form: node,
        formId: node.id,
        pages: node.$$('[data-powermail-page]'),
        to_page_links: $$('[data-powermail-to-page]'),
        active_page: null,
        active_page_link: null,
        active_page_class: '-active'
    };
    function getPageNodeById(id) {
        return el.form.$(`[data-powermail-page="${id}"]`);
    }
    function getPageHashById(id) {
        return el.formId+'-page-'+id;
    }
    function getPageIdByHash(hash) {
        return hash.replace('#'+el.formId+'-page-', '');
    }
    function goToPage(id) {
        let page = getPageNodeById(id);
        if (page) {
            let page_link = $(`[data-powermail-to-page="${id}"]`);
            if (el.active_page && el.active_page_link) {
                el.active_page.classList.remove(el.active_page_class);
                el.active_page_link.classList.remove(el.active_page_class);
            }
            page.classList.add(el.active_page_class);
            page_link.classList.add(el.active_page_class);
            el.active_page = page;
            el.active_page_link = page_link;
            if (id>1){
                el.form.scrollIntoView();
            }
        }
    }
    goToPage(1);
    window.location.hash = getPageHashById(1);
    //goToPage(getPageIdByHash(window.location.hash));
    window.onhashchange = function() {
        if (window.location.hash.includes(el.formId+'-page-')){
            goToPage(getPageIdByHash(window.location.hash));
        }
        if (!window.location.hash.length) {
            //goToPage(1);
        }
    };
    el.to_page_links.forEach(link => {
        let id = link.getAttribute('data-powermail-to-page');
        link.on('click', function() {
            if (link.hasAttribute('data-powermail-validate-fieldset')) {
                let valid = true;
                el.active_page.$$('[required]').forEach(input => {
                    if (!input.checkValidity()) {
                        valid = false;
                    }
                });
                if (valid) {
                    window.location.hash = getPageHashById(id);
                } else {
                    el.form.reportValidity();
                }
            } else {
                window.location.hash = getPageHashById(id);
            }
        });
    });
});