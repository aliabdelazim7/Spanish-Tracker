import { apiClient } from './client';

export interface NotificationPreferences {
  daily_reminder_enabled: boolean;
  review_due_enabled: boolean;
  streak_saver_enabled: boolean;
  new_content_enabled: boolean;
  assessment_results_enabled: boolean;
  preferred_reminder_time: string;
  timezone: string;
}

export interface VapidKeyResponse {
  public_key: string | null;
  is_configured: boolean;
}

export interface PushSubscriptionPayload {
  endpoint: string;
  keys: {
    p256dh: string;
    auth: string;
  };
  device_name?: string;
  content_encoding?: string;
}

export const notificationApi = {
  getVapidKey: () => apiClient<VapidKeyResponse>('/notifications/vapid-key'),

  getPreferences: () =>
    apiClient<{ preferences: NotificationPreferences; active_devices_count: number }>('/notifications/preferences'),

  updatePreferences: (preferences: Partial<NotificationPreferences>) =>
    apiClient<{ preferences: NotificationPreferences }>('/notifications/preferences', {
      method: 'PUT',
      body: JSON.stringify(preferences),
    }),

  subscribe: (payload: PushSubscriptionPayload) =>
    apiClient<{ message: string; subscription_id: number }>('/notifications/subscribe', {
      method: 'POST',
      body: JSON.stringify(payload),
    }),

  unsubscribe: (endpoint: string) =>
    apiClient<{ message: string; deleted: boolean }>('/notifications/unsubscribe', {
      method: 'POST',
      body: JSON.stringify({ endpoint }),
    }),

  sendTest: () =>
    apiClient<{ message: string; result: any }>('/notifications/test', {
      method: 'POST',
    }),
};
