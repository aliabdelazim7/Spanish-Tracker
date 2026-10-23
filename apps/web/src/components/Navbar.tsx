import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useTranslation } from 'react-i18next';
import { setAppLanguage } from '../i18n';
import {
  Flame,
  Sparkles,
  User,
  LogOut,
  Compass,
  Menu,
  X,
  LayoutDashboard,
  BookOpen,
  BookMarked,
  RotateCcw,
  Mic,
  Award,
  TrendingUp,
  Settings,
  HelpCircle,
  PenTool,
  AlertTriangle,
  Globe,
  Bell
} from 'lucide-react';

export const Navbar: React.FC = () => {
  const { user, isAuthenticated, logout } = useAuth();
  const { i18n } = useTranslation();
  const navigate = useNavigate();
  const location = useLocation();
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [currentLang, setCurrentLang] = useState<string>(i18n.language || 'ar');

  const toggleLanguage = () => {
    const next = currentLang === 'ar' ? 'en' : 'ar';
    setAppLanguage(next as 'ar' | 'en');
    setCurrentLang(next);
  };

  const handleLogout = async () => {
    setIsMobileMenuOpen(false);
    await logout();
    navigate('/login');
  };

  const streak = user?.profile?.current_streak ?? 0;
  const xp = user?.profile?.total_xp ?? 0;
  const level = user?.profile?.estimated_cefr_level ?? 'A1';

  const menuItems = [
    { to: '/dashboard', label: 'لوحة التحكم', icon: LayoutDashboard },
    { to: '/learn', label: 'المسار التعليمي والدروس', icon: BookOpen },
    { to: '/journal', label: 'دفتر اليوميات الإسباني', icon: PenTool },
    { to: '/my-mistakes', label: 'أخطائي وملاحظاتي', icon: AlertTriangle },
    { to: '/stories', label: 'قصص تفاعلية', icon: BookMarked },
    { to: '/review', label: 'مراجعة الكلمات (SRS)', icon: RotateCcw },
    { to: '/speak', label: 'استوديو التحدث الذكي', icon: Mic },
    { to: '/progress', label: 'الإحصائيات والأوسمة', icon: Award },
    { to: '/placement-test', label: 'اختبار تحديد المستوى', icon: TrendingUp },
    { to: '/settings/notifications', label: 'إعدادات الإشعارات والتذكير', icon: Bell },
    { to: '/profile', label: 'الإعدادات الشخصية', icon: Settings },
  ];

  return (
    <>
      <header className="sticky top-0 z-40 bg-[#FAF8F5]/95 backdrop-blur-md border-b border-stone-200/80 shadow-2xs">
        <div className="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2">
          {/* Brand */}
          <Link
            to={isAuthenticated ? '/dashboard' : '/'}
            className="flex items-center gap-2.5 group flex-shrink-0"
          >
            <div className="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#1B4D3E] flex items-center justify-center text-amber-200 shadow-md group-hover:scale-105 transition-transform flex-shrink-0">
              <Compass className="w-5 h-5 sm:w-6 sm:h-6 text-amber-300" />
            </div>
            <div>
              <div className="flex items-center gap-1.5">
                <span className="font-black text-lg sm:text-xl text-[#1B4D3E] tracking-tight">
                  Ruta Español
                </span>
                {isAuthenticated && (
                  <span className="text-[10px] sm:text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold border border-emerald-300">
                    {level}
                  </span>
                )}
              </div>
              <p className="hidden sm:block text-[11px] text-stone-500 font-medium">
                الإسبانية للمتعلمين العرب
              </p>
            </div>
          </Link>

          {/* Authenticated user stats & controls */}
          {isAuthenticated ? (
            <div className="flex items-center gap-2 sm:gap-4">
              {/* Gamification Stats: Streak & XP */}
              <div className="flex items-center gap-1.5 sm:gap-2.5">
                {/* Streak */}
                <div
                  title="أيام الالتزام المتتالية"
                  className="flex items-center gap-1 px-2.5 sm:px-3 py-1 rounded-full bg-orange-50 border border-orange-200/80 text-orange-700 text-xs sm:text-sm font-bold shadow-2xs"
                >
                  <Flame className="w-3.5 h-3.5 sm:w-4 sm:h-4 fill-orange-500 text-orange-500 animate-pulse" />
                  <span>{streak}</span>
                </div>

                {/* XP */}
                <div
                  title="نقاط الخبرة XP"
                  className="flex items-center gap-1 px-2.5 sm:px-3 py-1 rounded-full bg-amber-50 border border-amber-200/80 text-amber-800 text-xs sm:text-sm font-bold shadow-2xs"
                >
                  <Sparkles className="w-3.5 h-3.5 sm:w-4 sm:h-4 text-amber-600" />
                  <span>{xp} XP</span>
                </div>
              </div>

              {/* Desktop Quick Nav Links */}
              <div className="hidden lg:flex items-center gap-5 text-sm font-semibold text-stone-700">
                <Link to="/learn" className="hover:text-[#1B4D3E] transition-colors">
                  الدروس
                </Link>
                <Link to="/stories" className="hover:text-[#1B4D3E] transition-colors">
                  قصص
                </Link>
                <Link to="/review" className="hover:text-[#1B4D3E] transition-colors">
                  مراجعة
                </Link>
                <Link to="/speak" className="hover:text-[#1B4D3E] transition-colors">
                  محادثة AI
                </Link>
              </div>

              {/* Desktop User Avatar & Logout */}
              <div className="hidden md:flex items-center gap-2 border-r border-stone-200 pr-3 mr-1">
                {/* Language Switcher */}
                <button
                  type="button"
                  onClick={toggleLanguage}
                  className="px-2.5 py-1.5 rounded-xl border border-stone-200 hover:border-emerald-300 text-xs font-bold text-stone-700 hover:text-[#1B4D3E] flex items-center gap-1 transition-all cursor-pointer shadow-2xs"
                  title="تغيير لغة الواجهة / Switch Language"
                >
                  <Globe className="w-3.5 h-3.5 text-stone-500" />
                  <span>{currentLang === 'ar' ? 'English' : 'العربية'}</span>
                </button>

                <Link
                  to="/profile"
                  className="flex items-center gap-2 p-1.5 rounded-xl hover:bg-emerald-50 text-stone-700 transition-colors"
                  title="الملف الشخصي"
                >
                  <div className="w-8 h-8 rounded-full bg-[#1B4D3E] text-white flex items-center justify-center font-bold text-xs shadow-2xs">
                    {user?.name?.charAt(0) || <User className="w-4 h-4" />}
                  </div>
                  <span className="hidden xl:inline text-xs font-bold text-stone-800">
                    {user?.name}
                  </span>
                </Link>

                <button
                  onClick={handleLogout}
                  className="p-2 rounded-xl hover:bg-red-50 text-stone-400 hover:text-red-600 transition-colors cursor-pointer"
                  title="تسجيل الخروج"
                >
                  <LogOut className="w-4 h-4" />
                </button>
              </div>

              {/* Mobile Drawer Toggle (Hamburger) */}
              <button
                type="button"
                onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
                className="md:hidden p-2 rounded-xl bg-stone-100 hover:bg-emerald-50 text-stone-700 hover:text-[#1B4D3E] transition-colors cursor-pointer"
                aria-label="فتح القائمة الرئيسية"
              >
                {isMobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
              </button>
            </div>
          ) : (
            <div className="flex items-center gap-2">
              <Link
                to="/login"
                className="px-3 sm:px-4 py-2 text-xs sm:text-sm font-bold text-[#1B4D3E] hover:bg-emerald-50 rounded-xl transition-colors"
              >
                دخول
              </Link>
              <Link
                to="/register"
                className="px-3.5 sm:px-5 py-2 rounded-xl bg-[#1B4D3E] text-white font-bold text-xs sm:text-sm hover:bg-[#153e32] shadow-xs transition-all"
              >
                حساب جديد
              </Link>
            </div>
          )}
        </div>
      </header>

      {/* Mobile Slide-Over Drawer */}
      {isAuthenticated && isMobileMenuOpen && (
        <div className="md:hidden fixed inset-0 z-50 flex" dir="rtl">
          {/* Backdrop */}
          <div
            className="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity animate-fade-in"
            onClick={() => setIsMobileMenuOpen(false)}
          />

          {/* Drawer Content */}
          <div className="relative w-4/5 max-w-xs bg-white h-full shadow-2xl flex flex-col justify-between p-5 z-10 overflow-y-auto">
            <div className="space-y-6">
              {/* Drawer Header with User Info */}
              <div className="flex items-center justify-between border-b border-stone-200 pb-4">
                <div className="flex items-center gap-3">
                  <div className="w-11 h-11 rounded-2xl bg-[#1B4D3E] text-white flex items-center justify-center font-black text-base shadow-sm">
                    {user?.name?.charAt(0) || <User className="w-5 h-5" />}
                  </div>
                  <div>
                    <div className="font-bold text-stone-900 text-sm">{user?.name}</div>
                    <div className="text-[11px] text-stone-500">{user?.email}</div>
                  </div>
                </div>

                <button
                  onClick={() => setIsMobileMenuOpen(false)}
                  className="p-1.5 rounded-xl bg-stone-100 text-stone-600 hover:bg-stone-200"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>

              {/* Navigation Items */}
              <nav className="space-y-1">
                <div className="px-3 py-1 text-[11px] font-bold text-stone-400 uppercase tracking-wider">
                  الأقسام التعليمية
                </div>
                {menuItems.map((item) => {
                  const Icon = item.icon;
                  const isActive = location.pathname === item.to;

                  return (
                    <Link
                      key={item.to}
                      to={item.to}
                      onClick={() => setIsMobileMenuOpen(false)}
                      className={`flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm font-semibold transition-all ${
                        isActive
                          ? 'bg-[#1B4D3E] text-white shadow-xs'
                          : 'text-stone-700 hover:bg-emerald-50 hover:text-[#1B4D3E]'
                      }`}
                    >
                      <Icon className="w-4 h-4 flex-shrink-0" />
                      <span>{item.label}</span>
                    </Link>
                  );
                })}
              </nav>

              {/* Egyptian dialect learning tip badge */}
              <div className="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-950 space-y-1">
                <div className="font-bold flex items-center gap-1.5 text-amber-900">
                  <HelpCircle className="w-4 h-4 text-amber-600" />
                  <span>معلومة اليوم</span>
                </div>
                <p className="leading-relaxed text-[11px]">
                  حرف <span className="font-bold">"H"</span> في الإسبانية صامت دايماً، عشان كده كلمة <span className="font-bold">"Hola"</span> بتتنطق <span className="font-bold">"أولا"</span> مش "هولا"!
                </p>
              </div>
            </div>

            {/* Language & Logout Buttons */}
            <div className="pt-4 border-t border-stone-200 space-y-2">
              <button
                type="button"
                onClick={toggleLanguage}
                className="w-full flex items-center justify-center gap-2 py-2.5 rounded-2xl bg-stone-100 text-stone-800 font-bold text-xs hover:bg-stone-200 transition-colors cursor-pointer"
              >
                <Globe className="w-4 h-4 text-stone-600" />
                <span>لغة الواجهة: {currentLang === 'ar' ? 'العربية (تحويل إلى EN)' : 'English (Switch to AR)'}</span>
              </button>

              <button
                onClick={handleLogout}
                className="w-full flex items-center justify-center gap-2 py-3 rounded-2xl bg-red-50 text-red-700 font-bold text-sm hover:bg-red-100 transition-colors"
              >
                <LogOut className="w-4 h-4" />
                <span>تسجيل الخروج</span>
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
};
