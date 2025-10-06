<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Application Constants Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration file untuk menggantikan magic numbers dan hardcoded values
    | dengan values yang dapat dikonfigurasi. Values ini dapat di-override
    | melalui database settings menggunakan DynamicConfigService.
    |
    */

    'validation' => [
        'name_max_length' => env('VALIDATION_NAME_MAX_LENGTH', 255),
        'email_max_length' => env('VALIDATION_EMAIL_MAX_LENGTH', 255),
        'filename_max_length' => env('VALIDATION_FILENAME_MAX_LENGTH', 100),
        'input_preview_length' => env('VALIDATION_INPUT_PREVIEW_LENGTH', 200),
        'photo_max_size_kb' => env('VALIDATION_PHOTO_MAX_SIZE_KB', 5120), // 5MB
    ],

    'security' => [
        'session_timeout_minutes' => env('SECURITY_SESSION_TIMEOUT_MINUTES', 30),
        'max_concurrent_sessions' => env('SECURITY_MAX_CONCURRENT_SESSIONS', 3),
        'session_cache_hours' => env('SECURITY_SESSION_CACHE_HOURS', 24),
        'hsts_max_age_seconds' => env('SECURITY_HSTS_MAX_AGE_SECONDS', 31536000), // 1 year
        'password_min_length' => env('SECURITY_PASSWORD_MIN_LENGTH', 8),
    ],

    'rate_limiting' => [
        'api_requests_per_hour' => env('RATE_LIMIT_API_REQUESTS', 100),
        'api_window_minutes' => env('RATE_LIMIT_API_WINDOW', 60),
        'auth_requests_per_window' => env('RATE_LIMIT_AUTH_REQUESTS', 10),
        'auth_window_minutes' => env('RATE_LIMIT_AUTH_WINDOW', 15),
        'payment_requests_per_hour' => env('RATE_LIMIT_PAYMENT_REQUESTS', 20),
        'contact_requests_per_hour' => env('RATE_LIMIT_CONTACT_REQUESTS', 5),
        'search_requests_per_hour' => env('RATE_LIMIT_SEARCH_REQUESTS', 50),
        'default_requests_per_hour' => env('RATE_LIMIT_DEFAULT_REQUESTS', 200),
        'window_minutes' => env('RATE_LIMIT_WINDOW_MINUTES', 60),
    ],

    'file_upload' => [
        'image_max_size_bytes' => env('UPLOAD_IMAGE_MAX_SIZE', 5 * 1024 * 1024), // 5MB
        'video_max_size_bytes' => env('UPLOAD_VIDEO_MAX_SIZE', 500 * 1024 * 1024), // 500MB
        'document_max_size_bytes' => env('UPLOAD_DOCUMENT_MAX_SIZE', 10 * 1024 * 1024), // 10MB
        'magic_header_read_bytes' => env('UPLOAD_MAGIC_HEADER_BYTES', 20),
    ],

    'http_status' => [
        'unauthorized' => 401,
        'forbidden' => 403,
        'not_found' => 404,
        'too_many_requests' => 429,
        'internal_server_error' => 500,
    ],

    'twilio' => [
        'token_ttl_seconds' => env('TWILIO_TOKEN_TTL_SECONDS', 3600), // 1 hour
        'fake_room_id_min' => env('TWILIO_FAKE_ROOM_ID_MIN', 1000),
        'fake_room_id_max' => env('TWILIO_FAKE_ROOM_ID_MAX', 9999),
    ],

    'magic_numbers' => [
        // File type magic numbers for validation
        'jpeg_header' => [0xFF, 0xD8, 0xFF],
        'png_header' => [0x89, 0x50, 0x4E, 0x47],
        'gif87a_header' => [0x47, 0x49, 0x46, 0x38, 0x37, 0x61],
        'gif89a_header' => [0x47, 0x49, 0x46, 0x38, 0x39, 0x61],
        'webp_header' => [0x52, 0x49, 0x46, 0x46], // RIFF
        'mp4_header' => [0x00, 0x00, 0x00, 0x18, 0x66, 0x74, 0x79, 0x70],
        'avi_header' => [0x52, 0x49, 0x46, 0x46], // RIFF
        'mov_header' => [0x00, 0x00, 0x00, 0x14, 0x66, 0x74, 0x79, 0x70],
        'pdf_header' => [0x25, 0x50, 0x44, 0x46],
    ],

    'business_logic' => [
        'intermediate_package_slug' => env('INTERMEDIATE_PACKAGE_SLUG', 'intermediate'),
        'free_coaching_ticket_count' => env('FREE_COACHING_TICKET_COUNT', 1),
        'max_package_benefits_length' => env('MAX_PACKAGE_BENEFITS_LENGTH', 1000),
        'coaching_session_duration_minutes' => env('COACHING_SESSION_DURATION_MINUTES', 60),
        'keluh_kesah_max_length' => env('KELUH_KESAH_MAX_LENGTH', 1000),
        'admin_action_max_length' => env('ADMIN_ACTION_MAX_LENGTH', 1000),
        'referral_tickets_max' => env('REFERRAL_TICKETS_MAX', 1000),
        'test_payment_amount' => env('TEST_PAYMENT_AMOUNT', 1000),
        'bunny_url_expiry_seconds' => env('BUNNY_URL_EXPIRY_SECONDS', 3600),
    ],

    'api_endpoints' => [
        'bunny_video_base' => env('BUNNY_VIDEO_BASE_URL', 'https://video.bunnycdn.com'),
        'midtrans_base' => env('MIDTRANS_BASE_URL', 'https://app.midtrans.com'),
        'midtrans_sandbox' => env('MIDTRANS_SANDBOX_URL', 'https://app.sandbox.midtrans.com'),
        'default_protocol' => env('DEFAULT_URL_PROTOCOL', 'http://'),
    ],

];