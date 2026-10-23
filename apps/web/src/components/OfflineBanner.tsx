import React, { useEffect, useState } from 'react';
import { usePwa } from '../hooks/usePwa';
import { WifiOff, Wifi, RefreshCw } from 'lucide-react';

export const OfflineBanner: React.FC = () => {
  const { isOnline, queuedSyncCount } = usePwa();
  const [showReconnectedToast, setShowReconnectedToast] = useState(false);

  useEffect(() => {
    if (isOnline) {
      setShowReconnectedToast(true);
      const timer = setTimeout(() => {
        setShowReconnectedToast(false);
      }, 4000);
      return () => clearTimeout(timer);
    }
  }, [isOnline]);

  if (!isOnline) {
    return (
      <div
        role="status"
        aria-live="polite"
        dir="rtl"
        className="bg-amber-600 text-white text-xs font-bold py-2 px-4 shadow-sm flex items-center justify-between gap-2 sticky top-0 z-50 transition-all"
      >
        <div className="flex items-center gap-2">
          <WifiOff className="w-4 h-4 shrink-0 animate-pulse" />
          <span>أنت الآن في وضع عدم الاتصال (Offline) • يمكنك دراسة الدروس والمفردات المحفوظة.</span>
        </div>
        {queuedSyncCount > 0 && (
          <div className="flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-700/80 text-[11px] shrink-0">
            <RefreshCw className="w-3 h-3 animate-spin" />
            <span>{queuedSyncCount} في انتظار المزامنة</span>
          </div>
        )}
      </div>
    );
  }

  if (showReconnectedToast) {
    return (
      <div
        role="status"
        aria-live="polite"
        dir="rtl"
        className="bg-emerald-700 text-white text-xs font-bold py-2 px-4 shadow-sm flex items-center justify-center gap-2 sticky top-0 z-50 transition-all"
      >
        <Wifi className="w-4 h-4 shrink-0" />
        <span>تم استعادة الاتصال بالإنترنت بنجاح • جاري تحديث ومزامنة البيانات.</span>
      </div>
    );
  }

  return null;
};
