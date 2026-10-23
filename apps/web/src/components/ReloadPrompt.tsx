import React from 'react';
import { useRegisterSW } from 'virtual:pwa-register/react';
import { RefreshCw, X, Sparkles } from 'lucide-react';

export const ReloadPrompt: React.FC = () => {
  const {
    needRefresh: [needRefresh, setNeedRefresh],
    updateServiceWorker,
  } = useRegisterSW({
    onRegistered(r) {
      console.log('SW Registered: ', r);
    },
    onRegisterError(error) {
      console.error('SW registration error', error);
    },
  });

  const close = () => {
    setNeedRefresh(false);
  };

  if (!needRefresh) return null;

  return (
    <div
      role="alert"
      dir="rtl"
      className="fixed bottom-20 md:bottom-6 left-4 right-4 md:left-auto md:right-6 md:max-w-md z-50 bg-[#1B4D3E] text-white p-4 rounded-2xl shadow-xl border border-emerald-600/40 flex items-center justify-between gap-3 animate-bounce-subtle"
    >
      <div className="flex items-center gap-3">
        <div className="w-9 h-9 rounded-xl bg-amber-400 text-stone-950 flex items-center justify-center shrink-0 shadow-xs">
          <Sparkles className="w-5 h-5" />
        </div>
        <div className="space-y-0.5 text-right">
          <p className="text-sm font-bold">تحديث جديد متوفر للتطبيق! 🚀</p>
          <p className="text-xs text-emerald-200">
            يتضمن تحسينات بالصوت والتمارين دون التأثير على تقدمك.
          </p>
        </div>
      </div>

      <div className="flex items-center gap-2 shrink-0">
        <button
          onClick={() => updateServiceWorker(true)}
          className="px-3.5 py-1.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-stone-950 font-bold text-xs shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer"
        >
          <RefreshCw className="w-3.5 h-3.5" />
          <span>تحديث الآن</span>
        </button>
        <button
          onClick={close}
          className="p-1 rounded-lg hover:bg-emerald-800 text-emerald-200 hover:text-white transition-colors cursor-pointer"
          aria-label="إغلاق التنبيه"
        >
          <X className="w-4 h-4" />
        </button>
      </div>
    </div>
  );
};
