import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { AuthProvider } from './context/AuthContext';
import { ProtectedRoute } from './components/ProtectedRoute';

import { LandingPage } from './pages/LandingPage';
import { LoginPage } from './pages/LoginPage';
import { RegisterPage } from './pages/RegisterPage';
import { ForgotPasswordPage } from './pages/ForgotPasswordPage';
import { OnboardingPage } from './pages/OnboardingPage';
import { PlacementTestPage } from './pages/PlacementTestPage';
import { PlacementResultsPage } from './pages/PlacementResultsPage';
import { DashboardPage } from './pages/DashboardPage';
import { LearnPage } from './pages/LearnPage';
import { LessonPlayerPage } from './pages/LessonPlayerPage';
import { StoriesPage } from './pages/StoriesPage';
import { StoryReaderPage } from './pages/StoryReaderPage';
import { VocabularyReviewPage } from './pages/VocabularyReviewPage';
import { SpeakingTutorPage } from './pages/SpeakingTutorPage';
import { ProgressPage } from './pages/ProgressPage';
import { ProfilePage } from './pages/ProfilePage';
import { JournalPage } from './pages/JournalPage';
import { MyMistakesPage } from './pages/MyMistakesPage';
import { MobileTabBar } from './components/MobileTabBar';

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
          <Routes>
            {/* Public Routes */}
            <Route path="/" element={<LandingPage />} />
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
            <Route path="/forgot-password" element={<ForgotPasswordPage />} />

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

            {/* Fallback */}
            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
          <MobileTabBar />
        </BrowserRouter>
      </AuthProvider>
    </QueryClientProvider>
  );
};

export default App;
