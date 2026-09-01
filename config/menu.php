<?php

/*
|--------------------------------------------------------------------------
| Sidebar navigation
|--------------------------------------------------------------------------
|
| Blocks are rendered in the order below, grouped from daily work at the top
| down to configuration and administration at the bottom.
|
| 'permissions' is what actually decides visibility - see
| HandleInertiaRequests::menu(). When an item lists permissions, 'roles' is
| ignored and kept only to document who is expected to hold them.
|
| 'icon' holds a FontAwesome class name, mapped onto a lucide icon by the
| ICONS table in resources/js/layouts/app-layout.tsx. An unmapped class falls
| back to a generic list icon, so add new ones there too.
|
*/

return [

    // ==========================================
    // 1. DIRECT LINKS
    // ==========================================
    [
        'items' => [
            [
                'label' => 'Dashboard',
                'icon'  => 'fa fa-fire',
                'route' => 'home',
                'roles' => ['super_admin', 'admin', 'manager', 'department_head', 'management', 'staff', 'employee'],
                'permissions' => ['dashboard.view'],
            ],
        ],
    ],

    // ==========================================
    // 2. THE ASSET REGISTER
    // ==========================================
    [
        'header' => 'Assets',
        'items'  => [
            [
                'label' => 'All Assets',
                'icon'  => 'fa fa-cubes',
                'route' => 'assets.index',
                'roles' => ['super_admin', 'admin', 'manager', 'department_head', 'management', 'staff', 'employee'],
                'permissions' => ['assets.view'],
            ],
            [
                'label' => 'Assets In Use',
                'icon'  => 'fa fa-user-check',
                'route' => 'assignments.index',
                'roles' => ['super_admin', 'admin', 'manager', 'management'],
                'permissions' => ['assignments.view'],
            ],
            [
                'label' => 'Stock & Inventory',
                'icon'  => 'fa fa-boxes-stacked',
                'route' => 'stock.index',
                'roles' => ['super_admin', 'admin', 'manager', 'department_head', 'management', 'staff'],
                'permissions' => ['stock.view'],
            ],
            [
                'label' => 'Software Licenses',
                'icon'  => 'fa fa-key',
                'route' => 'software-licenses.index',
                'roles' => ['super_admin', 'admin', 'manager', 'management'],
                'permissions' => ['software_licenses.view'],
            ],
            [
                'label' => 'Assignment Audits',
                'icon'  => 'fa fa-clipboard-check',
                'route' => 'assignment-audits.index',
                'roles' => ['super_admin', 'admin', 'manager', 'management'],
                'permissions' => ['assignment_audits.view', 'assignment_audits.manage'],
            ],
        ],
    ],

    // ==========================================
    // 3. LIFECYCLE - what happens to an asset after it is on the register
    // ==========================================
    [
        'header' => 'Asset Lifecycle',
        'items'  => [
            [
                'label' => 'Maintenance Requests',
                'icon'  => 'fa fa-clipboard-list',
                'route' => 'maintenance-requests.index',
                'roles' => ['super_admin', 'admin', 'manager', 'department_head', 'management'],
                'permissions' => ['maintenance.view'],
            ],
            [
                // The scheduled-work register, distinct from the request queue above.
                'label' => 'Maintenance Schedule',
                'icon'  => 'fa fa-screwdriver-wrench',
                'route' => 'maintenance.index',
                'roles' => ['super_admin', 'admin', 'manager', 'department_head', 'management'],
                'permissions' => ['maintenance.view'],
            ],
            [
                'label' => 'Warranties',
                'icon'  => 'fa fa-shield-halved',
                'route' => 'warranties.index',
                'roles' => ['super_admin', 'admin', 'manager', 'department_head', 'management'],
                'permissions' => ['maintenance.view'],
            ],
            [
                'label' => 'Transfers',
                'icon'  => 'fa fa-right-left',
                'route' => 'transfers.index',
                'roles' => ['super_admin', 'admin', 'manager', 'management'],
                'permissions' => ['transfers.view'],
            ],
            [
                'label' => 'Disposals',
                'icon'  => 'fa fa-trash-can',
                'route' => 'disposals.index',
                'roles' => ['super_admin', 'admin', 'manager', 'management'],
                'permissions' => ['disposals.view'],
            ],
        ],
    ],

    // ==========================================
    // 4. ISSUE REPORTING - the helpdesk flow
    // ==========================================
    [
        'header' => 'Issue Reports',
        'items'  => [
            [
                'label' => 'Report an Issue',
                'icon'  => 'fa fa-file-circle-question',
                'route' => 'report-issue.index',
                'roles' => ['super_admin', 'admin', 'manager', 'department_head', 'management', 'staff', 'employee'],
                'permissions' => ['reports.create'],
            ],
            [
                'label' => 'My Issue Reports',
                'icon'  => 'fa fa-file-circle-check',
                'route' => 'my-reports.index',
                'roles' => ['super_admin', 'admin', 'manager', 'department_head', 'management', 'staff', 'employee'],
                'permissions' => ['reports.view_own'],
            ],
            [
                'label' => 'Incoming Issue Reports',
                'icon'  => 'fa fa-file-circle-exclamation',
                'route' => 'incoming-reports.index',
                'roles' => ['super_admin', 'admin', 'manager', 'department_head', 'management'],
                'permissions' => ['reports.view'],
            ],
        ],
    ],

    // ==========================================
    // 5. REPORTING & ALERTS
    // ==========================================
    [
        'header' => 'Reports & Alerts',
        'items'  => [
            [
                'label' => 'Executive Reports',
                'icon'  => 'fa fa-chart-pie',
                'route' => 'reports.index',
                'roles' => ['super_admin', 'admin', 'manager', 'department_head', 'management'],
                'permissions' => ['reports.view'],
            ],
            [
                'label' => 'Notifications',
                'icon'  => 'fa fa-bell',
                'route' => 'notifications.index',
                'roles' => ['super_admin', 'admin', 'manager', 'management'],
                'permissions' => ['notifications.view'],
            ],
        ],
    ],

    // ==========================================
    // 6. FINANCE & DEPRECIATION
    // ==========================================
    [
        'header' => 'Depreciation & Finance',
        'items'  => [
            [
                'label' => 'Depreciation List',
                'icon'  => 'fa fa-chart-line',
                'route' => 'depreciation.index',
                'roles' => ['super_admin', 'admin', 'manager', 'management'],
                'permissions' => ['depreciation.view'],
            ],
            [
                'label' => 'Depreciation Settings',
                'icon'  => 'fa fa-sliders',
                'route' => 'depreciation-settings.index',
                'roles' => ['super_admin', 'admin', 'manager', 'management'],
                'permissions' => ['depreciation.manage'],
            ],
        ],
    ],

    // ==========================================
    // 7. ORGANIZATION & HR
    // ==========================================
    [
        'header' => 'Organization',
        'items'  => [
            [
                'label' => 'Employees',
                'icon'  => 'fa fa-id-card',
                'route' => 'employees.index',
                'roles' => ['super_admin', 'admin', 'manager', 'department_head', 'management'],
                'permissions' => ['employees.view'],
            ],
            [
                'label' => 'Departments',
                'icon'  => 'fa fa-sitemap',
                'route' => 'departments.index',
                'roles' => ['super_admin', 'admin', 'manager', 'management'],
                'permissions' => ['departments.view'],
            ],
            [
                'label' => 'Positions / Designations',
                'icon'  => 'fa fa-user-tag',
                'route' => 'positions.index',
                'roles' => ['super_admin', 'admin', 'manager', 'management'],
                'permissions' => ['positions.view'],
            ],
        ],
    ],

    // ==========================================
    // 8. MASTER CONFIGURATION & LOOKUPS
    // ==========================================
    [
        'header' => 'Master Data',
        'items'  => [
            [
                'label' => 'Asset Categories',
                'icon'  => 'fa fa-list',
                'route' => 'categories.index',
                'roles' => ['super_admin', 'admin', 'manager', 'department_head', 'management'],
                'permissions' => ['categories.view'],
            ],
            [
                'label' => 'Locations & Branches',
                'icon'  => 'fa fa-building',
                'route' => 'locations.index',
                'roles' => ['super_admin', 'admin', 'manager', 'department_head', 'management'],
                'permissions' => ['locations.view'],
            ],
        ],
    ],

    // ==========================================
    // 9. ACCESS CONTROL & SYSTEM AUDIT
    // ==========================================
    [
        'header' => 'User & Security',
        'items'  => [
            [
                'label' => 'User Accounts',
                'icon'  => 'fa fa-users',
                'route' => 'users.index',
                'roles' => ['super_admin', 'admin', 'manager', 'management'],
                'permissions' => ['users.view', 'users.manage'],
            ],
            [
                'label' => 'Role Management',
                'icon'  => 'fa fa-user-shield',
                'route' => 'roles.index',
                'roles' => ['super_admin', 'admin', 'manager', 'management'],
                'permissions' => ['roles.view', 'roles.manage'],
            ],
            [
                'label' => 'Account Status',
                'icon'  => 'fa fa-user-gear',
                'route' => 'users.status',
                'roles' => ['super_admin', 'admin', 'manager', 'management'],
                'permissions' => ['users.view', 'users.manage'],
            ],
            [
                'label' => 'Audit & Activity Log',
                'icon'  => 'fa fa-signal',
                'route' => 'audit.index',
                'roles' => ['super_admin', 'admin', 'manager', 'management'],
                'permissions' => ['audit.view', 'audit.manage'],
            ],
        ],
    ],

];
