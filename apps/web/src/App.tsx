import React, { Suspense, lazy } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider } from './context/AuthContext';
import { ProtectedRoute } from './components/ProtectedRoute';
import { Compass } from 'lucide-react';

// Critical entry pages loaded directly for immediate first contentful paint
import { LandingPage } from './pages/LandingPage';
import { LoginPage } from './pages/LoginPage';
import { RegisterPage } from './pages/RegisterPage';
import { DashboardPage } from './pages/DashboardPage';

// Lazy-loaded routes for code splitting and mobile network performance
const ForgotPasswordPage = lazy(() => import('./pages/ForgotPasswordPage').then(m => ({ default: m.ForgotPasswordPage })));
const ResetPasswordPage = lazy(() => import('./pages/ResetPasswordPage').then(m => ({ default: m.ResetPasswordPage })));
const OnboardingPage = lazy(() => import('./pages/OnboardingPage').then(m => ({ default: m.OnboardingPage })));
const PlacementTestPage = lazy(() => import('./pages/PlacementTestPage').then(m => ({ default: m.PlacementTestPage })));
const PlacementResultsPage = lazy(() => import('./pages/PlacementResultsPage').then(m => ({ default: m.PlacementResultsPage })));
const LearnPage = lazy(() => import('./pages/LearnPage').then(m => ({ default: m.LearnPage })));
const LessonPlayerPage = lazy(() => import('./pages/LessonPlayerPage').then(m => ({ default: m.LessonPlayerPage })));
const StoriesPage = lazy(() => import('./pages/StoriesPage').then(m => ({ default: m.StoriesPage })));
const StoryReaderPage = lazy(() => import('./pages/StoryReaderPage').then(m => ({ default: m.StoryReaderPage })));
const VocabularyReviewPage = lazy(() => import('./pages/VocabularyReviewPage').then(m => ({ default: m.VocabularyReviewPage })));
const SpeakingTutorPage = lazy(() => import('./pages/SpeakingTutorPage').then(m => ({ default: m.SpeakingTutorPage })));
const ProgressPage = lazy(() => import('./pages/ProgressPage').then(m => ({ default: m.ProgressPage })));
const ProfilePage = lazy(() => import('./pages/ProfilePage').then(m => ({ default: m.ProfilePage })));
const JournalPage = lazy(() => import('./pages/JournalPage').then(m => ({ default: m.JournalPage })));
const MyMistakesPage = lazy(() => import('./pages/MyMistakesPage').then(m => ({ default: m.MyMistakesPage })));
const LevelExamPage = lazy(() => import('./pages/LevelExamPage').then(m => ({ default: m.LevelExamPage })));
const NotificationSettingsPage = lazy(() => import('./pages/NotificationSettingsPage').then(m => ({ default: m.NotificationSettingsPage })));

import { MobileTabBar } from './components/MobileTabBar';
import { ReloadPrompt } from './components/ReloadPrompt';
import { InstallPromptBanner } from './components/InstallPromptBanner';
import { OfflineBanner } from './components/OfflineBanner';

const RouteLoadingSpinner = () => (
  <div className="min-h-[60vh] flex flex-col items-center justify-center gap-3">
    <Compass className="w-9 h-9 text-[#1B4D3E] animate-spin" />
    <span className="text-xs font-bold text-stone-500">جاري التحميل...</span>
  </div>
);

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: 1,
    },
  },
});

export const App: React.FC = () => {
  return (
    <QueryClientProvider client={queryClient}>
      <AuthProvider>
        <BrowserRouter>
          <Suspense fallback={<RouteLoadingSpinner />}>
            <Routes>
              {/* Public Routes */}
              <Route path="/" element={<LandingPage />} />
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
            <Route path="/forgot-password" element={<ForgotPasswordPage />} />
            <Route path="/reset-password" element={<ResetPasswordPage />} />

            {/* Semi-protected / Auth-required Routes */}
            <Route
              path="/onboarding"
              element={
                <ProtectedRoute>
                  <OnboardingPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/placement-test"
              element={
                <ProtectedRoute>
                  <PlacementTestPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/placement-test/results"
              element={
                <ProtectedRoute>
                  <PlacementResultsPage />
                </ProtectedRoute>
              }
            />

            {/* Core Protected Learner Routes */}
            <Route
              path="/dashboard"
              element={
                <ProtectedRoute>
                  <DashboardPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/learn"
              element={
                <ProtectedRoute>
                  <LearnPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/learn/lessons/:lessonId"
              element={
                <ProtectedRoute>
                  <LessonPlayerPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/levels/:levelCode/exam"
              element={
                <ProtectedRoute>
                  <LevelExamPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/stories"
              element={
                <ProtectedRoute>
                  <StoriesPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/stories/:storyId"
              element={
                <ProtectedRoute>
                  <StoryReaderPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/review"
              element={
                <ProtectedRoute>
                  <VocabularyReviewPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/speak"
              element={
                <ProtectedRoute>
                  <SpeakingTutorPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/journal"
              element={
                <ProtectedRoute>
                  <JournalPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/my-mistakes"
              element={
                <ProtectedRoute>
                  <MyMistakesPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/progress"
              element={
                <ProtectedRoute>
                  <ProgressPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/profile"
              element={
                <ProtectedRoute>
                  <ProfilePage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/settings"
              element={
                <ProtectedRoute>
                  <ProfilePage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/settings/notifications"
              element={
                <ProtectedRoute>
                  <NotificationSettingsPage />
                </ProtectedRoute>
              }
            />

            {/* Fallback */}
            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
        </Suspense>
        <MobileTabBar />
          <OfflineBanner />
          <InstallPromptBanner />
          <ReloadPrompt />
        </BrowserRouter>
      </AuthProvider>
    </QueryClientProvider>
  );
};

export default App;
