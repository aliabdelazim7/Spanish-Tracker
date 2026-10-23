# Deployment Guide

This guide covers deploying the **Frontend to Vercel** and the **Laravel Backend to a persistent PHP host (Render, Railway, Forge, or VPS)** with **Supabase PostgreSQL**.

---

## 1. Frontend Deployment (Vercel)

The React/Vite application in `apps/web` is optimized for static CDN edge deployment on Vercel.

### Vercel Project Settings
- **Root Directory**: `apps/web`
- **Framework Preset**: `Vite`
- **Build Command**: `npm run build`
- **Output Directory**: `dist`

### Environment Variables on Vercel
```env
VITE_API_URL=https://api.yourdomain.com/api/v1
```

### SPA Routing & Cache Header Handling
The included `apps/web/vercel.json` automatically guarantees that all client routes (`/dashboard`, `/learn`, `/stories`, etc.) rewrite to `/index.html` without 404 errors:

```json
{
  "rewrites": [{ "source": "/(.*)", "destination": "/index.html" }]
}
```

---

## 2. Backend Deployment (PHP / Laravel Host)

Deploy `services/api` to any modern PHP 8.2+ hosting platform (Render, Railway, Laravel Forge, Fly.io, or VPS).

### Prerequisites
- PHP 8.2+ with extensions: `pdo_pgsql`, `mbstring`, `openssl`, `curl`, `xml`, `bcmath`.
- Composer 2.x

### Deployment Commands
```bash
cd services/api
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

---

## 3. Supabase PostgreSQL Configuration

1. In your **Supabase Dashboard**, navigate to **Project Settings \(\to\) Database**.
2. Copy the **Connection Pooling Connection String** (Transaction mode, port `6543`).
3. Set the following environment variables in your backend hosting environment:

```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-[region].pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.[project-ref]
DB_PASSWORD=[your-secure-supabase-password]
DB_SSLMODE=require
```

---

## 4. CORS & Sanctum Configuration

In `services/api/.env`:

```env
APP_URL=https://api.yourdomain.com
FRONTEND_URL=https://your-app.vercel.app
SANCTUM_STATEFUL_DOMAINS=your-app.vercel.app
```

The application's `config/cors.php` is pre-configured to allow requests from your frontend origin while exposing necessary authorization headers.
