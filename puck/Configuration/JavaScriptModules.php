<?php

return [
    'dependencies' => ['core', 'backend'],
    'tags' => [
        'backend.form',
    ],
    'imports' => [
        '@ubos/puck/' => 'EXT:puck/Resources/Public/JavaScript/',
        '@ubos/ckeditor-puck-icons' => 'EXT:puck/Resources/Public/CkEditorPlugins/PuckIcons.js',
    ],
];
