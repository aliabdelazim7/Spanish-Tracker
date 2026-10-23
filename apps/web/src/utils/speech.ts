/**
 * High-Fidelity Educational Speech Utility for Native Spanish Pronunciation.
 *
 * Pipeline:
 * 1. Primary: Studio-quality native Spanish voice stream from backend TTS cache (/api/v1/audio/tts).
 * 2. Secondary Fallback: Enhanced Web Speech API with prioritized natural voices (Google / Microsoft Natural).
 */

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';

let currentAudio: HTMLAudioElement | null = null;
let currentResolve: (() => void) | null = null;

/**
 * Stop any ongoing audio playback immediately (HTML5 Audio or Web Speech).
 */
export function stopSpanishAudio(): void {
  if (currentAudio) {
    try {
      currentAudio.pause();
      currentAudio.currentTime = 0;
      currentAudio = null;
    } catch {
      // Ignore
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
 * Plays Spanish text with clear native teacher-grade audio.
 * @param text The Spanish text to pronounce.
 * @param rate Playback speed (1.0 = normal natural, 0.75 = slow educational).
 */
export function playSpanishAudio(text: string, rate: number = 1.0): Promise<void> {
  return new Promise((resolve) => {
    // Stop any active audio to prevent overlapping speech
    stopSpanishAudio();
    currentResolve = resolve;

    // Clean text of non-spoken editorial symbols
    const cleanText = text
      .replace(/[¿¡()]/g, '')
      .replace(/\s+/g, ' ')
      .trim();

    if (!cleanText) {
      resolve();
      return;
    }

    // Try HTML5 Audio via native educational TTS API first
    const audioUrl = `${API_BASE_URL}/audio/tts?text=${encodeURIComponent(cleanText)}&lang=es`;
    const audio = new Audio();
    currentAudio = audio;

    let hasFallbackRun = false;

    const runFallbackSpeechSynthesis = () => {
      if (hasFallbackRun) return;
      hasFallbackRun = true;

      if (typeof window === 'undefined' || !('speechSynthesis' in window)) {
        resolve();
        return;
      }

      try {
        window.speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(cleanText);
        utterance.lang = 'es-ES';
        utterance.rate = Math.min(Math.max(rate * 0.9, 0.6), 1.2);
        utterance.pitch = 1.0;

        const voices = window.speechSynthesis.getVoices();
        // Priority: Natural European Spanish > Any European Spanish > Any Spanish > First available
        const preferredVoice =
          voices.find((v) => v.lang === 'es-ES' && /natural|online|google|helena|laura/i.test(v.name)) ||
          voices.find((v) => v.lang === 'es-ES') ||
          voices.find((v) => v.lang.startsWith('es') && /natural|google/i.test(v.name)) ||
          voices.find((v) => v.lang.startsWith('es')) ||
          null;

        if (preferredVoice) {
          utterance.voice = preferredVoice;
        }

        utterance.onend = () => {
          if (currentResolve === resolve) currentResolve = null;
          resolve();
        };
        utterance.onerror = () => {
          if (currentResolve === resolve) currentResolve = null;
          resolve();
        };

        window.speechSynthesis.speak(utterance);
      } catch {
        resolve();
      }
    };

    // Timeout safety fallback: if backend audio stalls for > 3.5s, fallback to Web Speech
    const fallbackTimeout = setTimeout(() => {
      if (currentAudio === audio && audio.readyState < 2) {
        audio.pause();
        currentAudio = null;
        runFallbackSpeechSynthesis();
      }
    }, 3500);

    audio.playbackRate = Math.max(0.6, Math.min(rate, 1.4));

    audio.oncanplaythrough = () => {
      clearTimeout(fallbackTimeout);
    };

    audio.onended = () => {
      clearTimeout(fallbackTimeout);
      if (currentAudio === audio) currentAudio = null;
      if (currentResolve === resolve) currentResolve = null;
      resolve();
    };

    audio.onerror = () => {
      clearTimeout(fallbackTimeout);
      if (currentAudio === audio) currentAudio = null;
      runFallbackSpeechSynthesis();
    };

    // Trigger playback
    audio.src = audioUrl;
    audio.play().catch(() => {
      // Autoplay restriction or network error -> fallback to Web Speech
      clearTimeout(fallbackTimeout);
      runFallbackSpeechSynthesis();
    });
  });
}
