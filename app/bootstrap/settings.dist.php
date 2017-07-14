<?php
return [
    // Framework Name
    'framework' => 'dappur', 
    // Enable/Disable Public display of error details
    'displayErrorDetails' => false,
    // Database Options
    'db' => [
        // Select Database to Use
        'use' => 'development',
        'databases' => [
            // Development Database
            'development' => [
                'driver' => 'mysql', // Only MySQL Supported
                'host' => 'localhost', // Database Host
                'port' => 3306, // My SQL Port
                'database' => '', // Database Name
                'username' => '', // Database Username
                'password' => '', // Database Password
                'charset' => 'utf8',
                'collation' => 'utf8_general_ci',
                'prefix' => ''
            ],
            // Production Database
            'production' => [
                'driver' => 'mysql',
                'host' => '',
                'port' => 3306,
                'database' => '',
                'username' => '',
                'password' => '',
                'charset' => 'utf8',
                'collation' => 'utf8_general_ci',
                'prefix' => ''
            ]
        ] 
    ], 
    // Twig Templating Options
    'view' => [
        'template_path' => '../app/views/',
        'twig' => [
            'cache' => false, //__DIR__ . '/../../storage/cache/twig'
            'debug' => false,
            'auto_reload' => true,
        ],
    ],
    // Logging Options
    'logger' => [
        'log_name' => 'Dappur', // App name in log entry
        'log_path' => '../storage/log/monolog/', // PATH_TO_LOG **Add trailing Slash**
        'log_file_name' => 'dappur.log', // Log File Name
        'le_token' => '', // Logentries Access Token
    ],
    // Cloudinary Options
    'cloudinary' => [
        'enabled' => false, // Enable Cloudinary
        'cloud_name' => '', // Cloud Name
        'api_key' => '', // API Key
        'api_secret' => '', // API Secret
    ],
    // Deployment Options
    'deployment' => [
        'enabled' => true, // Enable Deployment
        'manual' => true, // Allow manual deployment
        'deploy_url' => '', 
        'deploy_token' => '', // Deployment Token (Reccommend random SHA-1 Key)
        'repo_url' => '' // URL of the Git Repository (Make sure your PHP user has access to the repo)
    ],
    // Mail Options
    'mail' => [
        'relay' => 'phpmail', // Defaults to the PHP mail(). Options are: mailgun, mandrill
        // Mailgun Options (Required if set as relay)
        'mailgun' => [
            'username' => '', // Mailgun SMTP username from https://mailgun.com/cp/domains
            'password' => '' // Mailgun SMTP password from https://mailgun.com/cp/domains
        ],
        'mandrill' => [
            'username' => '', // Mandrill Username
            'password' => '' // Mandrill Password
        ]
    ]

];