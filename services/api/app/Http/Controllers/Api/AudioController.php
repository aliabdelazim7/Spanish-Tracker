<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AudioController extends Controller
{
    /**
     * Get audio engine capabilities, preferred European Spanish variety, and provider metadata.
     */
    public function capabilities(): JsonResponse
    {
        return response()->json([
            'preferred_variety' => 'es-ES',
            'preferred_variety_label' => 'Español Europeo / Castellano',
            'primary_provider' => 'google_tts_cached',
            'fallback_provider' => 'browser_speech_synthesis',
            'cache_enabled' => true,
            'max_text_length' => 400,
            'disclaimer_en' => 'Browser speech synthesis uses the device operating system voice pack. Requesting es-ES does not guarantee a Castilian accent if your operating system lacks a Spain voice pack.',
            'disclaimer_ar' => 'نطق المتصفح يعتمد على حزم الأصوات المثبتة بنظام تشغيل جهازك. اختيار es-ES يحاول تفعيل النطق القشتالي ولكنه قد يرجع لصوت إسباني بديل إذا لم تتوفر حزمة إسبانيا على جهازك.',
            'phonetic_accuracy_notice_en' => 'Speech evaluation uses lexical transcription matching. It provides guided practice and should not be mistaken for laboratory acoustic phonetic analysis.',
            'phonetic_accuracy_notice_ar' => 'تقييم النطق يعتمد على مطابقة الكلمات المنطوقة مع النص المستهدف، وهو أداة إرشادية وتدريبية ولا يمثل تحليلاً صوتياً معملياً لمخارج الحروف.',
        ]);
    }

    /**
     * Stream or cache high-fidelity native Spanish educational audio.
     */
    public function tts(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'text' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Text parameter is required',
                'errors' => $validator->errors(),
            ], 422);
        }

        $text = trim((string) $request->query('text', ''));
        $lang = (string) $request->query('lang', 'es');

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
                'X-Audio-Source' => 'local_disk_cache',
                'X-Audio-Variety' => $lang === 'es' ? 'es-ES' : $lang,
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
                    'X-Audio-Source' => 'upstream_tts_provider',
                    'X-Audio-Variety' => $lang === 'es' ? 'es-ES' : $lang,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Upstream TTS fetch failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Audio synthesis upstream unavailable, fallback to browser speech synthesis',
            'fallback_to_browser' => true,
            'variety' => 'es-ES',
        ], 503);
    }
}
