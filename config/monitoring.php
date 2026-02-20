<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration Monitoring ArtisanConnect
    |--------------------------------------------------------------------------
    |
    | Configuration pour le monitoring, les métriques et les alertes
    | de l'application ArtisanConnect en production.
    |
    */

    'enabled' => env('MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Métriques Application
    |--------------------------------------------------------------------------
    */
    'metrics' => [
        'enabled' => env('METRICS_ENABLED', true),
        'collection_interval' => env('METRICS_INTERVAL', 60), // secondes
        
        // Métriques à collecter
        'collect' => [
            'response_time' => true,
            'memory_usage' => true,
            'cpu_usage' => true,
            'database_queries' => true,
            'cache_hits' => true,
            'active_users' => true,
            'api_requests' => true,
            'errors' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Checks
    |--------------------------------------------------------------------------
    */
    'health' => [
        'enabled' => env('HEALTH_CHECKS_ENABLED', true),
        'endpoint' => '/health',
        
        'checks' => [
            'database' => [
                'enabled' => true,
                'timeout' => 5,
            ],
            'redis' => [
                'enabled' => true,
                'timeout' => 3,
            ],
            'storage' => [
                'enabled' => true,
                'timeout' => 2,
            ],
            'external_apis' => [
                'enabled' => true,
                'services' => [
                    'orange_money' => 'https://api.orange.com/health',
                    'mtn_money' => 'https://api.mtn.com/health',
                ],
                'timeout' => 10,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alertes & Notifications
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'enabled' => env('ALERTS_ENABLED', true),
        
        'channels' => [
            'email' => [
                'enabled' => env('ALERT_EMAIL_ENABLED', true),
                'to' => env('ALERT_EMAIL_TO', 'admin@artisanconnect.ci'),
                'from' => env('ALERT_EMAIL_FROM', 'monitoring@artisanconnect.ci'),
            ],
            'slack' => [
                'enabled' => env('ALERT_SLACK_ENABLED', false),
                'webhook_url' => env('SLACK_WEBHOOK_URL'),
                'channel' => env('SLACK_CHANNEL', '#alerts'),
            ],
            'sms' => [
                'enabled' => env('ALERT_SMS_ENABLED', false),
                'phone_numbers' => explode(',', env('ALERT_SMS_NUMBERS', '')),
            ],
        ],
        
        // Seuils d'alertes
        'thresholds' => [
            'response_time' => [
                'warning' => 2000, // ms
                'critical' => 5000, // ms
            ],
            'memory_usage' => [
                'warning' => 80, // %
                'critical' => 95, // %
            ],
            'cpu_usage' => [
                'warning' => 70, // %
                'critical' => 90, // %
            ],
            'error_rate' => [
                'warning' => 5, // %
                'critical' => 15, // %
            ],
            'database_connections' => [
                'warning' => 80, // %
                'critical' => 95, // %
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logs Monitoring
    |--------------------------------------------------------------------------
    */
    'logs' => [
        'enabled' => env('LOG_MONITORING_ENABLED', true),
        
        'patterns' => [
            'errors' => [
                'level' => ['error', 'critical', 'alert', 'emergency'],
                'keywords' => ['exception', 'fatal', 'crash'],
            ],
            'performance' => [
                'keywords' => ['slow', 'timeout', 'memory_limit'],
            ],
            'security' => [
                'keywords' => ['unauthorized', 'forbidden', 'attack', 'breach'],
            ],
        ],
        
        'retention' => [
            'days' => env('LOG_RETENTION_DAYS', 30),
            'max_size' => env('LOG_MAX_SIZE', '1GB'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    */
    'performance' => [
        'enabled' => env('PERFORMANCE_MONITORING_ENABLED', true),
        
        'slow_queries' => [
            'enabled' => true,
            'threshold' => 1000, // ms
            'log_slow_queries' => true,
        ],
        
        'profiling' => [
            'enabled' => env('PROFILING_ENABLED', false),
            'sample_rate' => env('PROFILING_SAMPLE_RATE', 0.1), // 10%
        ],
        
        'apm' => [
            'enabled' => env('APM_ENABLED', false),
            'service_name' => env('APM_SERVICE_NAME', 'artisanconnect-api'),
            'environment' => env('APM_ENVIRONMENT', 'production'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Monitoring
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'enabled' => env('DASHBOARD_ENABLED', true),
        'refresh_interval' => env('DASHBOARD_REFRESH_INTERVAL', 30), // secondes
        
        'widgets' => [
            'overview' => [
                'enabled' => true,
                'metrics' => ['requests_per_minute', 'response_time', 'error_rate'],
            ],
            'database' => [
                'enabled' => true,
                'metrics' => ['connections', 'queries_per_second', 'slow_queries'],
            ],
            'cache' => [
                'enabled' => true,
                'metrics' => ['hit_rate', 'memory_usage', 'evictions'],
            ],
            'queue' => [
                'enabled' => true,
                'metrics' => ['jobs_per_minute', 'failed_jobs', 'processing_time'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reporting
    |--------------------------------------------------------------------------
    */
    'reports' => [
        'enabled' => env('REPORTS_ENABLED', true),
        
        'daily' => [
            'enabled' => true,
            'time' => '23:59',
            'recipients' => env('DAILY_REPORT_RECIPIENTS', 'admin@artisanconnect.ci'),
        ],
        
        'weekly' => [
            'enabled' => true,
            'day' => 'sunday',
            'time' => '10:00',
            'recipients' => env('WEEKLY_REPORT_RECIPIENTS', 'team@artisanconnect.ci'),
        ],
        
        'monthly' => [
            'enabled' => true,
            'day' => 1,
            'time' => '09:00',
            'recipients' => env('MONTHLY_REPORT_RECIPIENTS', 'management@artisanconnect.ci'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Services
    |--------------------------------------------------------------------------
    */
    'integrations' => [
        'datadog' => [
            'enabled' => env('DATADOG_ENABLED', false),
            'api_key' => env('DATADOG_API_KEY'),
            'app_key' => env('DATADOG_APP_KEY'),
            'site' => env('DATADOG_SITE', 'datadoghq.com'),
        ],
        
        'newrelic' => [
            'enabled' => env('NEW_RELIC_ENABLED', false),
            'app_name' => env('NEW_RELIC_APP_NAME', 'ArtisanConnect API'),
            'license_key' => env('NEW_RELIC_LICENSE_KEY'),
        ],
        
        'prometheus' => [
            'enabled' => env('PROMETHEUS_ENABLED', false),
            'port' => env('PROMETHEUS_PORT', 9090),
            'path' => env('PROMETHEUS_PATH', '/metrics'),
        ],
        
        'grafana' => [
            'enabled' => env('GRAFANA_ENABLED', false),
            'url' => env('GRAFANA_URL'),
            'api_key' => env('GRAFANA_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Monitoring
    |--------------------------------------------------------------------------
    */
    'security' => [
        'enabled' => env('SECURITY_MONITORING_ENABLED', true),
        
        'events' => [
            'failed_login_attempts' => true,
            'brute_force_attacks' => true,
            'suspicious_requests' => true,
            'rate_limit_exceeded' => true,
            'invalid_tokens' => true,
        ],
        
        'thresholds' => [
            'failed_logouts_per_minute' => 10,
            'suspicious_requests_per_minute' => 50,
            'rate_limit_violations_per_minute' => 20,
        ],
        
        'actions' => [
            'block_ip' => env('AUTO_BLOCK_IP', false),
            'notify_admin' => true,
            'log_incident' => true,
        ],
    ],
];
