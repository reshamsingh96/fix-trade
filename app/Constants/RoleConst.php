<?php

namespace App\Constants;

class RoleConst
{
    # Roles TODO: Make new Role add slug and make permission 
    const SUPER_ADMIN = 'Super Admin';
    const ADMIN = "Admin";
    const USER = "User";
    const SERVICE_PROVIDER = "Service Provider";

    const SLUG_SUPER_ADMIN = 'super-admin';
    const SLUG_ADMIN = "admin";
    const SLUG_USER = "user";
    const SLUG_SERVICE_PROVIDER = "Service-provider";

    const SUPER_ADMIN_MESSAGE = "Super Admin Role Permission Not Update!";
    const ADMIN_PERMISSION = [];
    const USER_PERMISSION = [];
    const SERVICE_PROVIDER_PERMISSION = [];
    const ROLE_LIST = [
        ['name' => self::SUPER_ADMIN, "slug" => self::SLUG_SUPER_ADMIN, 'description' => 'Full access to all system features and settings.', "position" => 0],
        ['name' => self::ADMIN, "slug" => self::SLUG_ADMIN, 'description' => 'Manage most system settings and data.', "position" => 1],
        ['name' => self::USER, "slug" => self::SLUG_USER, 'description' => 'Manage most system settings and data.', "position" => 2],
        ['name' => self::SERVICE_PROVIDER, "slug" => self::SLUG_SERVICE_PROVIDER, 'description' => 'Manage most system settings and data.', "position" => 3],
    ];

    const ROLE_PERMISSION_LIST = [
        # 1. Dashboard Permission
        [
            'name' => 'Dashboard',
            'position' => 1,
            "icon" => 'tabler-dashboard',
            "category" => [
                [
                    'name' => 'Dashboard',
                    "permission_list" => [
                        ["name" => "Dashboard", "action" => "dashboard", "slug" => 'view'],
                    ],
                ]
            ],
        ],
        [
            'name' => 'Users',
            'position' => 5,
            "icon" => 'tabler-users',
            "category" => [
                [
                    'name' => 'Users',
                    "permission_list" => [
                        ["name" => 'View Users', "action" => "user", "slug" => 'view'],
                        ["name" => 'Create User', "action" => "user", "slug" => 'create'],
                        ["name" => 'Edit User', "action" => "user", "slug" => 'edit'],
                        ["name" => 'Export Users', "action" => "user", "slug" => 'export-list'],
                        ["name" => 'Restore User', "action" => "user", "slug" => 'restore'],
                        ["name" => 'Update User Password', "action" => "user", "slug" => 'update-password'],
                        ["name" => 'Delete User', "action" => "user", "slug" => 'delete'],
                        ["name" => 'View User Details', "action" => "user", "slug" => 'show'],
                    ]
                ],
                [
                    'name' => 'Manage Roles',
                    "permission_list" => [
                        ["name" => 'Add Role', "action" => "role", "slug" => 'create'],
                        ["name" => 'View Role', "action" => "role", "slug" => 'view'],
                        ["name" => 'Edit Role', "action" => "role", "slug" => 'edit'],
                        ["name" => 'Delete Role', "action" => "role", "slug" => 'delete'],
                    ]
                ]
            ]
        ],

        # 7. Calender Permission
        [
            'name' => 'Calender',
            'position' => 6,
            "icon" => 'tabler-calendar',
            "category" => [
                [
                    'name' => 'Calendar',
                    "permission_list" => [
                        ["name" => 'View Calendar', "action" => "calendar", "slug" => 'view'],
                    ]
                ],
            ]
        ],

        # 7. Profiles Permission
        [
            'name' => 'Profile',
            'position' => 6,
            "icon" => 'tabler-bell-dollar',
            "category" => [
                [
                    'name' => 'Profile',
                    "permission_list" => [
                        ["name" => 'View Profile', "action" => "profile", "slug" => 'view'],
                        ["name" => 'Update info', "action" => "profile", "slug" => 'edit'],
                        ["name" => 'Change Password', "action" => "profile", "slug" => 'change-password'],
                    ]
                ],
                [
                    'name' => 'Login Log',
                    "permission_list" => [
                        ["name" => 'View Login Log List', "action" => "loginLog", "slug" => 'view'],
                        ["name" => 'Delete Login Log', "action" => "loginLog", "slug" => 'delete'],
                    ]
                ],
            ]
        ],

        # 8. Settings Permission
        [
            'name' => 'Settings',
            'position' => 7,
            "icon" => 'tabler-settings',
            "category" => [
                [
                    'name' => 'General Settings',
                    "permission_list" => [
                        ["name" => 'View General Setting', "action" => "generalSetting", "slug" => 'view'],
                        ["name" => 'Save General Setting', "action" => "generalSetting", "slug" => 'save'],
                    ]
                ],
            ]
        ],
    ];
}
