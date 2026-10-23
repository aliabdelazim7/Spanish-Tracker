import React, { useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { Compass, CheckCircle2, ArrowRight, Lock, KeyRound, AlertCircle } from 'lucide-react';
import { apiClient } from '../api/client';

export const ResetPasswordPage: React.FC = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();

  const [email, setEmail] = useState(searchParams.get('email') || '');
  const [token, setToken] = useState(searchParams.get('token') || '');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [isSuccess, setIsSuccess] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMessage(null);

    if (password !== passwordConfirmation) {
      setErrorMessage('كلمتا المرور غير متطابقتين.');
      return;
    }

    if (password.length < 8) {
      setErrorMessage('يجب أن لا تقل كلمة المرور عن 8 أحرف.');
      return;
    }

    setIsLoading(true);
    try {
      await apiClient('/auth/reset-password', {
        method: 'POST',
        body: JSON.stringify({
          email,
          token,
          password,
          password_confirmation: passwordConfirmation,
        }),
      });
      setIsSuccess(true);
    } catch (err: any) {
      setErrorMessage(err.message || 'فشل تحديث كلمة المرور. تحقق من صحة الرمز والبريد.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#FAF8F5] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
      <div className="sm:mx-auto sm:w-full sm:max-w-md text-center space-y-3">
        <Link to="/" className="inline-flex items-center gap-2">
          <div className="w-12 h-12 rounded-2xl bg-[#1B4D3E] flex items-center justify-center text-amber-300 shadow-md">
            <Compass className="w-7 h-7" />
          </div>
          <span className="text-2xl font-black text-[#1B4D3E]">Ruta Español</span>
        </Link>
        <h2 className="text-2xl font-bold text-stone-900">تعيين كلمة مرور جديدة</h2>
      </div>

      <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div className="bg-white py-8 px-6 shadow-xl rounded-3xl border border-stone-200/80 sm:px-10 space-y-6">
          {isSuccess ? (
            <div className="text-center space-y-4">
              <div className="w-14 h-14 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                <CheckCircle2 className="w-8 h-8" />
              </div>
              <h3 className="text-lg font-bold text-stone-900">تم تغيير كلمة المرور بنجاح</h3>
              <p className="text-sm text-stone-600 leading-relaxed">
                تم تحديث كلمة المرور الخاصة بحسابك بنجاح. يمكنك الآن تسجيل الدخول بكلمة المرور الجديدة.
              </p>
              <button
                type="button"
                onClick={() => navigate('/login')}
                className="w-full py-3.5 px-4 rounded-xl bg-[#1B4D3E] text-white font-bold text-sm shadow-md hover:bg-[#153e32] transition-all flex items-center justify-center gap-2"
              >
                <span>الانتقال لتسجيل الدخول</span>
                <ArrowRight className="w-4 h-4 rotate-180" />
              </button>
            </div>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-4">
              {errorMessage && (
                <div className="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-2">
                  <AlertCircle className="w-4 h-4 shrink-0 mt-0.5" />
                  <span>{errorMessage}</span>
                </div>
              )}

              <div>
                <label className="block text-sm font-semibold text-stone-700 mb-1.5">
                  البريد الإلكتروني
                </label>
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                  placeholder="name@example.com"
                  dir="ltr"
                  className="w-full px-4 py-3 rounded-xl border border-stone-300 focus:ring-2 focus:ring-[#1B4D3E] focus:border-transparent text-sm text-stone-900 placeholder:text-stone-400 outline-none transition-all"
                />
              </div>

              <div>
                <label className="block text-sm font-semibold text-stone-700 mb-1.5">
                  رمز التحقق / الاستعادة
                </label>
                <div className="relative">
                  <input
                    type="text"
                    value={token}
                    onChange={(e) => setToken(e.target.value)}
                    required
                    placeholder="رمز التحقق المستلم"
                    dir="ltr"
                    className="w-full px-4 py-3 rounded-xl border border-stone-300 focus:ring-2 focus:ring-[#1B4D3E] focus:border-transparent text-sm text-stone-900 placeholder:text-stone-400 outline-none transition-all font-mono"
                  />
                  <KeyRound className="w-4 h-4 text-stone-400 absolute left-3 top-3.5" />
                </div>
              </div>

              <div>
                <label className="block text-sm font-semibold text-stone-700 mb-1.5">
                  كلمة المرور الجديدة
                </label>
                <div className="relative">
                  <input
                    type="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    required
                    minLength={8}
                    placeholder="8 أحرف على الأقل"
                    dir="ltr"
                    className="w-full px-4 py-3 rounded-xl border border-stone-300 focus:ring-2 focus:ring-[#1B4D3E] focus:border-transparent text-sm text-stone-900 placeholder:text-stone-400 outline-none transition-all"
                  />
                  <Lock className="w-4 h-4 text-stone-400 absolute left-3 top-3.5" />
                </div>
              </div>

              <div>
                <label className="block text-sm font-semibold text-stone-700 mb-1.5">
                  تأكيد كلمة المرور الجديدة
                </label>
                <div className="relative">
                  <input
                    type="password"
                    value={passwordConfirmation}
                    onChange={(e) => setPasswordConfirmation(e.target.value)}
                    required
                    minLength={8}
                    placeholder="أعد إدخال كلمة المرور"
                    dir="ltr"
                    className="w-full px-4 py-3 rounded-xl border border-stone-300 focus:ring-2 focus:ring-[#1B4D3E] focus:border-transparent text-sm text-stone-900 placeholder:text-stone-400 outline-none transition-all"
                  />
                  <Lock className="w-4 h-4 text-stone-400 absolute left-3 top-3.5" />
                </div>
              </div>

              <button
                type="submit"
                disabled={isLoading}
                className="w-full py-3.5 px-4 rounded-xl bg-[#1B4D3E] text-white font-bold text-sm shadow-md hover:bg-[#153e32] transition-all disabled:opacity-50"
              >
                {isLoading ? 'جاري الحفظ...' : 'حفظ كلمة المرور الجديدة'}
              </button>

              <div className="text-center pt-2">
                <Link to="/login" className="text-xs text-stone-500 hover:text-[#1B4D3E]">
                  العودة لتسجيل الدخول
                </Link>
              </div>
            </form>
          )}
        </div>
      </div>
    </div>
  );
};
