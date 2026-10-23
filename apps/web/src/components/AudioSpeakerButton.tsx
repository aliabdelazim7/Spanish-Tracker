import React, { useState } from 'react';
import { Volume2, Snail, Loader2, AlertCircle } from 'lucide-react';
import { playSpanishAudio, stopSpanishAudio, AudioPlaybackStatus } from '../utils/speech';

interface AudioSpeakerButtonProps {
  text: string;
  className?: string;
  size?: 'sm' | 'md' | 'lg';
  rate?: number;
  label?: string;
  showSlowButton?: boolean;
  approximateArabicHint?: string;
}

export const AudioSpeakerButton: React.FC<AudioSpeakerButtonProps> = ({
  text,
  className = '',
  size = 'md',
  rate = 1.0,
  label,
  showSlowButton = false,
  approximateArabicHint,
}) => {
  const [playingSpeed, setPlayingSpeed] = useState<'normal' | 'slow' | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [playbackError, setPlaybackError] = useState<string | null>(null);

  const handlePlay = async (
    e: React.MouseEvent | React.KeyboardEvent,
    targetRate: number,
    speedType: 'normal' | 'slow'
  ) => {
    e.stopPropagation();

    if (playingSpeed === speedType) {
      stopSpanishAudio();
      setPlayingSpeed(null);
      return;
    }

    setPlaybackError(null);
    setIsLoading(true);
    setPlayingSpeed(speedType);

    try {
      const result: AudioPlaybackStatus = await playSpanishAudio(text, targetRate);
      if (!result.success) {
        setPlaybackError(result.error || 'تعذر تشغيل الصوت');
      }
    } catch {
      setPlaybackError('خطأ أثناء تشغيل الصوت');
    } finally {
      setIsLoading(false);
      setPlayingSpeed(null);
    }
  };

  const handleKeyDown = (
    e: React.KeyboardEvent,
    targetRate: number,
    speedType: 'normal' | 'slow'
  ) => {
    if (e.key === ' ' || e.key === 'Enter') {
      e.preventDefault();
      handlePlay(e, targetRate, speedType);
    }
  };

  const sizeClasses = {
    sm: 'w-7 h-7 text-xs',
    md: 'w-9 h-9 text-sm',
    lg: 'w-11 h-11 text-base',
  }[size];

  const iconSizes = {
    sm: 'w-3.5 h-3.5',
    md: 'w-4 h-4',
    lg: 'w-5 h-5',
  }[size];

  return (
    <div className={`inline-flex items-center gap-1.5 ${className}`} dir="ltr">
      {/* Normal speed native audio button */}
      <button
        type="button"
        role="button"
        tabIndex={0}
        aria-label={label || `Play European Spanish pronunciation for: ${text}`}
        onClick={(e) => handlePlay(e, rate, 'normal')}
        onKeyDown={(e) => handleKeyDown(e, rate, 'normal')}
        title={playbackError || label || `استمع للنطق الإسباني الأوروبي (es-ES): ${text}`}
        className={`rounded-xl flex items-center justify-center transition-all cursor-pointer select-none focus:outline-none focus:ring-2 focus:ring-emerald-500 ${sizeClasses} ${
          playbackError
            ? 'bg-red-50 text-red-600 border border-red-300'
            : playingSpeed === 'normal'
            ? 'bg-emerald-600 text-white scale-105 shadow-md ring-2 ring-emerald-300'
            : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 hover:text-emerald-950 border border-emerald-200 shadow-2xs'
        }`}
      >
        {isLoading && playingSpeed === 'normal' ? (
          <Loader2 className={`${iconSizes} animate-spin text-emerald-700`} />
        ) : playbackError ? (
          <AlertCircle className={`${iconSizes} text-red-600`} />
        ) : (
          <Volume2 className={`${iconSizes} ${playingSpeed === 'normal' ? 'animate-pulse' : ''}`} />
        )}
        {label && <span className="mr-1.5 font-bold">{label}</span>}
      </button>

      {/* Slow educational speed pronunciation button (🐢) */}
      {showSlowButton && (
        <button
          type="button"
          role="button"
          tabIndex={0}
          aria-label={`Play slow Spanish pronunciation (0.7x) for: ${text}`}
          onClick={(e) => handlePlay(e, 0.72, 'slow')}
          onKeyDown={(e) => handleKeyDown(e, 0.72, 'slow')}
          title={`استمع بنطق بطيء ومتمهل (0.7x): ${text}`}
          className={`rounded-xl flex items-center justify-center transition-all cursor-pointer select-none focus:outline-none focus:ring-2 focus:ring-amber-500 ${sizeClasses} ${
            playingSpeed === 'slow'
              ? 'bg-amber-600 text-white scale-105 shadow-md ring-2 ring-amber-300'
              : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200 shadow-2xs'
          }`}
        >
          {isLoading && playingSpeed === 'slow' ? (
            <Loader2 className={`${iconSizes} animate-spin text-amber-700`} />
          ) : (
            <Snail className={`${iconSizes} ${playingSpeed === 'slow' ? 'animate-pulse' : ''}`} />
          )}
        </button>
      )}

      {/* Approximate Arabic Phonetic Hint if provided */}
      {approximateArabicHint && (
        <span
          className="text-[11px] font-semibold text-stone-500 bg-stone-100/90 px-2 py-0.5 rounded-lg border border-stone-200"
          title="تقريب نطق بالعربية (مساعد استئناسي تقريبي وليس بديلاً عن النطق الأصيل)"
          dir="rtl"
        >
          [{approximateArabicHint}]
        </span>
      )}
    </div>
  );
};
