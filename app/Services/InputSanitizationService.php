<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InputSanitizationService
{
    private static array $config;

    /**
     * Initialize configuration from constants
     */
    private static function getConfig(): array
    {
        if (!isset(self::$config)) {
            self::$config = config('constants.validation');
        }
        return self::$config;
    }
    /**
     * Sanitize string input for safe database storage and display
     */
    public static function sanitizeString(string $input, array $options = []): string
    {
        $allowHtml = $options['allow_html'] ?? false;
        $maxLength = $options['max_length'] ?? null;
        
        // Remove null bytes
        $input = str_replace("\0", '', $input);
        
        if (!$allowHtml) {
            // Strip all HTML tags and special characters
            $input = strip_tags($input);
            $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } else {
            // Allow only safe HTML tags
            $allowedTags = $options['allowed_tags'] ?? '<p><br><strong><em><ul><ol><li><a>';
            $input = strip_tags($input, $allowedTags);
            
            // Remove dangerous attributes
            $input = preg_replace('/on\w+="[^"]*"/i', '', $input);
            $input = preg_replace('/javascript:/i', '', $input);
        }
        
        // Normalize whitespace
        $input = preg_replace('/\s+/', ' ', $input);
        $input = trim($input);
        
        // Apply length limit
        if ($maxLength && strlen($input) > $maxLength) {
            $input = substr($input, 0, $maxLength);
        }
        
        return $input;
    }

    /**
     * Sanitize email input
     */
    public static function sanitizeEmail(string $email): string
    {
        $email = trim(strtolower($email));
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return $email;
    }

    /**
     * Sanitize numeric input
     */
    public static function sanitizeNumeric(mixed $input, array $options = []): ?float
    {
        $min = $options['min'] ?? null;
        $max = $options['max'] ?? null;
        $allowNegative = $options['allow_negative'] ?? true;
        
        // Convert to string first
        $input = (string) $input;
        
        // Remove all non-numeric characters except decimal point and minus
        $input = preg_replace('/[^0-9.\-]/', '', $input);
        
        // Convert to float
        $value = (float) $input;
        
        // Check negative allowance
        if (!$allowNegative && $value < 0) {
            return null;
        }
        
        // Apply min/max constraints
        if ($min !== null && $value < $min) {
            $value = $min;
        }
        if ($max !== null && $value > $max) {
            $value = $max;
        }
        
        return $value;
    }

    /**
     * Sanitize phone number
     */
    public static function sanitizePhone(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Ensure + is only at the beginning
        if (strpos($phone, '+') !== false) {
            $phone = '+' . str_replace('+', '', $phone);
        }
        
        return $phone;
    }

    /**
     * Sanitize URL input
     */
    public static function sanitizeUrl(string $url): ?string
    {
        $url = trim($url);
        
        // Add protocol if no protocol specified
        if (!preg_match('/^https?:\/\//', $url)) {
            $defaultProtocol = config('constants.api_endpoints.default_protocol', 'http://');
            $url = $defaultProtocol . $url;
        }
        
        // Validate and sanitize URL
        $sanitized = filter_var($url, FILTER_SANITIZE_URL);
        
        // Validate URL format
        if (filter_var($sanitized, FILTER_VALIDATE_URL) === false) {
            return null;
        }
        
        return $sanitized;
    }

    /**
     * Sanitize filename for safe storage
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Get file extension
        $pathInfo = pathinfo($filename);
        $extension = $pathInfo['extension'] ?? '';
        $name = $pathInfo['filename'] ?? 'file';
        
        // Remove dangerous characters from name
        $name = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        $name = trim($name, '_');
        
        // Limit length
                // Ensure name length is reasonable
        $config = self::getConfig();
        $name = substr($name, 0, $config['filename_max_length']);
        
        // Reconstruct filename
        return $extension ? $name . '.' . $extension : $name;
    }

    /**
     * Sanitize array of inputs recursively
     */
    public static function sanitizeArray(array $data, array $rules = []): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            $rule = $rules[$key] ?? ['type' => 'string'];
            
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value, $rule['rules'] ?? []);
            } else {
                $sanitized[$key] = self::sanitizeByType($value, $rule);
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize input based on type
     */
    private static function sanitizeByType(mixed $input, array $rule): mixed
    {
        $type = $rule['type'] ?? 'string';
        
        return match ($type) {
            'string' => self::sanitizeString((string) $input, $rule),
            'email' => self::sanitizeEmail((string) $input),
            'numeric' => self::sanitizeNumeric($input, $rule),
            'phone' => self::sanitizePhone((string) $input),
            'url' => self::sanitizeUrl((string) $input),
            'filename' => self::sanitizeFilename((string) $input),
            default => self::sanitizeString((string) $input, $rule),
        };
    }

    /**
     * Detect and log potential XSS attempts
     */
    public static function detectXSS(string $input, string $field = 'unknown'): bool
    {
        $xssPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>/i',
            '/<object[^>]*>/i',
            '/<embed[^>]*>/i',
            '/data:text\/html/i',
            '/vbscript:/i',
            '/<meta[^>]*http-equiv/i',
        ];
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                Log::warning('Potential XSS attempt detected', [
                    'field' => $field,
                    'input' => substr($input, 0, self::getConfig()['input_preview_length']),
                    'pattern' => $pattern,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'user_id' => Auth::id()
                ]);
                return true;
            }
        }
        
        return false;
    }

    /**
     * Detect SQL injection attempts
     */
    public static function detectSQLInjection(string $input, string $field = 'unknown'): bool
    {
        $sqlPatterns = [
            '/union\s+select/i',
            '/select\s+.*\s+from/i',
            '/insert\s+into/i',
            '/update\s+.*\s+set/i',
            '/delete\s+from/i',
            '/drop\s+table/i',
            '/truncate\s+table/i',
            '/alter\s+table/i',
            '/create\s+table/i',
            '/exec\s*\(/i',
            '/script.*>.*</i',
            '/\'\s*or\s*\'/i',
            '/\'\s*and\s*\'/i',
            '/\'\s*union\s*\'/i',
        ];
        
        foreach ($sqlPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                Log::warning('Potential SQL injection attempt detected', [
                    'field' => $field,
                    'input' => substr($input, 0, self::getConfig()['input_preview_length']),
                    'pattern' => $pattern,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'user_id' => Auth::id()
                ]);
                return true;
            }
        }
        
        return false;
    }
}