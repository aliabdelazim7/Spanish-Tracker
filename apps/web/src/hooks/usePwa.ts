import { useState, useEffect, useCallback } from 'react';
import { getQueuedProgress, syncQueuedProgress } from '../utils/offlineStorage';

interface BeforeInstallPromptEvent extends Event {
  prompt: () => Promise<void>;
  userChoice: Promise<{ outcome: 'accepted' | 'dismissed'; platform: string }>;
}

export function usePwa() {
  const [deferredPrompt, setDeferredPrompt] = useState<BeforeInstallPromptEvent | null>(null);
  const [canInstall, setCanInstall] = useState(false);
  const [isStandalone, setIsStandalone] = useState(false);
  const [isIos, setIsIos] = useState(false);
  const [isDismissed, setIsDismissed] = useState(false);
  const [isOnline, setIsOnline] = useState(navigator.onLine);
  const [queuedSyncCount, setQueuedSyncCount] = useState(0);

  useEffect(() => {
    // 1. Detect Standalone mode (already installed)
    const checkStandalone = () => {
      const isStandaloneMode =
        window.matchMedia('(display-mode: standalone)').matches ||
        (window.navigator as any).standalone === true ||
        document.referrer.includes('android-app://');
      setIsStandalone(Boolean(isStandaloneMode));
    };

    checkStandalone();
    window.matchMedia('(display-mode: standalone)').addEventListener('change', checkStandalone);

    // 2. Detect iOS Safari
    const userAgent = window.navigator.userAgent.toLowerCase();
    const isIosDevice = /iphone|ipad|ipod/.test(userAgent);
    const isSafari = /safari/.test(userAgent) && !/chrome|crios|fxios/.test(userAgent);
    setIsIos(isIosDevice && isSafari);

    // 3. Check dismissal memory
    const dismissedTime = localStorage.getItem('ruta_pwa_install_dismissed');
    if (dismissedTime) {
      const elapsedDays = (Date.now() - parseInt(dismissedTime, 10)) / (1000 * 60 * 60 * 24);
      if (elapsedDays < 7) {
        setIsDismissed(true);
      }
    }

    // 4. Capture native beforeinstallprompt event
    const handleBeforeInstallPrompt = (e: Event) => {
      e.preventDefault();
      setDeferredPrompt(e as BeforeInstallPromptEvent);
      setCanInstall(true);
    };

    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);

    // 5. Connection status listeners
    const handleOnline = () => {
      setIsOnline(true);
      // Trigger background synchronization when online
      syncQueuedProgress().then((res) => {
        if (res.syncedCount > 0) {
          updateQueueCount();
        }
      });
    };

    const handleOffline = () => {
      setIsOnline(false);
    };

    const updateQueueCount = () => {
      setQueuedSyncCount(getQueuedProgress().length);
    };

    updateQueueCount();
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);

    return () => {
      window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
      window.removeEventListener('online', handleOnline);
      window.removeEventListener('offline', handleOffline);
    };
  }, []);

  const promptInstall = useCallback(async () => {
    if (!deferredPrompt) return;

    try {
      await deferredPrompt.prompt();
      const choice = await deferredPrompt.userChoice;
      if (choice.outcome === 'accepted') {
        setCanInstall(false);
        setDeferredPrompt(null);
      }
    } catch (err) {
      console.error('PWA install prompt error:', err);
    }
  }, [deferredPrompt]);

  const dismissInstall = useCallback(() => {
    setIsDismissed(true);
    localStorage.setItem('ruta_pwa_install_dismissed', Date.now().toString());
  }, []);

  return {
    canInstall: canInstall && !isStandalone && !isDismissed,
    isStandalone,
    isIos: isIos && !isStandalone && !isDismissed,
    isOnline,
    queuedSyncCount,
    promptInstall,
    dismissInstall,
  };
}
