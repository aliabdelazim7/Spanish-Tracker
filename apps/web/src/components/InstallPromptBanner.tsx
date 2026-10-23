import React, { useState } from 'react';
import { usePwa } from '../hooks/usePwa';
import { Download, X, Share2, PlusSquare, Compass } from 'lucide-react';

export const InstallPromptBanner: React.FC = () => {
  const { canInstall, isIos, promptInstall, dismissInstall } = usePwa();
  const [showIosGuide, setShowIosGuide] = useState(false);

  if (!canInstall && !isIos) {
    return null;
  }

  return (
    <div
      role="region"
      aria-label="تثبيت التطبيق على الهاتف"
      dir="rtl"
      className="fixed bottom-20 md:bottom-6 left-4 right-4 md:left-6 md:right-auto md:max-w-md z-40 bg-white/95 backdrop-blur-md border border-stone-200/90 rounded-3xl p-4 shadow-xl transition-all"
    >
      <div className="flex items-start gap-3">
        {/* App Icon */}
        <div className="w-12 h-12 rounded-2xl bg-[#1B4D3E] text-amber-300 flex items-center justify-center shrink-0 shadow-md">
          <Compass className="w-7 h-7" />
        </div>

        <div className="flex-1 space-y-1 text-right">
          <div className="flex items-center justify-between">
            <h3 className="text-sm font-black text-stone-900">
              تثبيت تطبيق Ruta Español 📱
            </h3>
            <button
              onClick={dismissInstall}
              className="p-1 rounded-lg text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition-colors cursor-pointer"
              aria-label="إغلاق التنبيه"
            >
              <X className="w-4 h-4" />
            </button>
          </div>
          <p className="text-xs text-stone-600 leading-relaxed">
            ثبّت التطبيق على شاشتك الرئيسية لتجربة أسرع بدون إنترنت ودون استهلاك للبيانات.
          </p>

          {/* Action buttons */}
          <div className="pt-2 flex items-center gap-2">
            {canInstall && (
              <button
                onClick={promptInstall}
                className="px-4 py-2 rounded-xl bg-[#1B4D3E] hover:bg-[#153e32] text-white font-bold text-xs shadow-xs transition-all flex items-center gap-1.5 cursor-pointer"
              >
                <Download className="w-3.5 h-3.5 text-amber-300" />
                <span>تثبيت الآن</span>
              </button>
            )}

            {isIos && (
              <button
                onClick={() => setShowIosGuide(!showIosGuide)}
                className="px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-[#1B4D3E] border border-emerald-200 font-bold text-xs transition-colors flex items-center gap-1.5 cursor-pointer"
              >
                <Share2 className="w-3.5 h-3.5" />
                <span>طريقة التثبيت على iPhone</span>
              </button>
            )}

            <button
              onClick={dismissInstall}
              className="px-3 py-2 text-xs font-semibold text-stone-500 hover:text-stone-800 transition-colors"
            >
              لاحقاً
            </button>
          </div>

          {/* iOS Safari step-by-step instructions */}
          {isIos && showIosGuide && (
            <div className="mt-3 p-3 rounded-2xl bg-stone-50 border border-stone-200 text-xs text-stone-700 space-y-1.5 text-right">
              <div className="font-bold text-[#1B4D3E] flex items-center gap-1">
                <span>خطوات التثبيت في متصفح Safari:</span>
              </div>
              <ol className="list-decimal list-inside space-y-1 text-stone-600">
                <li>
                  اضغط على زر المشاركة{' '}
                  <span className="font-bold text-blue-600 inline-flex items-center gap-0.5">
                    (Share <Share2 className="w-3 h-3 inline" />)
                  </span>{' '}
                  في شريط أدوات Safari السفلي.
                </li>
                <li>
                  مرر للأسفل واختر{' '}
                  <span className="font-bold text-stone-900 inline-flex items-center gap-0.5">
                    "إضافة إلى الشاشة الرئيسية" (Add to Home Screen <PlusSquare className="w-3 h-3 inline" />)
                  </span>.
                </li>
                <li>اضغط على "إضافة" (Add) في الزاوية العلوية.</li>
              </ol>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};
