import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { authApi } from '../api/auth';
import { useAuth } from '../context/AuthContext';
import { Compass, AlertCircle, ArrowRight, Sparkles } from 'lucide-react';

const loginSchema = z.object({
  email: z.string().email('يرجى إدخال بريد إلكتروني صحيح'),
  password: z.string().min(1, 'يرجى إدخال كلمة المرور'),
});

type LoginFormData = z.infer<typeof loginSchema>;

export const LoginPage: React.FC = () => {
  const { login } = useAuth();
  const navigate = useNavigate();
  const [serverError, setServerError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const {
    register,
    handleSubmit,
    setValue,
    formState: { errors },
  } = useForm<LoginFormData>({
    resolver: zodResolver(loginSchema),
  });

  const onSubmit = async (data: LoginFormData) => {
    setServerError(null);
    setIsLoading(true);

    try {
      const response = await authApi.login(data);
      login(response.access_token, response.user);

      if (!response.user.profile?.onboarding_completed) {
        navigate('/onboarding');
      } else {
        navigate('/dashboard');
      }
    } catch (err: any) {
      setServerError(err.message || 'فشل تسجيل الدخول. تحقق من بريدك وكلمة المرور.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleDemoLogin = (email: string, pass: string) => {
    setValue('email', email);
    setValue('password', pass);
    onSubmit({ email, password: pass });
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
        <h2 className="text-2xl font-bold text-stone-900">مرحباً بعودتك!</h2>
        <p className="text-sm text-stone-600">سجل دخولك لمتابعة دروسك وسلسلة حماسك اليومية</p>
      </div>

      <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div className="bg-white py-8 px-6 shadow-xl rounded-3xl border border-stone-200/80 sm:px-10 space-y-6">
          {serverError && (
            <div className="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center gap-2.5">
              <AlertCircle className="w-5 h-5 flex-shrink-0 text-red-600" />
              <span>{serverError}</span>
            </div>
          )}

          <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
            <div>
              <label className="block text-sm font-semibold text-stone-700 mb-1.5">
                البريد الإلكتروني
              </label>
              <input
                type="email"
                {...register('email')}
                placeholder="name@example.com"
                dir="ltr"
                className="w-full px-4 py-3 rounded-xl border border-stone-300 focus:ring-2 focus:ring-[#1B4D3E] focus:border-transparent text-sm text-stone-900 placeholder:text-stone-400 outline-none transition-all"
              />
              {errors.email && (
                <p className="text-xs text-red-600 mt-1">{errors.email.message}</p>
              )}
            </div>

            <div>
              <div className="flex items-center justify-between mb-1.5">
                <label className="block text-sm font-semibold text-stone-700">
                  كلمة المرور
                </label>
                <Link to="/forgot-password" className="text-xs text-emerald-800 hover:underline">
                  نسيت كلمة المرور؟
                </Link>
              </div>
              <input
                type="password"
                {...register('password')}
                placeholder="••••••••"
                dir="ltr"
                className="w-full px-4 py-3 rounded-xl border border-stone-300 focus:ring-2 focus:ring-[#1B4D3E] focus:border-transparent text-sm text-stone-900 placeholder:text-stone-400 outline-none transition-all"
              />
              {errors.password && (
                <p className="text-xs text-red-600 mt-1">{errors.password.message}</p>
              )}
            </div>

            <button
              type="submit"
              disabled={isLoading}
              className="w-full py-3.5 px-4 rounded-xl bg-[#1B4D3E] text-white font-bold text-sm shadow-md hover:bg-[#153e32] transition-all disabled:opacity-50 flex items-center justify-center gap-2"
            >
              {isLoading ? (
                <span>جاري تسجيل الدخول...</span>
              ) : (
                <>
                  <span>تسجيل الدخول</span>
                  <ArrowRight className="w-4 h-4 rotate-180" />
                </>
              )}
            </button>
          </form>

          {/* 1-Click Demo Logins */}
          <div className="pt-4 border-t border-stone-100 space-y-3">
            <p className="text-xs text-center text-stone-400 font-medium">أو جرب الحسابات التجريبية بنقرة واحدة:</p>
            <div className="grid grid-cols-2 gap-2">
              <button
                type="button"
                onClick={() => handleDemoLogin('learner@example.com', 'password123')}
                className="py-2.5 px-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-900 font-bold text-xs border border-emerald-200/60 flex items-center justify-center gap-1.5 transition-colors"
              >
                <Sparkles className="w-3.5 h-3.5 text-emerald-600" />
                <span>حساب متعلم تجريبي</span>
              </button>

              <button
                type="button"
                onClick={() => handleDemoLogin('admin@example.com', 'admin12345')}
                className="py-2.5 px-3 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold text-xs border border-stone-200 flex items-center justify-center gap-1.5 transition-colors"
              >
                <span>حساب مشرف النظام</span>
              </button>
            </div>
          </div>

          <div className="text-center pt-2">
            <p className="text-xs text-stone-600">
              ليس لديك حساب بعد؟{' '}
              <Link to="/register" className="text-[#1B4D3E] font-bold hover:underline">
                إنشاء حساب جديد مجاناً
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};
