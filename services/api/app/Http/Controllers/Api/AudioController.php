<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AudioController extends Controller
{
    /**
     * Stream or cache high-fidelity native Spanish educational audio.
     */
    public function tts(Request $request)
    {
        $text = trim((string) $request->query('text', ''));
        $lang = (string) $request->query('lang', 'es');

        if (empty($text)) {
            return response()->json(['message' => 'Text parameter is required'], 422);
        }

        // Limit length to avoid abuse
        if (mb_strlen($text) > 400) {
            $text = mb_substr($text, 0, 400);
        }

        // Clean punctuation marks that degrade pronunciation
        $cleanText = preg_replace('/[¿¡]/u', '', $text);
        $cleanText = trim(preg_replace('/\s+/', ' ', $cleanText));

        if (empty($cleanText)) {
            return response()->json(['message' => 'Valid text required'], 422);
        }

        $cacheDir = storage_path('app/tts');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $filename = md5($lang . '_' . mb_strtolower($cleanText)) . '.mp3';
        $filePath = $cacheDir . DIRECTORY_SEPARATOR . $filename;

        // 1. Check local cached file
        if (file_exists($filePath) && filesize($filePath) > 300) {
            return response()->file($filePath, [
                'Content-Type' => 'audio/mpeg',
                'Cache-Control' => 'public, max-age=2592000, immutable',
                'Access-Control-Allow-Origin' => '*',
            ]);
        }

        // 2. Fetch from reliable high-quality native voice provider
        try {
            $ttsUrl = 'https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&tl=' . urlencode($lang) . '&q=' . urlencode($cleanText);
            
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'audio/mpeg,audio/*;q=0.9,*/*;q=0.8',
            ])->withoutVerifying()->timeout(8)->get($ttsUrl);

            if ($response->successful() && strlen($response->body()) > 300) {
                @file_put_contents($filePath, $response->body());

                return response()->file($filePath, [
                    'Content-Type' => 'audio/mpeg',
                    'Cache-Control' => 'public, max-age=2592000, immutable',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }
        } catch (\Throwable $e) {
            // Log fallback indicator
        }

        return response()->json([
            'message' => 'Audio synthesis unavailable',
            'fallback_to_browser' => true
        ], 503);
    }
}
