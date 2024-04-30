<?php

return [
    'default' => [
        'db' => [
            'class' => \DiplomaProject\Core\Modules\DataBase::class,
            'params' => [
                'hostname' => 'localhost', // 127.0.0.1
                'username' => 'diploma_project_admin',
                'password' => '123',
                'database' => 'diploma_project_db',
            ]
        ],
        'http' => [
            'class' => \DiplomaProject\Core\Modules\Http::class,
            'params' => [
                'base_url' => $_SERVER['SERVER_NAME'],
            ]
        ],
        'viewer' => [
            'class' => \DiplomaProject\Core\Modules\Viewer::class,
        ],
        'security' => [
            'class' => \DiplomaProject\Core\Modules\Security::class,
            'params' => [
                'salt' => 'lsduDfR5gviY4ad27u6sfh',
            ],
        ],
        'authentication' => [
            'class' => \DiplomaProject\Core\Modules\Authentication::class,
            'params' => [
                'user_class' => \DiplomaProject\Models\User::class,
                'token_lifetime' => 20,
            ],
        ],
        'router' => [
            'class' => \DiplomaProject\Core\Modules\Router::class,
            'params' => [
                'controller_map' => [
                    'authentication' => \DiplomaProject\Controllers\Authentication::class,
                    'login' => \DiplomaProject\Controllers\Authentication::class,
                ],
            ],
        ],
    ]
];
