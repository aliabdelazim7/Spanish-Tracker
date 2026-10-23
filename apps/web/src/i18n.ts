import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import arTranslation from './locales/ar/translation.json';
import enTranslation from './locales/en/translation.json';

const savedLang = (typeof window !== 'undefined' && localStorage.getItem('spanish_tracker_lang')) || 'ar';

i18n
  .use(initReactI18next)
  .init({
    resources: {
      ar: { translation: arTranslation },
      en: { translation: enTranslation },
    },
    lng: savedLang,
    fallbackLng: 'ar',
    interpolation: {
      escapeValue: false,
    },
  });

// Apply document direction and lang attribute automatically
export function setAppLanguage(lang: 'ar' | 'en') {
  i18n.changeLanguage(lang);
  if (typeof window !== 'undefined') {
    localStorage.setItem('spanish_tracker_lang', lang);
    document.documentElement.lang = lang;
    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
  }
}

// Initial DOM sync
if (typeof window !== 'undefined') {
  document.documentElement.lang = savedLang;
  document.documentElement.dir = savedLang === 'ar' ? 'rtl' : 'ltr';
}

export default i18n;
