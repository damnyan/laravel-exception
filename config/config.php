<?php

return [
    'default_group' => 'group1',
    'prefix' => 'api',
    'references' => [
        'group1' => [
            'field' => [
                'name1',
                'name2',
            ]
        ],
    ]
];
