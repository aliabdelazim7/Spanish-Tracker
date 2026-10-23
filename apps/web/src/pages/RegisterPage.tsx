import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { authApi } from '../api/auth';
import { useAuth } from '../context/AuthContext';
import { Compass, AlertCircle, ArrowRight, CheckCircle2 } from 'lucide-react';

const registerSchema = z
  .object({
    name: z.string().min(2, 'يرجى إدخال اسمك الكامل'),
    email: z.string().email('يرجى إدخال بريد إلكتروني صحيح'),
    password: z.string().min(8, 'يجب أن لا تقل كلمة المرور عن 8 أحرف'),
    password_confirmation: z.string(),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'كلمتا المرور غير متطابقتين',
    path: ['password_confirmation'],
  });

type RegisterFormData = z.infer<typeof registerSchema>;

export const RegisterPage: React.FC = () => {
  const { login } = useAuth();
  const navigate = useNavigate();
  const [serverError, setServerError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<RegisterFormData>({
    resolver: zodResolver(registerSchema),
  });

  const onSubmit = async (data: RegisterFormData) => {
    setServerError(null);
    setIsLoading(true);

    try {
      const response = await authApi.register(data);
      login(response.access_token, response.user);
      navigate('/onboarding');
    } catch (err: any) {
      setServerError(err.message || 'حدث خطأ أثناء إنشاء الحساب.');
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
        <h2 className="text-2xl font-bold text-stone-900">أنشئ حسابك التعليمي</h2>
        <p className="text-sm text-stone-600">انضم اليوم وابدأ أول خطواتك لإتقان اللغة الإسبانية</p>
      </div>

      <div className="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div className="bg-white py-8 px-6 shadow-xl rounded-3xl border border-stone-200/80 sm:px-10 space-y-6">
          {serverError && (
            <div className="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-center gap-2.5">
              <AlertCircle className="w-5 h-5 flex-shrink-0 text-red-600" />
              <span>{serverError}</span>
            </div>
          )}

          <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
            <div>
              <label className="block text-sm font-semibold text-stone-700 mb-1.5">
                الاسم الكامل
              </label>
              <input
                type="text"
                {...register('name')}
                placeholder="مثال: أحمد محمود"
                className="w-full px-4 py-3 rounded-xl border border-stone-300 focus:ring-2 focus:ring-[#1B4D3E] focus:border-transparent text-sm text-stone-900 placeholder:text-stone-400 outline-none transition-all"
              />
              {errors.name && (
                <p className="text-xs text-red-600 mt-1">{errors.name.message}</p>
              )}
            </div>

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
              <label className="block text-sm font-semibold text-stone-700 mb-1.5">
                كلمة المرور (8 أحرف على الأقل)
              </label>
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

            <div>
              <label className="block text-sm font-semibold text-stone-700 mb-1.5">
                تأكيد كلمة المرور
              </label>
              <input
                type="password"
                {...register('password_confirmation')}
                placeholder="••••••••"
                dir="ltr"
                className="w-full px-4 py-3 rounded-xl border border-stone-300 focus:ring-2 focus:ring-[#1B4D3E] focus:border-transparent text-sm text-stone-900 placeholder:text-stone-400 outline-none transition-all"
              />
              {errors.password_confirmation && (
                <p className="text-xs text-red-600 mt-1">{errors.password_confirmation.message}</p>
              )}
            </div>

            <button
              type="submit"
              disabled={isLoading}
              className="w-full py-3.5 px-4 rounded-xl bg-[#1B4D3E] text-white font-bold text-sm shadow-md hover:bg-[#153e32] transition-all disabled:opacity-50 flex items-center justify-center gap-2 mt-2"
            >
              {isLoading ? (
                <span>جاري إنشاء الحساب...</span>
              ) : (
                <>
                  <span>متابعة إعداد خطتي التعليمية</span>
                  <ArrowRight className="w-4 h-4 rotate-180" />
                </>
              )}
            </button>
          </form>

          <div className="text-center pt-2">
            <p className="text-xs text-stone-600">
              لديك حساب بالفعل؟{' '}
              <Link to="/login" className="text-[#1B4D3E] font-bold hover:underline">
                تسجيل الدخول
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};
