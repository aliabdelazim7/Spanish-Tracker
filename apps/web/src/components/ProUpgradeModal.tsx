import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { subscriptionApi } from '../api/subscription';
import confetti from 'canvas-confetti';
import {
  Sparkles,
  Check,
  X,
  CreditCard,
  Phone,
  Zap,
  Lock,
  ArrowRight,
  ShieldCheck,
  Crown
} from 'lucide-react';

interface ProUpgradeModalProps {
  isOpen: boolean;
  onClose: () => void;
  featureTitle?: string;
  onSubscribed?: () => void;
}

export const ProUpgradeModal: React.FC<ProUpgradeModalProps> = ({
  isOpen,
  onClose,
  featureTitle = 'الميزات المتقدمة ومحاكي المواقف الحية',
  onSubscribed,
}) => {
  const navigate = useNavigate();
  const [currency, setCurrency] = useState<'EGP' | 'USD'>('EGP');
  const [selectedPlan, setSelectedPlan] = useState<'pro_monthly' | 'pro_annual'>('pro_monthly');
  const [paymentMethod, setPaymentMethod] = useState<'vodafone_cash' | 'paymob_card'>('vodafone_cash');
  const [isLoading, setIsLoading] = useState(false);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);

  if (!isOpen) return null;

  const handleQuickCheckout = async () => {
    setIsLoading(true);
    setSuccessMessage(null);
    try {
      const res = await subscriptionApi.checkout({
        plan_slug: selectedPlan,
        payment_method: paymentMethod,
        currency: currency,
      });

      setSuccessMessage(res.message_ar);
      confetti({ particleCount: 80, spread: 90, origin: { y: 0.5 } });

      setTimeout(() => {
        if (onSubscribed) onSubscribed();
        onClose();
      }, 2200);
    } catch (err: any) {
      alert(err.message || 'تعذر استكمال الاشتراك، يرجى المحاولة لاحقاً');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs font-sans">
      <div className="bg-white rounded-3xl max-w-lg w-full border border-stone-200 shadow-2xl overflow-hidden text-right space-y-0">
        {/* Banner Top */}
        <div className="bg-gradient-to-br from-[#1B4D3E] via-emerald-900 to-stone-900 p-6 text-white relative">
          <button
            type="button"
            onClick={onClose}
            className="absolute top-4 left-4 p-1.5 rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors"
          >
            <X className="w-5 h-5" />
          </button>

          <div className="flex items-center gap-2 mb-2">
            <span className="px-2.5 py-0.5 rounded-full bg-amber-400 text-stone-950 font-black text-[11px] flex items-center gap-1">
              <Crown className="w-3.5 h-3.5 fill-stone-950" />
              <span>RUTA PRO</span>
            </span>
            <span className="text-xs text-emerald-200 font-medium">افتح إمكانياتك الكاملة</span>
          </div>

          <h2 className="text-2xl font-black text-white">
            ترقية حسابك إلى باقة Pro
          </h2>
          <p className="text-xs text-emerald-100/90 leading-relaxed pt-1">
            للوصول إلى {featureTitle} وكافة مستويات اللغة الإسبانية دون أي قيود.
          </p>
        </div>

        {/* Content Body */}
        <div className="p-6 space-y-5">
          {successMessage ? (
            <div className="p-6 rounded-2xl bg-emerald-50 border border-emerald-300 text-center space-y-3">
              <div className="w-12 h-12 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto shadow-md">
                <Check className="w-6 h-6 stroke-[3]" />
              </div>
              <h3 className="text-lg font-black text-emerald-950">¡Bienvenido a Pro!</h3>
              <p className="text-xs text-emerald-800 font-medium">{successMessage}</p>
            </div>
          ) : (
            <>
              {/* Currency & Plan Picker */}
              <div className="flex items-center justify-between border-b border-stone-100 pb-3">
                <span className="text-xs font-bold text-stone-600">اختر خطة التجديد:</span>
                <div className="flex items-center gap-1 bg-stone-100 p-1 rounded-xl">
                  <button
                    type="button"
                    onClick={() => setCurrency('EGP')}
                    className={`px-2.5 py-1 rounded-lg text-xs font-bold transition-all ${
                      currency === 'EGP' ? 'bg-white text-stone-900 shadow-2xs' : 'text-stone-500'
                    }`}
                  >
                    ج.م (EGP)
                  </button>
                  <button
                    type="button"
                    onClick={() => setCurrency('USD')}
                    className={`px-2.5 py-1 rounded-lg text-xs font-bold transition-all ${
                      currency === 'USD' ? 'bg-white text-stone-900 shadow-2xs' : 'text-stone-500'
                    }`}
                  >
                    $ (USD)
                  </button>
                </div>
              </div>

              {/* Plans Grid */}
              <div className="grid grid-cols-2 gap-3">
                <button
                  type="button"
                  onClick={() => setSelectedPlan('pro_monthly')}
                  className={`p-3.5 rounded-2xl border-2 text-right transition-all cursor-pointer ${
                    selectedPlan === 'pro_monthly'
                      ? 'border-[#1B4D3E] bg-emerald-50/60 shadow-2xs'
                      : 'border-stone-200 hover:border-stone-300'
                  }`}
                >
                  <div className="text-xs font-bold text-stone-500">شهري</div>
                  <div className="text-xl font-black text-stone-900 pt-1">
                    {currency === 'EGP' ? '149 ج.م' : '$9.99'}
                    <span className="text-[11px] font-normal text-stone-500">/شهر</span>
                  </div>
                  <div className="text-[10px] text-stone-400 pt-1">إلغاء في أي وقت</div>
                </button>

                <button
                  type="button"
                  onClick={() => setSelectedPlan('pro_annual')}
                  className={`p-3.5 rounded-2xl border-2 text-right transition-all cursor-pointer relative ${
                    selectedPlan === 'pro_annual'
                      ? 'border-[#1B4D3E] bg-emerald-50/60 shadow-2xs'
                      : 'border-stone-200 hover:border-stone-300'
                  }`}
                >
                  <span className="absolute -top-2.5 left-2 px-2 py-0.5 rounded-full bg-amber-400 text-stone-950 font-black text-[9px] shadow-2xs">
                    وفر شهرين 🎉
                  </span>
                  <div className="text-xs font-bold text-stone-500">سنوي</div>
                  <div className="text-xl font-black text-stone-900 pt-1">
                    {currency === 'EGP' ? '990 ج.م' : '$69.99'}
                    <span className="text-[11px] font-normal text-stone-500">/سنة</span>
                  </div>
                  <div className="text-[10px] text-emerald-700 font-bold pt-1">القيمة الأفضل للملتزمين</div>
                </button>
              </div>

              {/* Feature Highlights */}
              <div className="p-3.5 rounded-2xl bg-stone-50 border border-stone-200/80 space-y-2">
                <div className="text-xs font-bold text-stone-800">ما ستحصل عليه فوراً:</div>
                <div className="grid grid-cols-1 gap-1.5 text-xs text-stone-600">
                  <div className="flex items-center gap-2">
                    <Check className="w-3.5 h-3.5 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>فتح كافة مستويات CEFR من A1 حتى C2 كاملاً</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Check className="w-3.5 h-3.5 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>محاكي المواقف الحية غير المحدود مع تقييم النطق الصوتي</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Check className="w-3.5 h-3.5 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>تصحيح اليوميات الإسبانية وتتبع الأخطاء بالذكاء الاصطناعي</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Check className="w-3.5 h-3.5 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>مسار إسبانية كرة القدم والتأهيل لمقابلات عمل الكول سنتر</span>
                  </div>
                </div>
              </div>

              {/* Payment Method Selector */}
              <div className="space-y-1.5">
                <span className="text-[11px] font-bold text-stone-500">طريقة الدفع والتفعيل:</span>
                <div className="grid grid-cols-2 gap-2">
                  <button
                    type="button"
                    onClick={() => setPaymentMethod('vodafone_cash')}
                    className={`p-2.5 rounded-xl border text-xs font-bold flex items-center justify-center gap-1.5 transition-all ${
                      paymentMethod === 'vodafone_cash'
                        ? 'border-emerald-600 bg-emerald-50 text-emerald-900'
                        : 'border-stone-200 text-stone-700'
                    }`}
                  >
                    <Phone className="w-3.5 h-3.5 text-red-600" />
                    <span>فودافون كاش / إنستاباي</span>
                  </button>

                  <button
                    type="button"
                    onClick={() => setPaymentMethod('paymob_card')}
                    className={`p-2.5 rounded-xl border text-xs font-bold flex items-center justify-center gap-1.5 transition-all ${
                      paymentMethod === 'paymob_card'
                        ? 'border-emerald-600 bg-emerald-50 text-emerald-900'
                        : 'border-stone-200 text-stone-700'
                    }`}
                  >
                    <CreditCard className="w-3.5 h-3.5 text-blue-600" />
                    <span>بطاقة بنكية / ميزة</span>
                  </button>
                </div>
              </div>

              {/* Action Buttons */}
              <div className="space-y-2 pt-2">
                <button
                  type="button"
                  onClick={handleQuickCheckout}
                  disabled={isLoading}
                  className="w-full py-3.5 rounded-2xl bg-amber-400 hover:bg-amber-300 text-stone-950 font-black text-sm shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                >
                  <Zap className="w-4 h-4 fill-stone-950" />
                  <span>{isLoading ? 'جاري التفعيل الفوري...' : 'تأكيد الترقية والبدء الآن'}</span>
                </button>

                <div className="flex items-center justify-center gap-1.5 text-[11px] text-stone-400 pt-1">
                  <ShieldCheck className="w-3.5 h-3.5 text-emerald-600" />
                  <span>دفع آمن 100% • إمكانية الإلغاء بضغطة زر دون أي تعقيد</span>
                </div>
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
};
