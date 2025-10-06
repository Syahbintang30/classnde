<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FileUploadSecurityMiddleware
{
    /**
     * Security middleware for file upload endpoints
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to POST/PUT requests with files
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH']) || !$request->hasFile('*')) {
            return $next($request);
        }

        // Rate limiting for file uploads
        $rateLimitKey = 'file_upload:' . $request->ip();
        $attempts = cache()->get($rateLimitKey, 0);
        
        if ($attempts >= 20) { // Max 20 file uploads per hour per IP
            Log::warning('File upload rate limit exceeded', [
                'ip' => $request->ip(),
                'attempts' => $attempts,
                'url' => $request->url(),
                'user_agent' => $request->userAgent()
            ]);
            
            return response()->json([
                'error' => 'Too many upload attempts. Please try again later.'
            ], 429);
        }

        // Increment attempt counter
        cache()->put($rateLimitKey, $attempts + 1, now()->addHour());

        // Check for suspicious file upload patterns
        $this->detectSuspiciousUploadPatterns($request);

        // Monitor large file uploads
        $this->monitorLargeUploads($request);

        $response = $next($request);

        // Log successful uploads for monitoring
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->logSuccessfulUpload($request);
        }

        return $response;
    }

    /**
     * Detect suspicious upload patterns
     */
    private function detectSuspiciousUploadPatterns(Request $request): void
    {
        $files = $request->allFiles();
        $suspiciousCount = 0;
        $totalSize = 0;

        foreach ($files as $file) {
            if (is_array($file)) {
                foreach ($file as $subFile) {
                    $this->checkIndividualFile($subFile, $suspiciousCount, $totalSize);
                }
            } else {
                $this->checkIndividualFile($file, $suspiciousCount, $totalSize);
            }
        }

        // Alert on suspicious patterns
        if ($suspiciousCount > 0 || $totalSize > 1073741824) { // 1GB total
            Log::warning('Suspicious file upload pattern detected', [
                'ip' => $request->ip(),
                'suspicious_count' => $suspiciousCount,
                'total_size' => $totalSize,
                'file_count' => count($files, COUNT_RECURSIVE),
                'url' => $request->url(),
                'user_agent' => $request->userAgent()
            ]);
        }
    }

    /**
     * Check individual file for suspicious characteristics
     */
    private function checkIndividualFile($file, int &$suspiciousCount, int &$totalSize): void
    {
        if (!$file || !$file->isValid()) {
            return;
        }

        $filename = $file->getClientOriginalName();
        $size = $file->getSize();
        $totalSize += $size;

        // Check for suspicious filenames
        $suspiciousPatterns = [
            '/\.php$/i',
            '/\.asp$/i',
            '/\.jsp$/i',
            '/\.exe$/i',
            '/\.bat$/i',
            '/\.cmd$/i',
            '/\.scr$/i',
            '/\.vbs$/i',
            '/\.js$/i',
            '/shell/i',
            '/backdoor/i',
            '/hack/i',
            '/exploit/i',
            '/payload/i'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                $suspiciousCount++;
                break;
            }
        }

        // Check for unusually large files (>100MB for images, >1GB for videos)
        $extension = strtolower($file->getClientOriginalExtension());
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $videoExtensions = ['mp4', 'mov', 'avi', 'webm'];

        if (in_array($extension, $imageExtensions) && $size > 104857600) { // 100MB
            $suspiciousCount++;
        } elseif (in_array($extension, $videoExtensions) && $size > 1073741824) { // 1GB
            $suspiciousCount++;
        }
    }

    /**
     * Monitor large file uploads
     */
    private function monitorLargeUploads(Request $request): void
    {
        $files = $request->allFiles();
        $largeFiles = [];

        foreach ($files as $file) {
            if (is_array($file)) {
                foreach ($file as $subFile) {
                    if ($subFile && $subFile->isValid() && $subFile->getSize() > 52428800) { // 50MB
                        $largeFiles[] = [
                            'name' => $subFile->getClientOriginalName(),
                            'size' => $subFile->getSize(),
                            'mime_type' => $subFile->getMimeType()
                        ];
                    }
                }
            } else {
                if ($file && $file->isValid() && $file->getSize() > 52428800) { // 50MB
                    $largeFiles[] = [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ];
                }
            }
        }

        if (!empty($largeFiles)) {
            Log::info('Large file upload detected', [
                'ip' => $request->ip(),
                'files' => $largeFiles,
                'url' => $request->url(),
                'user_id' => Auth::id()
            ]);
        }
    }

    /**
     * Log successful file uploads
     */
    private function logSuccessfulUpload(Request $request): void
    {
        $files = $request->allFiles();
        
        if (empty($files)) {
            return;
        }

        $uploadInfo = [];
        foreach ($files as $key => $file) {
            if (is_array($file)) {
                foreach ($file as $index => $subFile) {
                    if ($subFile && $subFile->isValid()) {
                        $uploadInfo[] = [
                            'field' => $key . '[' . $index . ']',
                            'name' => $subFile->getClientOriginalName(),
                            'size' => $subFile->getSize(),
                            'mime_type' => $subFile->getMimeType()
                        ];
                    }
                }
            } else {
                if ($file && $file->isValid()) {
                    $uploadInfo[] = [
                        'field' => $key,
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType()
                    ];
                }
            }
        }

        if (!empty($uploadInfo)) {
            Log::info('File upload completed', [
                'ip' => $request->ip(),
                'files' => $uploadInfo,
                'url' => $request->url(),
                'user_id' => Auth::id()
            ]);
        }
    }
}