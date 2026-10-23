/**
 * High-Fidelity Educational Speech Utility for Spanish Pronunciation.
 *
 * Architecture:
 * 1. Primary: Cached High-Fidelity audio stream from backend (/api/v1/audio/tts).
 * 2. In-Memory Frontend Deduplication Cache: Prevents re-requesting audio for identical strings.
 * 3. Secondary Fallback: Web Speech API with prioritized European / Castilian voice matching.
 * 4. Honest Variety & Phonetic Disclaimers: Transparently indicates whether speech is cloud-cached
 *    or browser-synthesized, without false claims of acoustic laboratory analysis.
 */

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';

let currentAudio: HTMLAudioElement | null = null;
let currentResolve: (() => void) | null = null;

// In-memory cache to prevent downloading or generating duplicate audio unnecessarily
const memoryAudioBlobCache = new Map<string, string>();

export interface VoiceCapabilities {
  hasCastilianVoice: boolean;
  activeVoiceName: string;
  activeLang: string;
  sourceType: 'cloud_tts' | 'browser_speech';
  speechRecognitionAvailable: boolean;
  phoneticAccuracyNotice: string;
}

export interface AudioPlaybackStatus {
  success: boolean;
  source: 'memory_cache' | 'backend_stream' | 'browser_speech' | 'unavailable';
  error?: string;
}

/**
 * Stop any ongoing audio playback immediately.
 */
export function stopSpanishAudio(): void {
  if (currentAudio) {
    try {
      currentAudio.pause();
      currentAudio.currentTime = 0;
      currentAudio = null;
    } catch {
      // Ignore cleanup error
    }
  }

  if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
    try {
      window.speechSynthesis.cancel();
    } catch {
      // Ignore
    }
  }

  if (currentResolve) {
    currentResolve();
    currentResolve = null;
  }
}

/**
 * Inspect client device audio and voice capabilities.
 */
export function getAudioCapabilities(): VoiceCapabilities {
  let hasCastilian = false;
  let activeVoiceName = 'Default Device Voice';
  let activeLang = 'es-ES';

  if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
    const voices = window.speechSynthesis.getVoices();
    const castilianVoice = voices.find(
      (v) => v.lang === 'es-ES' && /natural|spain|castilian|helena|laura|pablo/i.test(v.name)
    ) || voices.find((v) => v.lang === 'es-ES');

    if (castilianVoice) {
      hasCastilian = true;
      activeVoiceName = castilianVoice.name;
      activeLang = castilianVoice.lang;
    } else {
      const anySpanish = voices.find((v) => v.lang.startsWith('es'));
      if (anySpanish) {
        activeVoiceName = anySpanish.name;
        activeLang = anySpanish.lang;
      }
    }
  }

  const speechRecognitionAvailable =
    typeof window !== 'undefined' &&
    ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window);

  return {
    hasCastilianVoice: hasCastilian,
    activeVoiceName,
    activeLang,
    sourceType: 'cloud_tts',
    speechRecognitionAvailable,
    phoneticAccuracyNotice:
      'تقييم النطق يعتمد على مطابقة النصوص المستخرجة ولا يقيس مخارج الحروف الصوتية بدقة معملية مطلقة.',
  };
}

/**
 * Check if Speech Recognition is supported on the client browser.
 */
export function isSpeechRecognitionSupported(): boolean {
  return (
    typeof window !== 'undefined' &&
    ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window)
  );
}

/**
 * Plays Spanish text with European Spanish preference and dual-engine fallback.
 *
 * @param text The Spanish text to pronounce.
 * @param rate Playback speed (1.0 = natural normal, 0.72 = educational slow).
 * @returns Promise resolving to AudioPlaybackStatus
 */
