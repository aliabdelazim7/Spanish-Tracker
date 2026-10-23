import { apiClient } from './client';
import { User } from '../types';

export interface AuthResponse {
  message: string;
  access_token: string;
  token_type: string;
  user: User;
}

export const authApi = {
  register: (payload: { name: string; email: string; password: string; password_confirmation: string }) =>
    apiClient<AuthResponse>('/auth/register', {
      method: 'POST',
      body: JSON.stringify(payload),
    }),

  login: (payload: { email: string; password: string }) =>
    apiClient<AuthResponse>('/auth/login', {
      method: 'POST',
      body: JSON.stringify(payload),
    }),

  me: () => apiClient<{ user: User }>('/auth/me'),

  logout: () =>
    apiClient<{ message: string }>('/auth/logout', {
      method: 'POST',
    }),

  updateOnboarding: (payload: {
    learning_reason: string;
    daily_goal_minutes: number;
    experience_level: string;
    explanation_dialect: string;
    target_variant: string;
  }) =>
    apiClient<{ message: string; profile: any }>('/onboarding', {
      method: 'PATCH',
      body: JSON.stringify(payload),
    }),
};
