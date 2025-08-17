<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Download a generated report.
     */
    public function download(Request $request)
    {
        try {
            // Validate request parameters
            $request->validate([
                'file' => 'required|string',
                'token' => 'required|string'
            ]);

            $filePath = base64_decode($request->file);
            $token = $request->token;

            // Validate download token
            if (!$this->validateDownloadToken($token, $filePath)) {
                return response()->json([
                    'error' => 'Invalid or expired download token'
                ], 403);
            }

            // Check if file exists
            if (!Storage::exists($filePath)) {
                return response()->json([
                    'error' => 'Report file not found'
                ], 404);
            }

            // Get file info
            $fileName = basename($filePath);
            $mimeType = $this->getMimeType($fileName);
            $fileSize = Storage::size($filePath);

            // Log download activity
            Log::info('Report downloaded', [
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'user_id' => auth()->id(),
                'ip_address' => $request->ip()
            ]);

            // Return file download response
            return Storage::download($filePath, $fileName, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            ]);

        } catch (\Exception $e) {
            Log::error('Report download failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Failed to download report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate download token.
     */
    private function validateDownloadToken(string $token, string $filePath): bool
    {
        $cachedFilePath = Cache::get("download_token_{$token}");

        if (!$cachedFilePath) {
            return false;
        }

        // Check if token matches file path
        if ($cachedFilePath !== $filePath) {
            return false;
        }

        // Token is valid, remove it to prevent reuse
        Cache::forget("download_token_{$token}");

        return true;
    }

    /**
     * Get MIME type based on file extension.
     */
    private function getMimeType(string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return match ($extension) {
            'csv' => 'text/csv',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'pdf' => 'application/pdf',
            'json' => 'application/json',
            default => 'application/octet-stream'
        };
    }
}