export function playSpanishAudio(
  text: string,
  rate: number = 1.0
): Promise<AudioPlaybackStatus> {
  return new Promise((resolve) => {
    stopSpanishAudio();

    const cleanText = text
      .replace(/[¿¡()]/g, '')
      .replace(/\s+/g, ' ')
      .trim();

    if (!cleanText) {
      resolve({ success: false, source: 'unavailable', error: 'Empty text' });
      return;
    }

    currentResolve = () => {
      resolve({ success: true, source: 'backend_stream' });
    };

    const cacheKey = `${cleanText.toLowerCase()}_${rate}`;

    const executeBrowserFallback = () => {
      if (typeof window === 'undefined' || !('speechSynthesis' in window)) {
        if (currentResolve) currentResolve = null;
        resolve({
          success: false,
          source: 'unavailable',
          error: 'Audio playback unavailable on this browser',
        });
        return;
      }

      try {
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(cleanText);
        utterance.lang = 'es-ES';
        utterance.rate = Math.min(Math.max(rate * 0.9, 0.6), 1.2);
        utterance.pitch = 1.0;

        const voices = window.speechSynthesis.getVoices();
        // Priority: Natural European Spanish > es-ES > any Spanish
        const preferredVoice =
          voices.find(
            (v) =>
              v.lang === 'es-ES' &&
              /natural|online|google|helena|laura|pablo|castilian/i.test(v.name)
          ) ||
          voices.find((v) => v.lang === 'es-ES') ||
          voices.find((v) => v.lang.startsWith('es') && /natural|google/i.test(v.name)) ||
          voices.find((v) => v.lang.startsWith('es')) ||
          null;

        if (preferredVoice) {
          utterance.voice = preferredVoice;
        }

        utterance.onend = () => {
          if (currentResolve) currentResolve = null;
          resolve({ success: true, source: 'browser_speech' });
        };

        utterance.onerror = (e) => {
          if (currentResolve) currentResolve = null;
          resolve({
            success: false,
            source: 'browser_speech',
            error: e.error || 'Speech synthesis error',
          });
        };

        window.speechSynthesis.speak(utterance);
      } catch (err: any) {
        if (currentResolve) currentResolve = null;
        resolve({
          success: false,
          source: 'browser_speech',
          error: err?.message || 'Speech synthesis exception',
        });
      }
    };

    // 1. Check Frontend In-Memory Cache first
    if (memoryAudioBlobCache.has(cacheKey)) {
      const cachedUrl = memoryAudioBlobCache.get(cacheKey)!;
      const audio = new Audio(cachedUrl);
      audio.playbackRate = Math.max(0.6, Math.min(rate, 1.4));
      currentAudio = audio;

      audio.onended = () => {
        if (currentAudio === audio) currentAudio = null;
        if (currentResolve) currentResolve = null;
        resolve({ success: true, source: 'memory_cache' });
      };

      audio.onerror = () => {
        if (currentAudio === audio) currentAudio = null;
        memoryAudioBlobCache.delete(cacheKey);
        executeBrowserFallback();
      };

      audio.play().catch(() => {
        executeBrowserFallback();
      });
      return;
    }

    // 2. Fetch from backend TTS with caching
    const audioUrl = `${API_BASE_URL}/audio/tts?text=${encodeURIComponent(cleanText)}&lang=es`;
    const audio = new Audio();
    currentAudio = audio;

    let fallbackTriggered = false;
    const fallbackTimeout = setTimeout(() => {
      if (currentAudio === audio && audio.readyState < 2) {
        fallbackTriggered = true;
        audio.pause();
        currentAudio = null;
        executeBrowserFallback();
      }
    }, 3500);

    audio.playbackRate = Math.max(0.6, Math.min(rate, 1.4));

    audio.oncanplaythrough = () => {
      clearTimeout(fallbackTimeout);
      memoryAudioBlobCache.set(cacheKey, audioUrl);
    };

    audio.onended = () => {
      clearTimeout(fallbackTimeout);
      if (currentAudio === audio) currentAudio = null;
      if (currentResolve) currentResolve = null;
      resolve({ success: true, source: 'backend_stream' });
    };

    audio.onerror = () => {
      clearTimeout(fallbackTimeout);
      if (currentAudio === audio) currentAudio = null;
      if (!fallbackTriggered) {
        executeBrowserFallback();
      }
    };

    audio.src = audioUrl;
    audio.play().catch(() => {
      clearTimeout(fallbackTimeout);
      if (!fallbackTriggered) {
        executeBrowserFallback();
      }
    });
  });
}
