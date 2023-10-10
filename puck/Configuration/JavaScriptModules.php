<?php

return [
    'dependencies' => ['core', 'backend'],
    'tags' => [
        'backend.form',
    ],
    'imports' => [
        '@ubos/puck/' => 'EXT:puck/Resources/Public/js/',
        '@ubos/ckeditor-icons' => 'EXT:puck/Resources/Public/CkEditorPlugins/Icons/Icons.js',
        '@ubos/ckeditor-icons-puck' => 'EXT:puck/Resources/Public/CkEditorPlugins/IconsPuck.js',
    ],
];
