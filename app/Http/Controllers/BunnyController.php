<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\SecureFileUploadService;

class BunnyController extends Controller
{
    /**
     * Mengunggah file video ke Bunny Stream menggunakan alur API 2 langkah yang benar.
     * Ini adalah satu-satunya fungsi yang Anda perlukan untuk unggah server-side.
     *
     * @param UploadedFile $file Objek file yang diunggah dari form.
     * @return string|null Mengembalikan Video ID (guid) jika berhasil, null jika gagal.
     */
    public static function uploadToStream(UploadedFile $file): ?string
    {
        // Enhanced security validation for video uploads
        $secureUploadService = new SecureFileUploadService();
        $validation = $secureUploadService->validateUploadedFile($file, 'video');
        
        if (!$validation['valid']) {
            Log::error('BunnyController: Video upload failed security validation', [
                'filename' => $file->getClientOriginalName(),
                'errors' => $validation['errors'],
                'file_info' => $validation['file_info'] ?? null
            ]);
            return null;
        }

        $libraryId = env('BUNNY_LIBRARY_ID');
        $apiKey = env('BUNNY_STREAM_API_KEY'); // Gunakan API Key khusus Stream

        if (! $libraryId || ! $apiKey) {
            Log::error('BunnyController: BUNNY_LIBRARY_ID atau BUNNY_STREAM_API_KEY tidak diatur di .env');
            return null;
        }

        // Sanitize video title for security
        $originalName = $file->getClientOriginalName();
        $videoTitle = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', pathinfo($originalName, PATHINFO_FILENAME));
        $videoTitle = substr($videoTitle, 0, 100); // Limit title length

        // LANGKAH 1: Buat placeholder video dan dapatkan Video ID (guid)
        $bunnyVideoBaseUrl = config('constants.api_endpoints.bunny_video_base');
        
        try {
            $responseCreate = Http::withHeaders([
                'AccessKey' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(30)->post("{$bunnyVideoBaseUrl}/library/{$libraryId}/videos", [
                'title' => $videoTitle,
            ]);

            if (! $responseCreate->successful()) {
                Log::error('Bunny Stream Error (Create): Gagal membuat placeholder video.', [
                    'status' => $responseCreate->status(),
                    'body' => $responseCreate->body(),
                    'filename' => $originalName
                ]);
                return null;
            }

            $videoGuid = $responseCreate->json('guid');

            if (empty($videoGuid)) {
                Log::error('Bunny Stream Error: Empty video GUID received');
                return null;
            }

        } catch (\Exception $e) {
            Log::error('Bunny Stream Exception (Create): ' . $e->getMessage(), [
                'filename' => $originalName
            ]);
            return null;
        }


        // LANGKAH 2: Unggah file video ke placeholder yang sudah dibuat
        try {
            // Additional content validation before upload
            $fileContent = $file->getContent();
            if (empty($fileContent)) {
                Log::error('BunnyController: Empty file content detected');
                return null;
            }

            $responseUpload = Http::withHeaders([
                'AccessKey' => $apiKey,
            ])->timeout(300) // 5 minutes for large video uploads
            ->withBody(
                $fileContent, $file->getMimeType()
            )->put("{$bunnyVideoBaseUrl}/library/{$libraryId}/videos/{$videoGuid}");

            if (! $responseUpload->successful()) {
                Log::error('Bunny Stream Error (Upload): Gagal mengunggah file video.', [
                    'status' => $responseUpload->status(),
                    'body' => $responseUpload->body(),
                    'video_guid' => $videoGuid,
                    'filename' => $originalName
                ]);
                return null;
            }

            // Log successful upload for security monitoring
            Log::info('Video uploaded successfully to Bunny Stream', [
                'video_guid' => $videoGuid,
                'original_filename' => $originalName,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'title' => $videoTitle
            ]);

        } catch (\Exception $e) {
            Log::error('Bunny Stream Exception (Upload): ' . $e->getMessage(), [
                'video_guid' => $videoGuid,
                'filename' => $originalName
            ]);
            return null;
        }

        // Jika semua berhasil, kembalikan Video ID untuk disimpan di database Anda
        return $videoGuid;
    }

    /**
     * Menghasilkan URL HLS (.m3u8) dasar yang belum ditandatangani (unsigned).
     *
     * @param string $guid Video ID dari video yang tersimpan.
     * @return string URL HLS.
     */
    public static function getStreamUrl(string $guid): string
    {
        $hostname = env('BUNNY_STREAM_HOSTNAME', 'video.b-cdn.net');
        return "https://{$hostname}/{$guid}/playlist.m3u8";
    }

    /**
     * Menghasilkan URL HLS yang aman dan sementara (Signed URL) untuk Bunny Stream.
     * Ini adalah cara yang BENAR untuk membuat URL video private.
     *
     * @param string $guid Video ID.
     * @param int $expiresInSeconds Waktu kedaluwarsa token dalam detik (default 1 jam).
     * @return string|null URL yang sudah ditandatangani, atau URL biasa jika signing key tidak ada.
     */
    public static function signStreamUrl(string $guid, ?int $expiresInSeconds = null): ?string
    {
        $expiresInSeconds = $expiresInSeconds ?? config('constants.business_logic.bunny_url_expiry_seconds');
        $signingKey = env('BUNNY_SIGNING_KEY');
        $libraryId = env('BUNNY_LIBRARY_ID');
        $hostname = env('BUNNY_STREAM_HOSTNAME', 'video.b-cdn.net');
        $baseUrl = self::getStreamUrl($guid);

        // If no signing key is configured, return unsigned URL (requires public library)
        if (! $signingKey) {
            return $baseUrl;
        }

        $expires = time() + (int) $expiresInSeconds;

        // Determine signing algorithm based on hostname
        // - For b-cdn.net (CDN token auth), Bunny uses MD5(expires + key + path)
        // - For video.bunnycdn.com or custom Stream hosts, use HMAC-SHA256 over path or library/video tuple
        $isCdnHost = stripos($hostname, 'b-cdn.net') !== false;

        if ($isCdnHost) {
            // CDN token auth: sign the request path including file part
            // Path that browser will request relative to host
            $path = '/' . ltrim("{$guid}/playlist.m3u8", '/');
            $token = md5($expires . $signingKey . $path);
            return sprintf('%s?token=%s&expires=%d', $baseUrl, $token, $expires);
        }

        // Stream token auth (HMAC-SHA256). Sign with path based on libraryId and guid for reliability.
        $pathToSign = '/' . trim($libraryId, '/') . '/' . trim($guid, '/');
            
        $rawSig = hash_hmac('sha256', $pathToSign . $expires, $signingKey, true);
        $token = rtrim(strtr(base64_encode($rawSig), '+/', '-_'), '=');
        return sprintf('%s?token=%s&expires=%d', $baseUrl, $token, $expires);
    }

    /**
     * Compatibility alias for older code that expects `signUrl`.
     * Delegates to `signStreamUrl`.
     *
     * @param string $guid
     * @param int $expiresInSeconds
     * @return string|null
     */
    public static function signUrl(string $guid, ?int $expiresInSeconds = null): ?string
    {
        $expiresInSeconds = $expiresInSeconds ?? config('constants.business_logic.bunny_url_expiry_seconds');
        return self::signStreamUrl($guid, $expiresInSeconds);
    }
    
    /**
     * Memeriksa status video (misalnya: 'finished', 'processing').
     *
     * @param string $guid Video ID.
     * @return array|null Status video atau null jika gagal.
     */
    public static function getVideoStatus(string $guid): ?array
    {
        $libraryId = env('BUNNY_LIBRARY_ID');
        $apiKey = env('BUNNY_STREAM_API_KEY');

        if (! $libraryId || ! $apiKey) {
            Log::error('BunnyController: BUNNY_LIBRARY_ID atau BUNNY_STREAM_API_KEY tidak diatur.');
            return null;
        }

        try {
            $bunnyVideoBaseUrl = config('constants.api_endpoints.bunny_video_base');
            
            $response = Http::withHeaders([
                'AccessKey' => $apiKey,
                'Accept' => 'application/json',
            ])->get("{$bunnyVideoBaseUrl}/library/{$libraryId}/videos/{$guid}");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Bunny Stream Exception (GetStatus): ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Compatibility alias for older code that expects `cdnUrl`.
     * Delegates to `getStreamUrl`.
     *
     * @param string $guid
     * @return string
     */
    public static function cdnUrl(string $guid): string
    {
        return self::getStreamUrl($guid);
    }

    /**
     * Return a signed thumbnail URL for a Bunny Stream GUID.
     * This assumes Bunny serves a thumbnail at /{guid}/thumbnail.jpg — adjust if your setup differs.
     */
    public static function signThumbnailUrl(string $guid, ?int $expiresInSeconds = null, string $filename = 'thumbnail.jpg'): ?string
    {
        $expiresInSeconds = $expiresInSeconds ?? config('constants.business_logic.bunny_url_expiry_seconds');
        $signingKey = env('BUNNY_SIGNING_KEY');
        $libraryId = env('BUNNY_LIBRARY_ID');
        $hostname = env('BUNNY_STREAM_HOSTNAME', 'video.b-cdn.net');
        // Allow filename from metadata (thumbnailFileName) or default
        $baseUrl = "https://{$hostname}/{$guid}/{$filename}";

        if (! $signingKey) {
            return $baseUrl;
        }

        $expirationTimestamp = time() + $expiresInSeconds;
        $hashableString = $libraryId . $signingKey . $expirationTimestamp . $guid;
        $signature = hash('sha256', $hashableString, true);
        $token = base64_encode($signature);
        $token = strtr($token, '+/', '-_');
        $token = rtrim($token, '=');

        return "{$baseUrl}?token={$token}&expires={$expirationTimestamp}";
    }

    /**
     * Route wrapper: create upload URL for client-side upload.
     * NOTE: current implementation does not provide a direct-to-storage signed URL.
     * For now return a clear message and suggest using server-side upload.
     */
    public function createUploadUrl(Request $request)
    {
        // Keep a helpful JSON response so frontend can handle it.
        return response()->json([
            'success' => false,
            'message' => 'Direct browser upload not available on this server. Use the server upload endpoint.',
            'upload_url' => null,
        ], 501);
    }

    /**
     * Route wrapper to upload file from server to Bunny Stream.
     * Expects multipart form with field 'video' or 'video_file'.
     */
    public function uploadToBunny(Request $request)
    {
        // Enhanced validation with security checks
        $request->validate([
            'video' => 'sometimes|file|max:512000', // 500MB max
            'video_file' => 'sometimes|file|max:512000',
        ]);

        $file = $request->file('video') ?: $request->file('video_file');

        if (! $file) {
            Log::warning('BunnyController uploadToBunny: No file provided', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return response()->json(['success' => false, 'message' => 'No file provided.'], 422);
        }

        // Rate limiting check for upload attempts
        $rateLimitKey = 'video_upload:' . $request->ip();
        $attempts = cache()->get($rateLimitKey, 0);
        if ($attempts >= 10) { // Max 10 uploads per hour per IP
            Log::warning('Video upload rate limit exceeded', [
                'ip' => $request->ip(),
                'attempts' => $attempts
            ]);
            return response()->json([
                'success' => false, 
                'message' => 'Upload rate limit exceeded. Please try again later.'
            ], 429);
        }

        // Increment attempt counter
        cache()->put($rateLimitKey, $attempts + 1, now()->addHour());

        try {
            $guid = self::uploadToStream($file);
        } catch (\Exception $e) {
            Log::error('Bunny uploadToBunny exception: ' . $e->getMessage(), [
                'filename' => $file->getClientOriginalName(),
                'ip' => $request->ip()
            ]);
            return response()->json(['success' => false, 'message' => 'Exception during upload.'], 500);
        }

        if (! $guid) {
            Log::warning('Video upload failed', [
                'filename' => $file->getClientOriginalName(),
                'ip' => $request->ip(),
                'size' => $file->getSize()
            ]);
            return response()->json(['success' => false, 'message' => 'Upload failed. Check server logs.'], 500);
        }

        // Success - reset rate limit counter on successful upload
        cache()->forget($rateLimitKey);

        Log::info('Video upload API success', [
            'guid' => $guid,
            'filename' => $file->getClientOriginalName(),
            'ip' => $request->ip()
        ]);

        return response()->json(['success' => true, 'guid' => $guid]);
    }

    /**
     * Route wrapper: return video status JSON for a guid.
     */
    public function videoStatus(string $guid)
    {
        $status = self::getVideoStatus($guid);
        if (! $status) {
            return response()->json(['success' => false, 'message' => 'Not found or failed to retrieve status.'], 404);
        }
        return response()->json(['success' => true, 'data' => $status]);
    }
}