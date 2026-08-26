<?php
return [

    // each block = one header + items
    [
        'header' => 'Dashboard',
        'items' => [
            ['label' => 'Dashboard', 'icon' => 'fa fa-fire', 'route' => 'home', 'roles' => ['admin','staff','manager']],
        ],
    ],

    [
        'header' => 'Accounts',
        'items' => [
            ['label' => 'Account List', 'icon' => 'fa fa-users', 'route' => 'users.index', 'roles' => ['admin']],
            ['label' => 'Account Status', 'icon' => 'fa fa-user-check', 'route' => 'users.status', 'roles' => ['admin']],
            ['label' => 'Account Activity', 'icon' => 'fa fa-signal', 'route' => 'audit.index', 'roles' => ['admin']],
        ],
    ],

    [
        'header' => 'Master Data',
        'items' => [
            ['label' => 'Assets', 'icon' => 'fa fa-cubes', 'route' => 'assets.index', 'roles' => ['admin','staff','manager']],
            ['label' => 'Categories', 'icon' => 'fa fa-list', 'route' => 'categories.index', 'roles' => ['admin','staff','manager']],
            ['label' => 'Locations', 'icon' => 'fa fa-building', 'route' => 'locations.index', 'roles' => ['admin','staff','manager']],
            ['label' => 'Employees', 'icon' => 'fa fa-id-card', 'route' => 'employees.index', 'roles' => ['admin','staff','manager']],
            ['label' => 'Departments', 'icon' => 'fa fa-sitemap', 'route' => 'departments.index', 'roles' => ['admin','manager']],
            ['label' => 'Positions', 'icon' => 'fa fa-user-tag', 'route' => 'positions.index', 'roles' => ['admin','manager']],
        ],
    ],

    [
        'header' => 'IT Management',
        'items' => [
            ['label' => 'Assets In Use', 'icon' => 'fa fa-user-check', 'route' => 'assignments.index', 'roles' => ['admin','manager']],
            ['label' => 'Software Licenses', 'icon' => 'fa fa-key', 'route' => 'software-licenses.index', 'roles' => ['admin','manager']],
        ],
    ],

    [
        'header' => 'Stock Take',
        'items' => [
            ['label' => 'Stock Take List', 'icon' => 'fa fa-clipboard-list', 'route' => 'stock-takes.index', 'roles' => ['admin','manager','staff']],
            ['label' => 'Start Stock Take', 'icon' => 'fa fa-qrcode', 'route' => 'stock-takes.create', 'roles' => ['admin','staff']],
        ],
    ],

    [
        'header' => 'Depreciation',
        'items' => [
            ['label' => 'Depreciation List', 'icon'  => 'fa fa-chart-line', 'route' => 'depreciation.index', 'roles' => ['admin','staff', 'manager']],
            ['label' => 'Depreciation Settings', 'icon'  => 'fa fa-sliders', 'route' => 'depreciation-settings.index', 'roles' => ['admin', 'manager']],
        ],
    ],

    [
        'header' => 'Issue Reports',
        'items' => [
            ['label' => 'Incoming Reports', 'icon' => 'fa fa-file-arrow-down', 'route' => 'incoming-reports.index', 'roles' => ['admin','manager']],
            ['label' => 'Report an Issue', 'icon' => 'fa fa-file-circle-plus', 'route' => 'report-issue.index', 'roles' => ['admin','staff']],
            ['label' => 'Review Reports', 'icon' => 'fa fa-file-circle-question', 'route' => 'review-reports.index', 'roles' => ['staff','manager','admin']],
            ['label' => 'Completed Reports', 'icon' => 'fa fa-file-circle-check', 'route' => 'completed-reports.index', 'roles' => ['admin','staff','manager']],
        ],
    ],


];
