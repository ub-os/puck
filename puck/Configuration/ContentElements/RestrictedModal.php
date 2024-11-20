<?php

return (include __DIR__ . '/Modal.php')->makeRestrictedChildElement(
    'restricted_modal',
    label: 'Restricted Modal',
    description: 'Restricted Modal desc new',
    sorting: 101,
    valueOverrides: [
        'bodytext' => 'pleasework',
    ]
);