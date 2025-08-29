<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $libraryId = env('BUNNY_LIBRARY_ID');
        $apiKey = env('BUNNY_STREAM_API_KEY'); // Gunakan API Key khusus Stream

        if (! $libraryId || ! $apiKey) {
            Log::error('BunnyController: BUNNY_LIBRARY_ID atau BUNNY_STREAM_API_KEY tidak diatur di .env');
            return null;
        }

        $videoTitle = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // LANGKAH 1: Buat placeholder video dan dapatkan Video ID (guid)
        try {
            $responseCreate = Http::withHeaders([
                'AccessKey' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("https://video.bunnycdn.com/library/{$libraryId}/videos", [
                'title' => $videoTitle,
            ]);

            if (! $responseCreate->successful()) {
                Log::error('Bunny Stream Error (Create): Gagal membuat placeholder video.', [
                    'status' => $responseCreate->status(),
                    'body' => $responseCreate->body(),
                ]);
                return null;
            }

            $videoGuid = $responseCreate->json('guid');

        } catch (\Exception $e) {
            Log::error('Bunny Stream Exception (Create): ' . $e->getMessage());
            return null;
        }


        // LANGKAH 2: Unggah file video ke placeholder yang sudah dibuat
        try {
            $responseUpload = Http::withHeaders([
                'AccessKey' => $apiKey,
            ])->withBody(
                $file->getContent(), $file->getMimeType()
            )->put("https://video.bunnycdn.com/library/{$libraryId}/videos/{$videoGuid}");

            if (! $responseUpload->successful()) {
                Log::error('Bunny Stream Error (Upload): Gagal mengunggah file video.', [
                    'status' => $responseUpload->status(),
                    'body' => $responseUpload->body(),
                ]);
                return null;
            }

        } catch (\Exception $e) {
            Log::error('Bunny Stream Exception (Upload): ' . $e->getMessage());
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
    public static function signStreamUrl(string $guid, int $expiresInSeconds = 3600): ?string
    {
        $signingKey = env('BUNNY_SIGNING_KEY');
        $libraryId = env('BUNNY_LIBRARY_ID');
        $baseUrl = self::getStreamUrl($guid);

        // Jika tidak ada signing key, kembalikan URL biasa (video akan menjadi publik)
        if (! $signingKey) {
            return $baseUrl;
        }

        $expirationTimestamp = time() + $expiresInSeconds;
        
        // Format string yang akan di-hash untuk Bunny Stream
        $hashableString = $libraryId . $signingKey . $expirationTimestamp . $guid;
        
        $signature = hash('sha256', $hashableString, true);
        $token = base64_encode($signature);
        
        // Membuat token menjadi URL-safe
        $token = strtr($token, '+/', '-_');
        $token = rtrim($token, '=');

        return "{$baseUrl}?token={$token}&expires={$expirationTimestamp}";
    }

    /**
     * Compatibility alias for older code that expects `signUrl`.
     * Delegates to `signStreamUrl`.
     *
     * @param string $guid
     * @param int $expiresInSeconds
     * @return string|null
     */
    public static function signUrl(string $guid, int $expiresInSeconds = 3600): ?string
    {
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
            $response = Http::withHeaders([
                'AccessKey' => $apiKey,
                'Accept' => 'application/json',
            ])->get("https://video.bunnycdn.com/library/{$libraryId}/videos/{$guid}");

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
    public static function signThumbnailUrl(string $guid, int $expiresInSeconds = 3600, string $filename = 'thumbnail.jpg'): ?string
    {
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
        $request->validate([
            'video' => 'sometimes|file',
            'video_file' => 'sometimes|file',
        ]);

        $file = $request->file('video') ?: $request->file('video_file');

        if (! $file) {
            return response()->json(['success' => false, 'message' => 'No file provided.'], 422);
        }

        try {
            $guid = self::uploadToStream($file);
        } catch (\Exception $e) {
            Log::error('Bunny uploadToBunny exception: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Exception during upload.'], 500);
        }

        if (! $guid) {
            return response()->json(['success' => false, 'message' => 'Upload failed. Check server logs.'], 500);
        }

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