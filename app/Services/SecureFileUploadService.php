<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SecureFileUploadService
{
    /**
     * Security configuration for file uploads
     */
    private array $allowedMimeTypes = [
        'image' => [
            'image/jpeg',
            'image/png', 
            'image/gif',
            'image/webp'
        ],
        'video' => [
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo', // .avi
            'video/webm'
        ],
        'document' => [
            'application/pdf',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ]
    ];

    private array $allowedExtensions = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'video' => ['mp4', 'mov', 'avi', 'webm'],
        'document' => ['pdf', 'txt', 'doc', 'docx']
    ];

    private array $maxFileSizes;
    private array $magicNumbers;
    private int $magicHeaderBytes;

    public function __construct()
    {
        $fileConfig = config('constants.file_upload');
        $magicConfig = config('constants.magic_numbers');
        
        $this->maxFileSizes = [
            'image' => $fileConfig['image_max_size_bytes'],
            'video' => $fileConfig['video_max_size_bytes'],
            'document' => $fileConfig['document_max_size_bytes']
        ];
        
        $this->magicNumbers = $magicConfig;
        $this->magicHeaderBytes = $fileConfig['magic_header_read_bytes'];
    }

    /**
     * Validate uploaded file with comprehensive security checks
     */
    public function validateUploadedFile(UploadedFile $file, string $type = 'image'): array
    {
        $errors = [];
        
        // Basic file validation
        if (!$file->isValid()) {
            $errors[] = 'File upload failed or corrupted';
            return ['valid' => false, 'errors' => $errors];
        }

        // File size validation
        if (!$this->validateFileSize($file, $type)) {
            $maxSize = $this->maxFileSizes[$type] ?? $this->maxFileSizes['image'];
            $errors[] = "File size exceeds maximum allowed size of " . $this->formatBytes($maxSize);
        }

        // Extension validation
        if (!$this->validateFileExtension($file, $type)) {
            $allowed = implode(', ', $this->allowedExtensions[$type] ?? []);
            $errors[] = "File extension not allowed. Allowed extensions: {$allowed}";
        }

        // MIME type validation
        if (!$this->validateMimeType($file, $type)) {
            $errors[] = 'File type not allowed or MIME type mismatch';
        }

        // Content validation (magic number check)
        if (!$this->validateFileContent($file, $type)) {
            $errors[] = 'File content validation failed - file may be malicious';
        }

        // File name security check
        if (!$this->validateFileName($file)) {
            $errors[] = 'Filename contains potentially dangerous characters';
        }

        // Virus scanning
        if (!$this->performVirusScan($file)) {
            $errors[] = 'File failed virus scan - potential malware detected';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'file_info' => [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension()
            ]
        ];
    }

    /**
     * Validate file size
     */
    private function validateFileSize(UploadedFile $file, string $type): bool
    {
        $maxSize = $this->maxFileSizes[$type] ?? $this->maxFileSizes['image'];
        return $file->getSize() <= $maxSize;
    }

    /**
     * Validate file extension
     */
    private function validateFileExtension(UploadedFile $file, string $type): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = $this->allowedExtensions[$type] ?? $this->allowedExtensions['image'];
        return in_array($extension, $allowedExtensions);
    }

    /**
     * Validate MIME type
     */
    private function validateMimeType(UploadedFile $file, string $type): bool
    {
        $mimeType = $file->getMimeType();
        $allowedMimeTypes = $this->allowedMimeTypes[$type] ?? $this->allowedMimeTypes['image'];
        return in_array($mimeType, $allowedMimeTypes);
    }

    /**
     * Validate file content using magic numbers (file signatures)
     */
    private function validateFileContent(UploadedFile $file, string $type): bool
    {
        try {
            $handle = fopen($file->getRealPath(), 'rb');
            if (!$handle) {
                return false;
            }

            $header = fread($handle, $this->magicHeaderBytes);
            fclose($handle);

            // Check magic numbers for common file types using configuration
            $typeSignatures = [];
            
            if ($type === 'image') {
                $typeSignatures = [
                    'JPEG' => $this->magicNumbers['jpeg_header'],
                    'PNG' => $this->magicNumbers['png_header'],
                    'GIF87a' => $this->magicNumbers['gif87a_header'],
                    'GIF89a' => $this->magicNumbers['gif89a_header'],
                    'WEBP' => $this->magicNumbers['webp_header']
                ];
            } elseif ($type === 'video') {
                $typeSignatures = [
                    'MP4' => $this->magicNumbers['mp4_header'],
                    'AVI' => $this->magicNumbers['avi_header'],
                    'MOV' => $this->magicNumbers['mov_header']
                ];
            } elseif ($type === 'document') {
                $typeSignatures = [
                    'PDF' => $this->magicNumbers['pdf_header']
                ];
            }
            
            foreach ($typeSignatures as $format => $signature) {
                if ($this->checkMagicNumber($header, $signature)) {
                    return true;
                }
            }

            Log::warning('File content validation failed - magic number mismatch', [
                'file' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'header_hex' => bin2hex(substr($header, 0, 10))
            ]);

            return false;

        } catch (\Throwable $e) {
            Log::error('File content validation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check magic number signature
     */
    private function checkMagicNumber(string $header, array $signature): bool
    {
        for ($i = 0; $i < count($signature); $i++) {
            if (!isset($header[$i]) || ord($header[$i]) !== $signature[$i]) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate filename for security
     */
    private function validateFileName(UploadedFile $file): bool
    {
        $filename = $file->getClientOriginalName();
        
        // Check for dangerous patterns
        $dangerousPatterns = [
            '/\.\./',           // Directory traversal
            '/[<>:"|?*]/',      // Invalid characters
            '/^(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])$/i', // Windows reserved names
            '/\.(php|asp|jsp|exe|bat|cmd|scr|vbs|js)$/i' // Executable extensions
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                Log::warning('Dangerous filename detected', [
                    'filename' => $filename,
                    'pattern' => $pattern
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Perform virus scanning (basic implementation)
     * In production, integrate with ClamAV or similar
     */
    private function performVirusScan(UploadedFile $file): bool
    {
        try {
            // Basic file content scan for malicious patterns
            $content = file_get_contents($file->getRealPath());
            
            // Check for common malicious patterns
            $maliciousPatterns = [
                '/<script[^>]*>.*?<\/script>/i',
                '/eval\s*\(/i',
                '/exec\s*\(/i',
                '/system\s*\(/i',
                '/shell_exec\s*\(/i',
                '/base64_decode\s*\(/i',
                '/__FILE__/',
                '/__DIR__/',
                '/\$_GET\[/',
                '/\$_POST\[/',
                '/file_get_contents\s*\(/i'
            ];

            foreach ($maliciousPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    Log::warning('Malicious pattern detected in file', [
                        'file' => $file->getClientOriginalName(),
                        'pattern' => $pattern
                    ]);
                    return false;
                }
            }

            // TODO: Integrate with external virus scanner like ClamAV
            // $this->scanWithClamAV($file->getRealPath());

            return true;

        } catch (\Throwable $e) {
            Log::error('Virus scan error: ' . $e->getMessage());
            return false; // Fail secure
        }
    }

    /**
     * Generate secure filename
     */
    public function generateSecureFilename(UploadedFile $file, string $prefix = ''): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $timestamp = now()->format('Y-m-d_H-i-s');
        $randomString = Str::random(16);
        
        return $prefix . $timestamp . '_' . $randomString . '.' . $extension;
    }

    /**
     * Store file securely
     */
    public function storeSecurely(UploadedFile $file, string $directory, string $type = 'image', string $disk = 'private'): array
    {
        $validation = $this->validateUploadedFile($file, $type);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        try {
            $secureFilename = $this->generateSecureFilename($file);
            $path = $file->storeAs($directory, $secureFilename, $disk);

            // Additional security: Set restrictive permissions
            if ($disk === 'local') {
                $fullPath = Storage::disk($disk)->path($path);
                chmod($fullPath, 0644); // Read-only for group/others
            }

            Log::info('File uploaded securely', [
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $path,
                'size' => $file->getSize(),
                'type' => $type
            ]);

            return [
                'success' => true,
                'path' => $path,
                'filename' => $secureFilename,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ];

        } catch (\Throwable $e) {
            Log::error('Secure file storage failed: ' . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['File storage failed: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}