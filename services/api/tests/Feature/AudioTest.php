<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AudioTest extends TestCase
{
    /** Test 1: Capabilities endpoint reports European Spanish variety and disclaimers */
    public function test_audio_capabilities_endpoint_returns_metadata_and_disclaimers(): void
    {
        $response = $this->getJson('/api/v1/audio/capabilities');

        $response->assertStatus(200)
            ->assertJsonPath('preferred_variety', 'es-ES')
            ->assertJsonPath('cache_enabled', true)
            ->assertJsonStructure([
                'preferred_variety',
                'preferred_variety_label',
                'primary_provider',
                'fallback_provider',
                'cache_enabled',
                'max_text_length',
                'disclaimer_en',
                'disclaimer_ar',
                'phonetic_accuracy_notice_en',
                'phonetic_accuracy_notice_ar',
            ]);

        $this->assertStringContainsString('es-ES', $response->json('disclaimer_en'));
        $this->assertStringContainsString('مطابقة الكلمات المنطوقة', $response->json('phonetic_accuracy_notice_ar'));
    }

    /** Test 2: Validation on TTS text parameter */
    public function test_audio_tts_requires_valid_text(): void
    {
        // Missing parameter
        $resEmpty = $this->getJson('/api/v1/audio/tts');
        $resEmpty->assertStatus(422)
            ->assertJsonValidationErrors(['text']);

        // Blank parameter
        $resBlank = $this->getJson('/api/v1/audio/tts?text=%20%20');
        $resBlank->assertStatus(422);
    }

    /** Test 3: Audio TTS caching and stream headers */
    public function test_audio_tts_streams_cached_audio_or_falls_back_gracefully(): void
    {
        $cacheDir = storage_path('app/tts');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $testText = 'buenos días';
        $filename = md5('es_' . mb_strtolower($testText)) . '.mp3';
        $filePath = $cacheDir . DIRECTORY_SEPARATOR . $filename;

        // Create a mock MP3 file > 300 bytes
        $dummyMp3 = str_repeat("ID3\x04\x00\x00\x00\x00\x00#DUMMY_MP3_AUDIO_STREAM_DATA", 12);
        @file_put_contents($filePath, $dummyMp3);

        $response = $this->get('/api/v1/audio/tts?text=' . urlencode($testText));

        $response->assertStatus(200);
        $this->assertEquals('audio/mpeg', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
        $this->assertEquals('local_disk_cache', $response->headers->get('X-Audio-Source'));

        // Clean up mock file
        @unlink($filePath);
    }

    /** Test 4: Graceful 503 fallback when upstream network is unavailable */
    public function test_audio_tts_handles_upstream_failure_with_browser_fallback_directive(): void
    {
        // Fake upstream to fail
        Http::fake([
            'translate.google.com/*' => Http::response(null, 500),
        ]);

        $uniqueText = 'prueba_fallback_sin_cache_' . uniqid();
        $response = $this->getJson('/api/v1/audio/tts?text=' . urlencode($uniqueText));

        $response->assertStatus(503)
            ->assertJsonPath('fallback_to_browser', true)
            ->assertJsonPath('variety', 'es-ES');
    }
}
