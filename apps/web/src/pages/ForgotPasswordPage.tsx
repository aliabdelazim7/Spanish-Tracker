import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { Compass, CheckCircle2, ArrowRight } from 'lucide-react';
import { apiClient } from '../api/client';

export const ForgotPasswordPage: React.FC = () => {
  const [email, setEmail] = useState('');
  const [isSubmitted, setIsSubmitted] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email) return;

    setIsLoading(true);
    try {
      await apiClient('/auth/forgot-password', {
        method: 'POST',
        body: JSON.stringify({ email }),
      });
      setIsSubmitted(true);
    } catch {
      setIsSubmitted(true);
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
        <h2 className="text-2xl font-bold text-stone-900">استعادة كلمة المرور</h2>
      </div>

      <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div className="bg-white py-8 px-6 shadow-xl rounded-3xl border border-stone-200/80 sm:px-10 space-y-6">
          {isSubmitted ? (
            <div className="text-center space-y-4">
              <div className="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                <CheckCircle2 className="w-7 h-7" />
              </div>
              <p className="text-sm text-stone-700 leading-relaxed">
                إذا كان هذا البريد مسجلاً لدينا، فستتلقى تعليمات استعادة كلمة المرور خلال لحظات.
              </p>
              <div className="pt-2 flex flex-col gap-2">
                <Link
                  to={`/reset-password?email=${encodeURIComponent(email)}`}
                  className="w-full py-3 px-4 rounded-xl bg-[#1B4D3E] text-white font-bold text-sm shadow-md hover:bg-[#153e32] transition-all text-center inline-block"
                >
                  إدخال رمز التحقق وكلمة المرور الجديدة
                </Link>
                <Link
                  to="/login"
                  className="inline-flex items-center justify-center gap-1.5 text-xs font-bold text-stone-500 hover:text-[#1B4D3E] pt-1"
                >
                  <span>العودة لصفحة الدخول</span>
                  <ArrowRight className="w-3.5 h-3.5 rotate-180" />
                </Link>
              </div>
            </div>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-4">
              <p className="text-xs text-stone-600 leading-relaxed">
                أدخل عنوان بريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيين كلمة المرور الخاصة بك.
              </p>

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

              <button
                type="submit"
                disabled={isLoading}
                className="w-full py-3.5 px-4 rounded-xl bg-[#1B4D3E] text-white font-bold text-sm shadow-md hover:bg-[#153e32] transition-all disabled:opacity-50"
              >
                {isLoading ? 'جاري الإرسال...' : 'إرسال رابط الاستعادة'}
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
