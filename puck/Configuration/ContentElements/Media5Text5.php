<?php

return (include __DIR__ . '/Media.php')->makeRestrictedChildElement(
    'media_55',
    label: '5/5 Text/Media element',
    description: 'Layout: centered, text left 5 columns, media right 5 columns.',
    valueOverrides: [
        'container_width' => 10,
        'container_position' => 'center',
        'container_offset' => 0,
        'media_layout' => 'right',
        'text_column_width' => 5,
        'media_column_width' => 5,
        'row_justify' => 'left',
        'row_align' => 'start',
        'item_column_width' => 5,
    ]
);