import { apiClient } from './client';

export interface Plan {
  id: number;
  slug: 'free' | 'pro_monthly' | 'pro_annual' | string;
  name_ar: string;
  name_en: string;
  description_ar: string;
  description_en: string;
  price_egp: number;
  price_usd: number;
  billing_cycle: 'free' | 'monthly' | 'annual' | 'lifetime';
  features_ar: string[];
  features_en: string[];
  is_active: boolean;
  is_featured: boolean;
  sort_order: number;
}

export interface UserSubscription {
  has_subscription: boolean;
  is_pro: boolean;
  status: 'free' | 'active' | 'trialing' | 'canceled' | 'past_due' | 'expired';
  plan: Plan | null;
  billing_cycle?: string;
  currency?: string;
  amount_paid?: number;
  payment_method?: string;
  current_period_starts_at?: string;
  current_period_ends_at?: string;
  days_remaining?: number;
  canceled_at?: string;
  can_cancel?: boolean;
}

export const subscriptionApi = {
  getPlans: () =>
    apiClient<{ plans: Plan[] }>('/subscription/plans'),

  getMySubscription: () =>
    apiClient<UserSubscription>('/subscription/my-subscription'),

  checkout: (data: {
    plan_slug: string;
    payment_method?: string;
    currency?: 'EGP' | 'USD' | 'SAR';
  }) =>
    apiClient<{
      success: boolean;
      message_ar: string;
      message_en: string;
      subscription: UserSubscription;
    }>('/subscription/checkout', {
      method: 'POST',
      body: JSON.stringify(data),
    }),

  cancel: () =>
    apiClient<{
      success: boolean;
      message_ar: string;
      subscription: UserSubscription;
    }>('/subscription/cancel', {
      method: 'POST',
    }),
};
