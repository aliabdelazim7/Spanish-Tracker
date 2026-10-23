import React from 'react';
import { NavLink } from 'react-router-dom';
import {
  LayoutDashboard,
  BookOpen,
  BookMarked,
  RotateCcw,
  Mic,
  TrendingUp,
  Award,
  Settings,
  HelpCircle,
  PenTool,
  AlertTriangle,
  Bell
} from 'lucide-react';

export const Sidebar: React.FC = () => {
  const navItems = [
    { to: '/dashboard', label: 'لوحة التحكم', icon: LayoutDashboard },
    { to: '/learn', label: 'المسار التعليمي', icon: BookOpen },
    { to: '/journal', label: 'دفتر اليوميات الذكي', icon: PenTool },
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
    <aside className="hidden md:flex md:w-64 bg-white/70 backdrop-blur-xs border-l border-amber-900/10 flex-col justify-between p-4 min-h-[calc(100vh-4rem)] flex-shrink-0">
      <div className="space-y-1.5">
        <div className="px-3 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider">
          التنقل التعليمي
        </div>
        {navItems.map((item) => {
          const Icon = item.icon;
          return (
            <NavLink
              key={item.to}
              to={item.to}
              className={({ isActive }) =>
                `flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all ${
                  isActive
                    ? 'bg-[#1B4D3E] text-white shadow-sm'
                    : 'text-gray-600 hover:text-[#1B4D3E] hover:bg-emerald-50/60'
                }`
              }
            >
              <Icon className="w-5 h-5 flex-shrink-0" />
              <span>{item.label}</span>
            </NavLink>
          );
        })}
      </div>

      {/* Egyptian dialect learning tip badge */}
      <div className="p-3.5 rounded-2xl bg-amber-50/90 border border-amber-200/80 text-xs text-amber-900 space-y-1 shadow-2xs">
        <div className="font-bold flex items-center gap-1.5 text-amber-800">
          <HelpCircle className="w-4 h-4 text-amber-600" />
          <span>معلومة اليوم</span>
        </div>
        <p className="leading-relaxed">
          حرف <span className="font-bold text-amber-950">"H"</span> في الإسبانية صامت دايماً، عشان كده كلمة <span className="font-bold text-amber-950">"Hola"</span> بتتنطق <span className="font-bold text-amber-950">"أولا"</span> مش "هولا"!
        </p>
      </div>
    </aside>
  );
};
