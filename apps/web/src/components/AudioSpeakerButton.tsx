import React, { useState } from 'react';
import { Volume2, Snail } from 'lucide-react';
import { playSpanishAudio, stopSpanishAudio } from '../utils/speech';

interface AudioSpeakerButtonProps {
  text: string;
  className?: string;
  size?: 'sm' | 'md' | 'lg';
  rate?: number;
  label?: string;
  showSlowButton?: boolean;
}

export const AudioSpeakerButton: React.FC<AudioSpeakerButtonProps> = ({
  text,
  className = '',
  size = 'md',
  rate = 1.0,
  label,
  showSlowButton = false,
}) => {
  const [playingSpeed, setPlayingSpeed] = useState<'normal' | 'slow' | null>(null);

  const handlePlay = async (e: React.MouseEvent, targetRate: number, speedType: 'normal' | 'slow') => {
    e.stopPropagation();

    if (playingSpeed === speedType) {
      stopSpanishAudio();
      setPlayingSpeed(null);
      return;
    }

    setPlayingSpeed(speedType);
    await playSpanishAudio(text, targetRate);
    setPlayingSpeed(null);
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
      {/* Normal speed native audio */}
      <button
        type="button"
        onClick={(e) => handlePlay(e, rate, 'normal')}
        title={label || `استمع للنطق الطبيعي: ${text}`}
        className={`rounded-xl flex items-center justify-center transition-all ${sizeClasses} ${
          playingSpeed === 'normal'
            ? 'bg-emerald-600 text-white scale-105 shadow-md ring-2 ring-emerald-300'
            : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 hover:text-emerald-950 border border-emerald-200 shadow-2xs'
        }`}
      >
        <Volume2 className={`${iconSizes} ${playingSpeed === 'normal' ? 'animate-pulse' : ''}`} />
        {label && <span className="mr-1.5 font-bold">{label}</span>}
      </button>

      {/* Slow educational speed pronunciation button */}
      {showSlowButton && (
        <button
          type="button"
          onClick={(e) => handlePlay(e, 0.72, 'slow')}
          title={`استمع بنطق بطيء وتعليمي متمهل (0.7x): ${text}`}
          className={`rounded-xl flex items-center justify-center transition-all ${sizeClasses} ${
            playingSpeed === 'slow'
              ? 'bg-amber-600 text-white scale-105 shadow-md ring-2 ring-amber-300'
              : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200/90 shadow-2xs'
          }`}
        >
          <Snail className={`${iconSizes} ${playingSpeed === 'slow' ? 'animate-pulse' : ''}`} />
        </button>
      )}
    </div>
  );
};
