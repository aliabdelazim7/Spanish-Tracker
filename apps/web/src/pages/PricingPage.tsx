import React, { useState, useEffect } from 'react';
import { subscriptionApi, Plan, UserSubscription } from '../api/subscription';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import confetti from 'canvas-confetti';
import {
  Check,
  Crown,
  Sparkles,
  Zap,
  ShieldCheck,
  HelpCircle,
  Phone,
  CreditCard,
  ChevronDown,
  ChevronUp,
  Award,
  AlertCircle
} from 'lucide-react';

export const PricingPage: React.FC = () => {
  const [plans, setPlans] = useState<Plan[]>([]);
  const [mySubscription, setMySubscription] = useState<UserSubscription | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [currency, setCurrency] = useState<'EGP' | 'USD'>('EGP');
  const [billingCycle, setBillingCycle] = useState<'monthly' | 'annual'>('monthly');
  const [isProcessingCheckout, setIsProcessingCheckout] = useState(false);
  const [checkoutSuccessMsg, setCheckoutSuccessMsg] = useState<string | null>(null);

  // FAQ accordion state
  const [openFaqIndex, setOpenFaqIndex] = useState<number | null>(null);

  useEffect(() => {
    loadPricingData();
  }, []);

  const loadPricingData = async () => {
    setIsLoading(true);
    try {
      const [plansRes, subRes] = await Promise.allSettled([
        subscriptionApi.getPlans(),
        subscriptionApi.getMySubscription(),
      ]);

      if (plansRes.status === 'fulfilled') {
        setPlans(plansRes.value.plans || []);
      }
      if (subRes.status === 'fulfilled') {
        setMySubscription(subRes.value);
      }
    } catch (err) {
      console.error('Failed to load pricing data:', err);
    } finally {
      setIsLoading(false);
    }
  };

  const handleCheckout = async (planSlug: string) => {
    setIsProcessingCheckout(true);
    setCheckoutSuccessMsg(null);
    try {
      const res = await subscriptionApi.checkout({
        plan_slug: planSlug,
        payment_method: currency === 'EGP' ? 'vodafone_cash' : 'stripe_card',
        currency: currency,
      });

      setCheckoutSuccessMsg(res.message_ar);
      setMySubscription(res.subscription);
      confetti({ particleCount: 90, spread: 80, origin: { y: 0.4 } });
    } catch (err: any) {
      alert(err.message || 'تعذر استكمال العملية');
    } finally {
      setIsProcessingCheckout(false);
    }
  };

  const handleCancelSubscription = async () => {
    if (!window.confirm('هل أنت متأكد من رغبتك في إلغاء التجديد التلقائي؟ ستظل ميزاتك نشطة حتى نهاية الفترة.')) {
      return;
    }

    try {
      const res = await subscriptionApi.cancel();
      alert(res.message_ar);
      setMySubscription(res.subscription);
    } catch (err: any) {
      alert(err.message || 'تعذر الإلغاء');
    }
  };

  const faqs = [
    {
      q: 'هل يمكنني الدفع عبر المحافظ الإلكترونية مثل فودافون كاش وإنستاباي؟',
      a: 'نعم بالتأكيد! نحن ندعم الدفع المباشر عبر محافظ المحمول (فودافون كاش، أورنج كاش، إتصالات كاش، وي باي) بالإضافة لبطاقات ميزة والبطاقات البنكية Visa و MasterCard.',
    },
    {
      q: 'هل يمكنني إلغاء الاشتراك في أي وقت؟',
      a: 'بكل تأكيد ودون أي قيود. يمكنك إلغاء التجديد التلقائي بضغطة زر واحدة من هذه الصفحة أو من ملفك الشخصي، وستستمر في الاستفادة من ميزات باقتك حتى آخر يوم في فترتك المدفوعة.',
    },
    {
      q: 'ما هو مسار التأهيل لوظائف الكول سنتر الإسباني (BPO)؟',
      a: 'مسار مخصص يركز على المصطلحات وخدمة العملاء ومحاكاة المقابلات الهاتفية التي تطلبها شركات التعهيد العالمية (مثل Teleperformance و Concentrix) برواتب تبدأ من 20 ألف جنيه مصري.',
    },
    {
      q: 'هل الشهادات الممنوحة متوافقة مع الإطار الأوروبي المشترك (CEFR)؟',
      a: 'نعم، بعد إنهاء وحدات المستوى واجتياز الامتحان الشامل بدرجة 70% فأعلى، تصدر لك المنصة شهادة إتمام إلكترونية موثقة برقم مرجعي وكود QR لمشاركتها على LinkedIn أو سيرتك الذاتية.',
    },
  ];

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col font-sans">
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        <Sidebar />

        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-8 max-w-5xl w-full pb-24 md:pb-8">
          {/* Header */}
          <div className="text-center space-y-3 max-w-2xl mx-auto">
            <span className="px-3.5 py-1 rounded-full bg-emerald-100 text-emerald-900 font-bold text-xs border border-emerald-300">
              خطط واستثمارات واضحة وشفافة 💎
            </span>
            <h1 className="text-3xl sm:text-4xl font-black text-stone-900">
              اختر الخطة المناسبة لرحلتك في الإسبانية
            </h1>
            <p className="text-xs sm:text-sm text-stone-600 leading-relaxed font-normal">
              ابدأ مجاناً واكتشف مستواك، أو قم بالترقية لفتح كافة المستويات ومحاكي المواقف الحية ومسارات التأهيل الوظيفي.
            </p>

            {/* Currency & Billing Toggles */}
            <div className="flex flex-wrap items-center justify-center gap-4 pt-3">
              {/* Billing Cycle Toggle */}
              <div className="flex items-center gap-1 bg-stone-200/80 p-1 rounded-2xl">
                <button
                  type="button"
                  onClick={() => setBillingCycle('monthly')}
                  className={`px-4 py-2 rounded-xl text-xs font-bold transition-all ${
                    billingCycle === 'monthly'
                      ? 'bg-white text-stone-900 shadow-2xs'
                      : 'text-stone-600 hover:text-stone-900'
                  }`}
                >
                  اشتراك شهري
                </button>
                <button
                  type="button"
                  onClick={() => setBillingCycle('annual')}
                  className={`px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 ${
                    billingCycle === 'annual'
                      ? 'bg-[#1B4D3E] text-white shadow-2xs'
                      : 'text-stone-600 hover:text-stone-900'
                  }`}
                >
                  <span>اشتراك سنوي</span>
                  <span className="bg-amber-400 text-stone-950 px-1.5 py-0.5 rounded-full text-[10px] font-black">
                    وفر شهرين 🎉
                  </span>
                </button>
              </div>

              {/* Currency Toggle */}
              <div className="flex items-center gap-1 bg-stone-100 p-1 rounded-2xl border border-stone-200">
                <button
                  type="button"
                  onClick={() => setCurrency('EGP')}
                  className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
                    currency === 'EGP' ? 'bg-white text-[#1B4D3E] shadow-2xs' : 'text-stone-500'
                  }`}
                >
                  🇪🇬 ج.م (EGP)
                </button>
                <button
                  type="button"
                  onClick={() => setCurrency('USD')}
                  className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
                    currency === 'USD' ? 'bg-white text-[#1B4D3E] shadow-2xs' : 'text-stone-500'
                  }`}
                >
                  🌐 $ (USD)
                </button>
              </div>
            </div>
          </div>

          {/* Current Subscription Status Alert (if active) */}
          {mySubscription && mySubscription.is_pro && (
            <div className="p-5 rounded-3xl bg-emerald-50 border-2 border-emerald-300 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div className="flex items-center gap-3">
                <div className="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                  <Crown className="w-6 h-6" />
                </div>
                <div className="text-right">
                  <div className="text-sm font-black text-emerald-950 flex items-center gap-2">
                    <span>أنت مشترك في {mySubscription.plan?.name_ar || 'باقة Pro'}</span>
                    <span className="text-[10px] px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-900 font-bold">
                      نشط ✓
                    </span>
                  </div>
                  <div className="text-xs text-emerald-800 pt-0.5 font-medium">
                    متبقي في اشتراكك: {mySubscription.days_remaining} يوماً (ينتهي في {mySubscription.current_period_ends_at?.split('T')[0]})
                  </div>
                </div>
              </div>

              {mySubscription.can_cancel && (
                <button
                  type="button"
                  onClick={handleCancelSubscription}
                  className="px-4 py-2 rounded-xl border border-emerald-300 text-emerald-800 hover:bg-emerald-100 text-xs font-bold transition-colors"
                >
                  إلغاء التجديد التلقائي
                </button>
              )}
            </div>
          )}

          {checkoutSuccessMsg && (
            <div className="p-4 rounded-2xl bg-emerald-100 border border-emerald-300 text-emerald-900 text-xs font-bold text-center">
              {checkoutSuccessMsg}
            </div>
          )}

          {/* Pricing Cards Grid */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
            {/* 1. Free Starter */}
            <div className="bg-white rounded-3xl p-6 sm:p-7 border border-stone-200/90 shadow-2xs flex flex-col justify-between space-y-6 text-right">
              <div className="space-y-4">
                <div className="space-y-1">
                  <span className="text-xs font-bold px-2.5 py-0.5 rounded-full bg-stone-100 text-stone-600">
                    استكشافي
                  </span>
                  <h3 className="text-xl font-black text-stone-900 pt-2">الباقة المجانية</h3>
                  <p className="text-xs text-stone-500">
                    لتجربة المنصة وفحص مستواك واكتشاف أسلوب التعلم الذكي
                  </p>
                </div>

                <div className="pt-2">
                  <div className="text-3xl font-black text-stone-900">
                    {currency === 'EGP' ? '0 ج.م' : '$0'}
                  </div>
                  <div className="text-xs text-stone-400">مجاناً مدى الحياة</div>
                </div>

                <div className="space-y-2.5 pt-4 border-t border-stone-100 text-xs text-stone-700">
                  <div className="flex items-center gap-2">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>اختبار تحديد المستوى وتشخيص نقاط الضعف</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>أول 3 وحدات في المستوى التأسيسي A1</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>تحدي الـ 3 دقائق اليومي وبطاقة الإنجاز</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>موقف محادثة صوتي واحد يومياً</span>
                  </div>
                </div>
              </div>

              <button
                type="button"
                disabled
                className="w-full py-3 rounded-2xl bg-stone-100 text-stone-500 font-bold text-xs"
              >
                {mySubscription?.is_pro ? 'الباقة الأساسية' : 'باقتك الحالية ✓'}
              </button>
            </div>

            {/* 2. Pro Monthly (Featured) */}
            <div className="bg-white rounded-3xl p-6 sm:p-7 border-2 border-[#1B4D3E] shadow-md flex flex-col justify-between space-y-6 text-right relative">
              <span className="absolute -top-3.5 right-6 px-3 py-1 rounded-full bg-[#1B4D3E] text-white font-black text-[11px] shadow-xs flex items-center gap-1">
                <Sparkles className="w-3.5 h-3.5 text-amber-400" />
                <span>الخيار الأكثر شعبية</span>
              </span>

              <div className="space-y-4">
                <div className="space-y-1">
                  <span className="text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200">
                    احترافي شهري
                  </span>
                  <h3 className="text-xl font-black text-stone-900 pt-2">الباقة الاحترافية (Pro)</h3>
                  <p className="text-xs text-stone-500">
                    لتحقيق الطلاقة الكاملة وفتح كافة مستويات وإمكانيات المنصة
                  </p>
                </div>

                <div className="pt-2">
                  <div className="text-3xl font-black text-[#1B4D3E]">
                    {currency === 'EGP' ? '149 ج.م' : '$9.99'}
                    <span className="text-xs font-normal text-stone-500"> / شهرياً</span>
                  </div>
                  <div className="text-xs text-stone-400">إلغاء فوري في أي وقت دون التزامات</div>
                </div>

                <div className="space-y-2.5 pt-4 border-t border-stone-100 text-xs text-stone-800">
                  <div className="flex items-center gap-2 font-medium">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>فتح كافة مستويات CEFR من A1 حتى C2 كاملاً</span>
                  </div>
                  <div className="flex items-center gap-2 font-medium">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>محاكي المواقف الحية غير المحدود مع تقييم النطق</span>
                  </div>
                  <div className="flex items-center gap-2 font-medium">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>دفتر اليوميات وتصحيح الكتابة بالذكاء الاصطناعي</span>
                  </div>
                  <div className="flex items-center gap-2 font-medium">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>مسار إسبانية كرة القدم (الكلاسيكو) والتراث الأندلسي</span>
                  </div>
                  <div className="flex items-center gap-2 font-medium">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>امتحانات الترقية والشهادات الرقمية المعتمدة</span>
                  </div>
                  <div className="flex items-center gap-2 font-medium">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>دفع سهل بفودافون كاش وإنستاباي وميزة والبطاقات</span>
                  </div>
                </div>
              </div>

              <button
                type="button"
                onClick={() => handleCheckout('pro_monthly')}
                disabled={isProcessingCheckout || (mySubscription?.is_pro && mySubscription.plan?.slug === 'pro_monthly')}
                className="w-full py-3.5 rounded-2xl bg-[#1B4D3E] hover:bg-[#153e32] text-white font-black text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <Zap className="w-4 h-4 fill-white" />
                <span>
                  {mySubscription?.is_pro && mySubscription.plan?.slug === 'pro_monthly'
                    ? 'اشتراكك الحالي نشط'
                    : 'اشترك الآن في Pro الشهري'}
                </span>
              </button>
            </div>

            {/* 3. Pro Annual (VIP) */}
            <div className="bg-gradient-to-b from-amber-50/70 to-white rounded-3xl p-6 sm:p-7 border-2 border-amber-300 shadow-sm flex flex-col justify-between space-y-6 text-right relative">
              <span className="absolute -top-3.5 left-6 px-3 py-1 rounded-full bg-amber-400 text-stone-950 font-black text-[11px] shadow-xs flex items-center gap-1">
                <Crown className="w-3.5 h-3.5 fill-stone-950" />
                <span>أفضل قيمة سنوية</span>
              </span>

              <div className="space-y-4">
                <div className="space-y-1">
                  <span className="text-xs font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300">
                    ملكي سنوي (VIP)
                  </span>
                  <h3 className="text-xl font-black text-stone-900 pt-2">الباقة السنوية الكاملة</h3>
                  <p className="text-xs text-stone-500">
                    12 شهراً بسعر 10 أشهر مع مسارات الإعداد الوظيفي وامتحانات DELE
                  </p>
                </div>

                <div className="pt-2">
                  <div className="text-3xl font-black text-amber-900">
                    {currency === 'EGP' ? '990 ج.م' : '$69.99'}
                    <span className="text-xs font-normal text-stone-500"> / سنوياً</span>
                  </div>
                  <div className="text-xs text-emerald-700 font-bold">توفير شهرين كاملين مجاناً 🎉</div>
                </div>

                <div className="space-y-2.5 pt-4 border-t border-amber-200/60 text-xs text-stone-800">
                  <div className="flex items-center gap-2 font-medium">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>كل ميزات باقة Pro لمدة عام كامل</span>
                  </div>
                  <div className="flex items-center gap-2 font-medium">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>مسار التأهيل لوظائف الكول سنتر الإسباني (BPO Hiring)</span>
                  </div>
                  <div className="flex items-center gap-2 font-medium">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>مسار التحضير لامتحانات DELE و SIELE الرسمية</span>
                  </div>
                  <div className="flex items-center gap-2 font-medium">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>شارة العضو الملكي VIP في الملف وبطاقات الإنجاز</span>
                  </div>
                  <div className="flex items-center gap-2 font-medium">
                    <Check className="w-4 h-4 text-emerald-600 shrink-0 stroke-[3]" />
                    <span>أولوية الدعم والاستشارات اللغوية المباشرة</span>
                  </div>
                </div>
              </div>

              <button
                type="button"
                onClick={() => handleCheckout('pro_annual')}
                disabled={isProcessingCheckout || (mySubscription?.is_pro && mySubscription.plan?.slug === 'pro_annual')}
                className="w-full py-3.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
              >
                <Crown className="w-4 h-4 fill-stone-950" />
                <span>
                  {mySubscription?.is_pro && mySubscription.plan?.slug === 'pro_annual'
                    ? 'اشتراكك السنوي نشط'
                    : 'اشترك سنوياً ووفر شهرين'}
                </span>
              </button>
            </div>
          </div>

          {/* Payment Trust & Gateways Banner */}
          <div className="bg-white rounded-3xl p-6 border border-stone-200/80 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-right">
            <div className="space-y-1">
              <div className="flex items-center justify-center sm:justify-start gap-2 text-stone-900 font-bold text-sm">
                <ShieldCheck className="w-5 h-5 text-emerald-600" />
                <span>طرق دفع محلية وعالمية آمنة 100%</span>
              </div>
              <p className="text-xs text-stone-500 font-normal">
                ندعم فودافون كاش، محافظ المحمول الذكية، بطاقات ميزة الوطنية، وبطاقات Visa و MasterCard مع تشفير مصرفي كامل.
              </p>
            </div>

            <div className="flex items-center gap-2.5">
              <span className="px-3 py-1.5 rounded-xl bg-stone-100 text-stone-700 font-bold text-xs">📱 Vodafone Cash</span>
              <span className="px-3 py-1.5 rounded-xl bg-stone-100 text-stone-700 font-bold text-xs">⚡ Instapay</span>
              <span className="px-3 py-1.5 rounded-xl bg-stone-100 text-stone-700 font-bold text-xs">💳 Meeza / Visa</span>
            </div>
          </div>

          {/* Frequently Asked Questions */}
          <div className="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/80 shadow-2xs space-y-4 text-right">
            <div className="space-y-1">
              <h2 className="text-xl font-black text-stone-900 flex items-center gap-2">
                <HelpCircle className="w-5 h-5 text-amber-500" />
                <span>الأسئلة الشائعة حول الاشتراكات</span>
              </h2>
              <p className="text-xs text-stone-500">كل ما تحتاج لمعرفته قبل بدء اشتراكك</p>
            </div>

            <div className="divide-y divide-stone-100 pt-2">
              {faqs.map((faq, idx) => (
                <div key={idx} className="py-3.5">
                  <button
                    type="button"
                    onClick={() => setOpenFaqIndex(openFaqIndex === idx ? null : idx)}
                    className="w-full flex items-center justify-between text-right font-bold text-xs sm:text-sm text-stone-800 hover:text-emerald-800 transition-colors"
                  >
                    <span>{faq.q}</span>
                    {openFaqIndex === idx ? (
                      <ChevronUp className="w-4 h-4 text-stone-400" />
                    ) : (
                      <ChevronDown className="w-4 h-4 text-stone-400" />
                    )}
                  </button>
                  {openFaqIndex === idx && (
                    <p className="text-xs text-stone-600 leading-relaxed pt-2 font-normal">
                      {faq.a}
                    </p>
                  )}
                </div>
              ))}
            </div>
          </div>
        </main>
      </div>
    </div>
  );
};
export default PricingPage;
