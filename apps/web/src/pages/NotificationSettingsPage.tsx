import React, { useEffect, useState } from 'react';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import { notificationApi, NotificationPreferences } from '../api/notifications';
import {
  Bell,
  BellRing,
  BellOff,
  Clock,
  Globe,
  Sparkles,
  Flame,
  RotateCcw,
  BookOpen,
  Award,
  CheckCircle2,
  AlertCircle,
  Smartphone,
  Send
} from 'lucide-react';

function urlBase64ToUint8Array(base64String: string): Uint8Array {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

export const NotificationSettingsPage: React.FC = () => {
  const [preferences, setPreferences] = useState<NotificationPreferences | null>(null);
  const [activeDevices, setActiveDevices] = useState(0);
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [isSubscribing, setIsSubscribing] = useState(false);
  const [isSendingTest, setIsSendingTest] = useState(false);
  const [statusMessage, setStatusMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);
  const [permissionState, setPermissionState] = useState<NotificationPermission>('default');
  const [isSupported, setIsSupported] = useState(true);

  useEffect(() => {
    // Feature detect Push & Notification support
    if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
      setIsSupported(false);
    } else {
      setPermissionState(Notification.permission);
    }

    loadPreferences();
  }, []);

  const loadPreferences = async () => {
    setIsLoading(true);
    try {
      const data = await notificationApi.getPreferences();
      setPreferences(data.preferences);
      setActiveDevices(data.active_devices_count);
    } catch (err) {
      console.error('Failed to load preferences:', err);
    } finally {
      setIsLoading(false);
    }
  };

  const handleToggle = (key: keyof NotificationPreferences) => {
    if (!preferences) return;
    setPreferences({
      ...preferences,
      [key]: !preferences[key],
    });
  };

  const handleTimeChange = (time: string) => {
    if (!preferences) return;
    setPreferences({
      ...preferences,
      preferred_reminder_time: time,
    });
  };

  const handleSavePreferences = async () => {
    if (!preferences) return;
    setIsSaving(true);
    setStatusMessage(null);
    try {
      await notificationApi.updatePreferences(preferences);
      setStatusMessage({ type: 'success', text: 'تم حفظ تفضيلات الإشعارات بنجاح!' });
    } catch (err) {
      setStatusMessage({ type: 'error', text: 'فشل في حفظ التفضيلات. يُرجى المحاولة لاحقاً.' });
    } finally {
      setIsSaving(false);
    }
  };

  const handleEnablePushNotifications = async () => {
    if (!isSupported) {
      alert('متصفحك لا يدعم إشعارات الويب Push Notifications.');
      return;
    }

    setIsSubscribing(true);
    setStatusMessage(null);

    try {
      // 1. Request browser permission
      const permission = await Notification.requestPermission();
      setPermissionState(permission);

      if (permission !== 'granted') {
        setStatusMessage({
          type: 'error',
          text: 'تم رفض إذن الإشعارات من المتصفح. يُرجى السماح بالإشعارات من إعدادات المتصفح.',
        });
        setIsSubscribing(false);
        return;
      }

      // 2. Fetch server VAPID key
      const { public_key, is_configured } = await notificationApi.getVapidKey();
      if (!is_configured || !public_key) {
        setStatusMessage({
          type: 'error',
          text: 'مفتاح VAPID غير مهيأ على الخادم حالياً.',
        });
        setIsSubscribing(false);
        return;
      }

      // 3. Register push subscription with service worker
      const registration = await navigator.serviceWorker.ready;
      let subscription = await registration.pushManager.getSubscription();

      if (!subscription) {
        subscription = await registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: urlBase64ToUint8Array(public_key) as unknown as BufferSource,
        });
      }

      // 4. Send subscription keys to backend
      const rawKey = subscription.getKey ? subscription.getKey('p256dh') : null;
      const rawAuth = subscription.getKey ? subscription.getKey('auth') : null;

      const p256dh = rawKey ? btoa(String.fromCharCode.apply(null, Array.from(new Uint8Array(rawKey)))) : '';
      const auth = rawAuth ? btoa(String.fromCharCode.apply(null, Array.from(new Uint8Array(rawAuth)))) : '';

      await notificationApi.subscribe({
        endpoint: subscription.endpoint,
        keys: { p256dh, auth },
        device_name: `${navigator.platform || 'Device'} (${navigator.userAgent.slice(0, 40)})`,
      });

      setStatusMessage({
        type: 'success',
        text: 'تم تفعيل إشعارات الهاتف بنجاح وربط جهازك بالمنصة!',
      });
      loadPreferences();
    } catch (err: any) {
      console.error('Subscription error:', err);
      setStatusMessage({
        type: 'error',
        text: `تعذر تفعيل الإشعارات: ${err.message || 'خطأ غير معروف'}`,
      });
    } finally {
      setIsSubscribing(false);
    }
  };

  const handleSendTestNotification = async () => {
    setIsSendingTest(true);
    setStatusMessage(null);
    try {
      await notificationApi.sendTest();
      setStatusMessage({
        type: 'success',
        text: 'تم إرسال إشعار تجريبي فوري! تحقق من مركز إشعارات جهازك.',
      });
    } catch (err) {
      setStatusMessage({
        type: 'error',
        text: 'تعذر إرسال الإشعار التجريبي. تأكد من تفعيل الإشعارات لهذا الجهاز أولاً.',
      });
    } finally {
      setIsSendingTest(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col" dir="rtl">
      <Navbar />

      <div className="flex-1 flex max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 gap-6">
        <Sidebar />

        <main className="flex-1 max-w-3xl space-y-6 pb-24 md:pb-12">
          {/* Header */}
          <div className="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/90 shadow-sm space-y-4">
            <div className="flex items-center gap-3">
              <div className="w-12 h-12 rounded-2xl bg-[#1B4D3E] text-amber-300 flex items-center justify-center shrink-0 shadow-md">
                <BellRing className="w-6 h-6" />
              </div>
              <div className="space-y-0.5">
                <h1 className="text-2xl font-black text-stone-900">إعدادات الإشعارات والتذكير</h1>
                <p className="text-xs sm:text-sm text-stone-500 font-medium">
                  اضبط مواعيد تذكيرك اليومي بالمفردات والدروس وفق جدولك ومنطقتك الزمنية.
                </p>
              </div>
            </div>

            {/* Support disclaimer banner */}
            {!isSupported && (
              <div className="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center gap-2">
                <AlertCircle className="w-5 h-5 shrink-0 text-amber-700" />
                <span>
                  متصفحك الحالي لا يدعم تقنية Web Push Notifications. لتلقي الإشعارات، يُرجى استخدام متصفح Chrome أو Edge أو تثبيت التطبيق على هاتفك.
                </span>
              </div>
            )}

            {/* Status alerts */}
            {statusMessage && (
              <div
                className={`p-4 rounded-2xl text-xs font-bold flex items-center gap-2 ${
                  statusMessage.type === 'success'
                    ? 'bg-emerald-50 text-emerald-900 border border-emerald-200'
                    : 'bg-red-50 text-red-900 border border-red-200'
                }`}
              >
                {statusMessage.type === 'success' ? (
                  <CheckCircle2 className="w-5 h-5 text-emerald-600 shrink-0" />
                ) : (
                  <AlertCircle className="w-5 h-5 text-red-600 shrink-0" />
                )}
                <span>{statusMessage.text}</span>
              </div>
            )}

            {/* Device activation status card */}
            <div className="p-4 rounded-2xl bg-stone-50 border border-stone-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <div className="flex items-center gap-3">
                <Smartphone className="w-5 h-5 text-[#1B4D3E]" />
                <div className="space-y-0.5">
                  <div className="text-xs font-bold text-stone-800">
                    حالة الجهاز الحالي:{' '}
                    <span
                      className={`px-2 py-0.5 rounded-full text-[11px] font-black ${
                        permissionState === 'granted'
                          ? 'bg-emerald-100 text-emerald-800'
                          : permissionState === 'denied'
                          ? 'bg-red-100 text-red-800'
                          : 'bg-amber-100 text-amber-800'
                      }`}
                    >
                      {permissionState === 'granted'
                        ? 'مفعل وجاهز ✓'
                        : permissionState === 'denied'
                        ? 'مرفوض ✕'
                        : 'غير مفعل بعد'}
                    </span>
                  </div>
                  <p className="text-[11px] text-stone-500">
                    عدد الأجهزة النشطة المرتبطة بحسابك: {activeDevices} جهاز
                  </p>
                </div>
              </div>

              <div className="flex items-center gap-2 w-full sm:w-auto">
                <button
                  type="button"
                  onClick={handleEnablePushNotifications}
                  disabled={isSubscribing}
                  className="w-full sm:w-auto px-4 py-2 rounded-xl bg-[#1B4D3E] hover:bg-[#153e32] text-white font-bold text-xs shadow-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50"
                >
                  <Bell className="w-3.5 h-3.5" />
                  <span>{permissionState === 'granted' ? 'تحديث اشتراك الجهاز' : 'تفعيل الإشعارات الآن'}</span>
                </button>

                {permissionState === 'granted' && (
                  <button
                    type="button"
                    onClick={handleSendTestNotification}
                    disabled={isSendingTest}
                    className="px-3.5 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-stone-950 font-bold text-xs shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                    title="اختبر وصول الإشعار لهاتفك الآن"
                  >
                    <Send className="w-3.5 h-3.5" />
                    <span>إشعار تجريبي</span>
                  </button>
                )}
              </div>
            </div>
          </div>

          {/* Preferences Form */}
          {preferences && (
            <div className="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/90 shadow-sm space-y-6">
              <h2 className="text-base font-black text-stone-900 border-b border-stone-100 pb-3">
                فئات الإشعارات التعليمية
              </h2>

              <div className="space-y-4">
                {/* 1. Daily Learning Reminder */}
                <div className="flex items-center justify-between p-3.5 rounded-2xl hover:bg-stone-50 transition-colors">
                  <div className="flex items-center gap-3">
                    <div className="w-9 h-9 rounded-xl bg-emerald-50 text-[#1B4D3E] flex items-center justify-center shrink-0">
                      <Sparkles className="w-5 h-5 text-emerald-600" />
                    </div>
                    <div>
                      <div className="text-sm font-bold text-stone-900">تذكير الورد اليومي</div>
                      <div className="text-xs text-stone-500">
                        تنبيهك لإكمال دقائق هدفك اليومي قبل انتهاء اليوم (لا يُرسل إذا أتممت هدفك).
                      </div>
                    </div>
                  </div>
                  <input
                    type="checkbox"
                    checked={preferences.daily_reminder_enabled}
                    onChange={() => handleToggle('daily_reminder_enabled')}
                    className="w-5 h-5 accent-[#1B4D3E] rounded cursor-pointer"
                  />
                </div>

                {/* 2. Review Due SRS Reminder */}
                <div className="flex items-center justify-between p-3.5 rounded-2xl hover:bg-stone-50 transition-colors">
                  <div className="flex items-center gap-3">
                    <div className="w-9 h-9 rounded-xl bg-blue-50 text-blue-800 flex items-center justify-center shrink-0">
                      <RotateCcw className="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                      <div className="text-sm font-bold text-stone-900">مراجعة المفردات (SRS)</div>
                      <div className="text-xs text-stone-500">
                        تنبيه عند استحقاق كلمات في نظام التكرار المتباعد لتثبيتها في الذاكرة الدائمة.
                      </div>
                    </div>
                  </div>
                  <input
                    type="checkbox"
                    checked={preferences.review_due_enabled}
                    onChange={() => handleToggle('review_due_enabled')}
                    className="w-5 h-5 accent-[#1B4D3E] rounded cursor-pointer"
                  />
                </div>

                {/* 3. Streak Saver Reminder */}
                <div className="flex items-center justify-between p-3.5 rounded-2xl hover:bg-stone-50 transition-colors">
                  <div className="flex items-center gap-3">
                    <div className="w-9 h-9 rounded-xl bg-orange-50 text-orange-800 flex items-center justify-center shrink-0">
                      <Flame className="w-5 h-5 text-orange-600" />
                    </div>
                    <div>
                      <div className="text-sm font-bold text-stone-900">حماية سلسلة الالتزام (Streak)</div>
                      <div className="text-xs text-stone-500">
                        تذكير ذكي لحماية أيام التزامك المتتالية من الانقطاع في المساء.
                      </div>
                    </div>
                  </div>
                  <input
                    type="checkbox"
                    checked={preferences.streak_saver_enabled}
                    onChange={() => handleToggle('streak_saver_enabled')}
                    className="w-5 h-5 accent-[#1B4D3E] rounded cursor-pointer"
                  />
                </div>

                {/* 4. New Content */}
                <div className="flex items-center justify-between p-3.5 rounded-2xl hover:bg-stone-50 transition-colors">
                  <div className="flex items-center gap-3">
                    <div className="w-9 h-9 rounded-xl bg-purple-50 text-purple-800 flex items-center justify-center shrink-0">
                      <BookOpen className="w-5 h-5 text-purple-600" />
                    </div>
                    <div>
                      <div className="text-sm font-bold text-stone-900">قصص ومحتوى جديد</div>
                      <div className="text-xs text-stone-500">
                        إشعارك عند إضافة وحدات تعليمية أو قصص إسبانية تفاعلية جديدة.
                      </div>
                    </div>
                  </div>
                  <input
                    type="checkbox"
                    checked={preferences.new_content_enabled}
                    onChange={() => handleToggle('new_content_enabled')}
                    className="w-5 h-5 accent-[#1B4D3E] rounded cursor-pointer"
                  />
                </div>

                {/* 5. Assessment Results */}
                <div className="flex items-center justify-between p-3.5 rounded-2xl hover:bg-stone-50 transition-colors">
                  <div className="flex items-center gap-3">
                    <div className="w-9 h-9 rounded-xl bg-amber-50 text-amber-800 flex items-center justify-center shrink-0">
                      <Award className="w-5 h-5 text-amber-600" />
                    </div>
                    <div>
                      <div className="text-sm font-bold text-stone-900">نتائج امتحانات التخرج</div>
                      <div className="text-xs text-stone-500">
                        إشعارك باجتياز امتحانات المستويات وفتح المستوى التالي.
                      </div>
                    </div>
                  </div>
                  <input
                    type="checkbox"
                    checked={preferences.assessment_results_enabled}
                    onChange={() => handleToggle('assessment_results_enabled')}
                    className="w-5 h-5 accent-[#1B4D3E] rounded cursor-pointer"
                  />
                </div>
              </div>

              {/* Time and Timezone Controls */}
              <div className="pt-4 border-t border-stone-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-stone-700 flex items-center gap-1.5">
                    <Clock className="w-4 h-4 text-[#1B4D3E]" />
                    <span>وقت التذكير اليومي المفضل:</span>
                  </label>
                  <input
                    type="time"
                    value={preferences.preferred_reminder_time}
                    onChange={(e) => handleTimeChange(e.target.value)}
                    className="w-full p-3 rounded-xl border border-stone-300 font-bold text-stone-800 text-sm focus:border-[#1B4D3E] focus:outline-none"
                  />
                  <p className="text-[11px] text-stone-400">سنرسل لك التذكير خلال هذه الساعة.</p>
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-stone-700 flex items-center gap-1.5">
                    <Globe className="w-4 h-4 text-[#1B4D3E]" />
                    <span>المنطقة الزمنية:</span>
                  </label>
                  <input
                    type="text"
                    disabled
                    value={preferences.timezone}
                    className="w-full p-3 rounded-xl border border-stone-200 bg-stone-100 font-medium text-stone-600 text-sm cursor-not-allowed"
                  />
                  <p className="text-[11px] text-stone-400">محددة تلقائياً بحسب ملفك الشخصي.</p>
                </div>
              </div>

              {/* Save Button */}
              <div className="pt-4">
                <button
                  type="button"
                  onClick={handleSavePreferences}
                  disabled={isSaving}
                  className="w-full py-3.5 rounded-2xl bg-[#1B4D3E] hover:bg-[#153e32] text-white font-bold text-sm shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                >
                  <CheckCircle2 className="w-4 h-4" />
                  <span>{isSaving ? 'جاري الحفظ...' : 'حفظ التفضيلات'}</span>
                </button>
              </div>
            </div>
          )}
        </main>
      </div>
    </div>
  );
};
