const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';

export class ApiError extends Error {
  status: number;
  data: any;

  constructor(status: number, message: string, data?: any) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.data = data;
  }
}

export async function apiClient<T>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const token = localStorage.getItem('spanish_tracker_token');

  const headers: Record<string, string> = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    ...(options.headers as Record<string, string>),
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const url = endpoint.startsWith('http') ? endpoint : `${API_BASE_URL}${endpoint.startsWith('/') ? '' : '/'}${endpoint}`;

  try {
    const response = await fetch(url, {
      ...options,
      headers,
    });

    if (response.status === 401) {
      // Clear token if expired or invalid
      localStorage.removeItem('spanish_tracker_token');
    }

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      let message = data.message || `حدث خطأ أثناء الاتصال بالخادم (${response.status})`;
      if (data.errors) {
        // Collect first validation message
        const firstErr = Object.values(data.errors)[0];
        if (Array.isArray(firstErr) && firstErr.length > 0) {
          message = firstErr[0];
        }
      }
      throw new ApiError(response.status, message, data);
    }

    return data as T;
  } catch (error: any) {
    if (error instanceof ApiError) {
      throw error;
    }
    throw new ApiError(500, error.message || 'تعذر الاتصال بالخادم. يرجى التحقق من اتصال الإنترنت.');
  }
}
