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
            'params' => [
                'layout_path'       => 'main-layout',
                'max_nesting_depth' => 10,
            ],
        ],
        'asset-manager' => [
            'class' => \DiplomaProject\Core\Modules\AssetManager::class,
            'params' => [
                'path_to_assets' => 'assets',
            ],
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
                'error_controller' => \DiplomaProject\Controllers\Errors::class,
                'controller_map' => [
                    'authentication' => \DiplomaProject\Controllers\Authentication::class,
                    'login'          => \DiplomaProject\Controllers\Authentication::class,
                    'admin-panel'    => \DiplomaProject\Controllers\AdminPanel::class,
                ],
            ],
        ],
    ]
];
