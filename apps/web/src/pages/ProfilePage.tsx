import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { authApi } from '../api/auth';
import { Navbar } from '../components/Navbar';
import { Sidebar } from '../components/Sidebar';
import {
  Compass,
  User,
  Settings,
  CheckCircle2,
  AlertCircle,
  Save,
  Globe,
  Clock,
  Sparkles,
  BellRing,
  ArrowRight
} from 'lucide-react';

export const ProfilePage: React.FC = () => {
  const { user, refreshUser } = useAuth();
  const profile = user?.profile;

  const [learningReason, setLearningReason] = useState(profile?.learning_reason || 'travel');
  const [dailyMinutes, setDailyMinutes] = useState(profile?.daily_goal_minutes || 15);
  const [experienceLevel, setExperienceLevel] = useState(profile?.experience_level || 'zero');
  const [explanationDialect, setExplanationDialect] = useState(profile?.explanation_dialect || 'egyptian_arabic');
  const [targetVariant, setTargetVariant] = useState(profile?.target_variant || 'spain');

  const [isSaving, setIsSaving] = useState(false);
  const [saveSuccess, setSaveSuccess] = useState(false);

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSaving(true);
    setSaveSuccess(false);

    try {
      await authApi.updateOnboarding({
        learning_reason: learningReason,
        daily_goal_minutes: dailyMinutes,
        experience_level: experienceLevel,
        explanation_dialect: explanationDialect,
        target_variant: targetVariant,
      });

      await refreshUser();
      setSaveSuccess(true);
      setTimeout(() => setSaveSuccess(false), 3000);
    } catch (err) {
      console.error(err);
    } finally {
      setIsSaving(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col">
      <Navbar />

      <div className="flex-1 flex flex-col md:flex-row">
        <Sidebar />

        <main className="flex-1 p-4 sm:p-6 lg:p-8 space-y-6 max-w-3xl w-full pb-24 md:pb-8">
          <div className="space-y-1">
            <h1 className="text-3xl font-black text-stone-900">
              الملف الشخصي وإعدادات التعلم
            </h1>
            <p className="text-xs sm:text-sm text-stone-600">
              خصص تجربتك، وتيرة دراستك اليومية، واللهجة المفضلة للشروحات.
            </p>
          </div>

          <div className="bg-white rounded-3xl p-6 sm:p-8 border border-stone-200/80 shadow-xs space-y-6">
            {/* User Info Header */}
            <div className="flex items-center gap-4 pb-6 border-b border-stone-100">
              <div className="w-16 h-16 rounded-2xl bg-[#1B4D3E] text-amber-300 flex items-center justify-center font-black text-2xl shadow-xs">
                {user?.name?.charAt(0) || 'U'}
              </div>
              <div className="space-y-1 text-right">
                <h2 className="text-xl font-bold text-stone-900">{user?.name}</h2>
                <div className="text-xs text-stone-500">{user?.email}</div>
                <span className="inline-block text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                  المستوى المقدر: {profile?.estimated_cefr_level || 'A1'}
                </span>
              </div>
            </div>

            {/* Quick Link to Notifications Settings */}
            <Link
              to="/settings/notifications"
              className="flex items-center justify-between p-4 rounded-2xl bg-emerald-50 hover:bg-emerald-100/80 border border-emerald-200 transition-all text-right group"
            >
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-xl bg-[#1B4D3E] text-amber-300 flex items-center justify-center shrink-0 shadow-xs">
                  <BellRing className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="text-sm font-bold text-stone-900 group-hover:text-[#1B4D3E] transition-colors">
                    إعدادات الإشعارات والتذكير الذكي (Web Push)
                  </h3>
                  <p className="text-xs text-stone-600">
                    اضبط توقيت تذكيرك اليومي، مراجعة المفردات، وسلسلة الأيام.
                  </p>
                </div>
              </div>
              <ArrowRight className="w-4 h-4 text-[#1B4D3E] rotate-180 group-hover:translate-x-1 transition-transform" />
            </Link>

            {saveSuccess && (
              <div className="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-bold flex items-center gap-2">
                <CheckCircle2 className="w-4 h-4 text-emerald-600" />
                <span>تم حفظ تفضيلاتك بنجاح!</span>
              </div>
            )}

            <form onSubmit={handleSave} className="space-y-5">
              {/* Daily Goal */}
              <div>
                <label className="block text-xs font-bold text-stone-700 mb-1.5 text-right">
                  الهدف اليومي (بالدقائق):
                </label>
                <select
                  value={dailyMinutes}
                  onChange={(e) => setDailyMinutes(parseInt(e.target.value, 10))}
                  className="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm font-semibold text-stone-900 focus:border-[#1B4D3E] outline-none"
                >
                  <option value={5}>5 دقائق يومياً (وتيرة خفيفة)</option>
                  <option value={15}>15 دقيقة يومياً (الوتيرة الموصى بها)</option>
                  <option value={30}>30 دقيقة يومياً (وتيرة مكثفة)</option>
                  <option value={60}>60 دقيقة يومياً (تفرغ كامل)</option>
                </select>
              </div>

              {/* Dialect */}
              <div>
                <label className="block text-xs font-bold text-stone-700 mb-1.5 text-right">
                  لغة وتنسيق الشروحات:
                </label>
                <select
                  value={explanationDialect}
                  onChange={(e) => setExplanationDialect(e.target.value as any)}
                  className="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm font-semibold text-stone-900 focus:border-[#1B4D3E] outline-none"
                >
                  <option value="egyptian_arabic">العامية المصرية المبسطة (موصى بها للمتعلمين المصريين)</option>
                  <option value="msa">اللغة العربية الفصحى القياسية</option>
                </select>
              </div>

              {/* Spanish Variant */}
              <div>
                <label className="block text-xs font-bold text-stone-700 mb-1.5 text-right">
                  اللهجة الإسبانية المستهدفة:
                </label>
                <select
                  value={targetVariant}
                  onChange={(e) => setTargetVariant(e.target.value as any)}
                  className="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm font-semibold text-stone-900 focus:border-[#1B4D3E] outline-none"
                >
                  <option value="spain">إسبانيا (القياسية - España)</option>
                  <option value="latin_america">أمريكا اللاتينية (قيد الإعداد)</option>
                </select>
              </div>

              {/* Reason */}
              <div>
                <label className="block text-xs font-bold text-stone-700 mb-1.5 text-right">
                  الدافع الرئيسي للتعلم:
                </label>
                <select
                  value={learningReason}
                  onChange={(e) => setLearningReason(e.target.value as any)}
                  className="w-full px-4 py-3 rounded-xl border border-stone-300 text-sm font-semibold text-stone-900 focus:border-[#1B4D3E] outline-none"
                >
                  <option value="travel">السياحة والسفر إلى إسبانيا</option>
                  <option value="career">التطوير المهني وفرص العمل</option>
                  <option value="education">الدراسة الجامعية والابتعاث</option>
                  <option value="culture">الثقافة والفنون والرياضة</option>
                  <option value="personal">تطوير الذات وتمرين الذاكرة</option>
                </select>
              </div>

              <div className="pt-4">
                <button
                  type="submit"
                  disabled={isSaving}
                  className="w-full py-3.5 rounded-xl bg-[#1B4D3E] text-white font-bold text-sm shadow-md hover:bg-[#153e32] flex items-center justify-center gap-2 transition-all"
                >
                  <Save className="w-4 h-4" />
                  <span>{isSaving ? 'جاري الحفظ...' : 'حفظ التعديلات'}</span>
                </button>
              </div>
            </form>
          </div>
        </main>
      </div>
    </div>
  );
};
