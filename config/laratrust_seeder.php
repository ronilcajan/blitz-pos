<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        'superadministrator' => [
            'pos' => 'c,r,u,d,g',
            'products' => 'c,r,u,d,g',
            'stocks' => 'c,r,u,d,g',
            'expenses' => 'c,r,u,d,g',
            'sales' => 'c,r,u,d,g',
            'purchase' => 'c,r,u,d,g',
            'delivery' => 'c,r,u,d,g',
            'customers' => 'c,r,u,d,g',
            'suppliers' => 'c,r,u,d,g',
            'logs' => 'c,r,u,d,g',
            'settings' => 'c,r,u,d,g',
            'subscription' => 'c,r,u,d,g',
            'users' => 'c,r,u,d,g',
            'profile' => 'r,u',
        ],
        'owner' => [
            'pos' => 'c,r,u,d,g',
            'products' => 'c,r,u,d,g',
            'stocks' => 'c,r,u,d,g',
            'expenses' => 'c,r,u,d,g',
            'sales' => 'c,r,u,d,g',
            'purchase' => 'c,r,u,d,g',
            'delivery' => 'c,r,u,d,g',
            'customers' => 'c,r,u,d,g',
            'suppliers' => 'c,r,u,d,g',
            'logs' => 'c,r,u,d,g',
            'settings' => 'c,r,u,d,g',
            'subscription' => 'c,r,u,d,g',
            'users' => 'c,r,u,d,g',
            'profile' => 'r,u',
        ],
        'admin' => [
            'pos' => 'c,r,u,d,g',
            'products' => 'c,r,u,d,g',
            'stocks' => 'c,r,u,d,g',
            'expenses' => 'c,r,u,d,g',
            'sales' => 'c,r,u,d,g',
            'purchase' => 'c,r,u,d,g',
            'delivery' => 'c,r,u,d,g',
            'customers' => 'c,r,u,d,g',
            'suppliers' => 'c,r,u,d,g',
            'users' => 'c,r,u,d,g',
            'profile' => 'r,u',
        ],
        'staff' => [
            'pos' => 'c,r',
            'products' => 'c,r',
            'stocks' => 'c,r',
            'expenses' => 'c,r',
            'sales' => 'c,r',
            'purchase' => 'c,r',
            'delivery' => 'c,r',
            'customers' => 'c,r',
            'suppliers' => 'c,r',
            'profile' => 'r,u',
        ],
        // 'role_name' => [
        //     'module_1_name' => 'c,r,u,d',
        // ],
    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
        'g' => 'generate',
    ],
];
