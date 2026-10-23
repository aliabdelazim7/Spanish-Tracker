import React from 'react';
import { NavLink, useLocation } from 'react-router-dom';
import {
  LayoutDashboard,
  BookOpen,
  BookMarked,
  RotateCcw,
  Mic,
  User
} from 'lucide-react';
import { useAuth } from '../context/AuthContext';

export const MobileTabBar: React.FC = () => {
  const { isAuthenticated } = useAuth();
  const location = useLocation();

  if (!isAuthenticated) return null;

  // Hide tab bar inside active full-screen exercise sessions
  const isLessonSession = location.pathname.startsWith('/learn/lessons/');
  const isStoryReaderSession = /^\/stories\/\d+/.test(location.pathname);
  const isPlacementTestSession = location.pathname === '/placement-test';

  if (isLessonSession || isStoryReaderSession || isPlacementTestSession) {
    return null;
  }

  const tabs = [
    { to: '/dashboard', label: 'الرئيسية', icon: LayoutDashboard },
    { to: '/learn', label: 'المنهج', icon: BookOpen },
    { to: '/stories', label: 'قصص', icon: BookMarked },
    { to: '/review', label: 'مراجعة', icon: RotateCcw },
    { to: '/speak', label: 'تحدث', icon: Mic },
    { to: '/profile', label: 'حسابي', icon: User },
  ];

  return (
    <nav
      aria-label="التنقل السفلي للموبايل"
      className="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-lg border-t border-stone-200/90 shadow-[0_-4px_16px_rgba(0,0,0,0.06)] px-2 py-1.5 pb-safe"
      dir="rtl"
    >
      <div className="flex items-center justify-around max-w-md mx-auto">
        {tabs.map((tab) => {
          const Icon = tab.icon;
          return (
            <NavLink
              key={tab.to}
              to={tab.to}
              className={({ isActive }) =>
                `flex flex-col items-center justify-center min-w-[50px] py-1 px-1 rounded-2xl transition-all ${
                  isActive
                    ? 'text-[#1B4D3E] font-bold scale-105'
                    : 'text-stone-500 hover:text-stone-800'
                }`
              }
            >
              {({ isActive }) => (
                <>
                  <div
                    className={`w-9 h-7 rounded-xl flex items-center justify-center transition-all ${
                      isActive
                        ? 'bg-emerald-100 text-[#1B4D3E] shadow-2xs'
                        : 'text-stone-500'
                    }`}
                  >
                    <Icon className="w-5 h-5" />
                  </div>
                  <span className="text-[10px] tracking-tight mt-0.5 leading-none">
                    {tab.label}
                  </span>
                </>
              )}
            </NavLink>
          );
        })}
      </div>
    </nav>
  );
};
