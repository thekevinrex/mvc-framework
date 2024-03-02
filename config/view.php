<?php

return [

    'paths' => [
        resource_path('views/'),
    ],

    'component_path' => 'resources/views/components',

    'compiled_path' => realpath(
        storage_path('views/')
    ),

    'compiled_extension' => 'php',

    'rootFile' => 'root',
];
