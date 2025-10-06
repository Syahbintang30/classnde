<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | File Upload Security Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains settings for secure file uploads,
    | including allowed file types, size limits, and security policies.
    |
    */

    'max_file_sizes' => [
        'image' => env('UPLOAD_MAX_IMAGE_SIZE', 5 * 1024 * 1024), // 5MB default
        'video' => env('UPLOAD_MAX_VIDEO_SIZE', 500 * 1024 * 1024), // 500MB default
        'document' => env('UPLOAD_MAX_DOCUMENT_SIZE', 10 * 1024 * 1024), // 10MB default
    ],

    'allowed_mime_types' => [
        'image' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ],
        'video' => [
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo', // .avi
            'video/webm',
        ],
        'document' => [
            'application/pdf',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
    ],

    'allowed_extensions' => [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'video' => ['mp4', 'mov', 'avi', 'webm'],
        'document' => ['pdf', 'txt', 'doc', 'docx'],
    ],

    'rate_limiting' => [
        'max_uploads_per_hour' => env('UPLOAD_RATE_LIMIT', 20),
        'max_video_uploads_per_hour' => env('UPLOAD_VIDEO_RATE_LIMIT', 5),
        'large_file_threshold' => env('UPLOAD_LARGE_FILE_THRESHOLD', 50 * 1024 * 1024), // 50MB
    ],

    'security_scanning' => [
        'enable_virus_scan' => env('UPLOAD_ENABLE_VIRUS_SCAN', false),
        'enable_content_validation' => env('UPLOAD_ENABLE_CONTENT_VALIDATION', true),
        'quarantine_suspicious_files' => env('UPLOAD_QUARANTINE_SUSPICIOUS', true),
    ],

    'storage' => [
        'default_disk' => env('UPLOAD_DEFAULT_DISK', 'public'),
        'secure_disk' => env('UPLOAD_SECURE_DISK', 'private'),
        'temp_disk' => env('UPLOAD_TEMP_DISK', 'local'),
    ],

    'monitoring' => [
        'log_all_uploads' => env('UPLOAD_LOG_ALL', true),
        'log_failed_uploads' => env('UPLOAD_LOG_FAILED', true),
        'alert_on_suspicious' => env('UPLOAD_ALERT_SUSPICIOUS', true),
    ],

    'cleanup' => [
        'auto_delete_temp_files' => env('UPLOAD_AUTO_DELETE_TEMP', true),
        'temp_file_retention_hours' => env('UPLOAD_TEMP_RETENTION_HOURS', 2),
        'failed_upload_retention_days' => env('UPLOAD_FAILED_RETENTION_DAYS', 7),
    ],

];