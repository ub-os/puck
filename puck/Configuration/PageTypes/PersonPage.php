<?php

return new \UBOS\Puck\PageTypeDefinition(
    16504,
    label: 'Person Page',
    icon: 'person_page',
    showItemAdditions: [
        [
            'page_persons',
            'after:--palette--;;title'
        ]
    ],
    columnsOverrides: []
);