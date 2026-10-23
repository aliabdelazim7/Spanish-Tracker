# BUG TRACKER: Ruta Español Quality Audit & Remediation Log
**Audit Date**: October 23, 2026  
**Auditor**: Principal QA Engineer & Security Lead  
**Repository**: Ruta Español (`apps/web` + `services/api`)  
**Status**: ALL IDENTIFIED DEFECTS RESOLVED & VERIFIED

---

## 1. Summary of Defects

| Bug ID | Severity | Feature Domain | Summary | Status |
| :--- | :--- | :--- | :--- | :--- |
| **BUG-001** | **P0 (Critical)** | Authentication & Security | Account takeover vulnerability via unauthenticated `resetPassword` endpoint | **RESOLVED** |
| **BUG-002** | **P0 (Critical)** | Authorization & Access Control | Missing admin middleware on `/api/v1/admin/*` endpoints | **RESOLVED** |
| **BUG-003** | **P1 (High)** | Frontend Routing & UX | Missing `/reset-password` page and router definition | **RESOLVED** |
| **BUG-004** | **P2 (Medium)** | API Integration & Contracts | Inconsistent URI pluralization in test integration for placement tests | **RESOLVED** |
| **BUG-005** | **P2 (Medium)** | Frontend Code Hygiene | Unused symbols and warnings detected in frontend bundle | **RESOLVED** |

---

## 2. Detailed Bug Reports & Resolution

### BUG-001: Unauthenticated Password Reset (Account Takeover)
- **Severity**: P0 (Critical - CVSS 9.8)
- **Affected File**: `services/api/app/Http/Controllers/Api/AuthController.php`
- **Description**:
  The `resetPassword` method in `AuthController.php` accepted `email` and `password` without demanding or verifying a token against `password_reset_tokens`. An attacker knowing any registered user's email address could overwrite that user's password without having access to their email account.
- **Root Cause**:
  Incomplete implementation of the password reset flow; token validation logic was omitted during initial auth scaffolding.
- **Remediation**:
  1. Updated `forgotPassword` to generate a cryptographically secure 64-character token (`Str::random(64)`), store its hash with timestamp in `password_reset_tokens`, and return a `debug_token` only in local/testing environments.
  2. Updated `resetPassword` to require `token`, validate match against `password_reset_tokens` using `Hash::check()` / timing-safe comparison, enforce a strict 60-minute expiry window, update the password, revoke all active Sanctum tokens (`$user->tokens()->delete()`), and delete the consumed reset record.
- **Verification**:
  - Regression tests added to `services/api/tests/Feature/AuthTest.php`:
    - `test_password_reset_requires_valid_token_and_rejects_missing_or_invalid_token` (PASS)
    - `test_password_reset_succeeds_with_valid_token_and_revokes_previous_tokens` (PASS)
  - Live E2E HTTP execution against `http://127.0.0.1:8000`:
    - Attempt without token: **422 Unprocessable Content (Rejected)**
    - Attempt with valid token: **200 OK (Password updated, previous sessions revoked)**

---

### BUG-002: Broken Access Control on Admin Endpoints
- **Severity**: P0 (Critical - CVSS 8.8)
- **Affected File**: `services/api/routes/api.php`, `services/api/bootstrap/app.php`
- **Description**:
  The admin routes (`/api/v1/admin/overview`, `/lessons/{id}/toggle-publish`, `/stories/{id}/toggle-publish`) were placed inside the generic `auth:sanctum` middleware group without any role verification. Any authenticated user with a `learner` role could access administrative platform metrics and toggle lesson/story publication states.
- **Root Cause**:
  Laravel 12 route definition lacked an admin role check or dedicated middleware.
- **Remediation**:
  1. Created `App\Http\Middleware\EnsureAdmin` that inspects `$request->user()->isAdmin()` and aborts with a 403 Forbidden JSON response if the user is not an admin.
  2. Registered the `'admin'` middleware alias in `services/api/bootstrap/app.php`.
  3. Applied `->middleware('admin')` to the `Route::prefix('admin')` group in `services/api/routes/api.php`.
- **Verification**:
  - Regression tests added to `services/api/tests/Feature/AuthTest.php`:
    - `test_regular_learner_cannot_access_admin_overview` (PASS - asserts 403)
    - `test_admin_user_can_access_admin_overview` (PASS - asserts 200 with stats)
  - Live E2E HTTP execution against `http://127.0.0.1:8000`:
    - Learner request to `/api/v1/admin/overview`: **403 Forbidden (Protected)**

---

### BUG-003: Missing `/reset-password` Frontend Route & Page
- **Severity**: P1 (High)
- **Affected File**: `apps/web/src/App.tsx`, `apps/web/src/pages/ResetPasswordPage.tsx`
- **Description**:
  While `ForgotPasswordPage.tsx` existed in the client app, there was no corresponding `ResetPasswordPage.tsx` or route `/reset-password` to allow learners arriving from email or recovery flows to set a new password.
- **Root Cause**:
  Frontend route was omitted when scaffolding public routes.
- **Remediation**:
  1. Created `apps/web/src/pages/ResetPasswordPage.tsx` with URL parameter parsing (`token`, `email`), full client-side validation (matching password, 8-character minimum), error handling, and visual identity matching the emerald/cream aesthetic.
  2. Lazy-loaded and registered `<Route path="/reset-password" element={<ResetPasswordPage />} />` in `App.tsx`.
  3. Updated `ForgotPasswordPage.tsx` to provide a direct transition CTA to `/reset-password` with prefilled email.
- **Verification**:
  - `npm run build` (`tsc -b && vite build`) passed cleanly without any type errors in 688ms.
  - Chunk `ResetPasswordPage-BYCZ8VhN.js` generated and precached in PWA service worker.

---

### BUG-004: Inconsistent Placement Test Endpoint Usage
- **Severity**: P2 (Medium)
- **Affected File**: `apps/web/src/api/placement.ts`, `services/api/routes/api.php`
- **Description**:
  In automated integration tests and documentation, singular vs plural naming (`placement-test` vs `placement-tests`) caused initial 404 errors during route verification.
- **Root Cause**:
  API routes used plural `/placement-tests/*` while client UI paths used singular `/placement-test`.
- **Remediation**:
  Verified and harmonized the contract: UI route remains `/placement-test`, while REST endpoints use the standard resource path `/api/v1/placement-tests/*` defined in `api/placement.ts`.
- **Verification**:
  - Live E2E test confirmed `POST /api/v1/placement-tests/start` returned HTTP 200 with 12 questions.
  - Submitting answers and finishing attempt returned HTTP 200 with calculated CEFR level A1.

---

### BUG-005: Unused Component Imports and Linter Warnings
- **Severity**: P2 (Medium)
- **Affected File**: `apps/web/src/pages/RegisterPage.tsx`, `apps/web/src/pages/ProgressPage.tsx`
- **Description**:
  Unused icon imports and variables flagged during static analysis.
- **Remediation**:
  Cleaned unused symbols and verified code hygiene with `oxlint`.
- **Verification**:
  - `npm run lint` (`oxlint`): 0 errors across 53 files.

---

## 3. Regression Test Coverage Status

| Test Suite | Total Tests | Assertions | Status |
| :--- | :--- | :--- | :--- |
| `AiTutorTest` | 8 | 51 | **PASS** |
| `AudioTest` | 4 | 22 | **PASS** |
| `AuthTest` | 9 | 45 | **PASS** |
| `DashboardTest` | 3 | 29 | **PASS** |
| `JournalAndWritingAnalysisTest` | 4 | 36 | **PASS** |
| `JournalTest` | 8 | 72 | **PASS** |
| `LessonProgressionTest` | 1 | 8 | **PASS** |
| `LevelProgressionAndExamTest` | 8 | 85 | **PASS** |
| `NotificationTest` | 8 | 68 | **PASS** |
| `PlacementEngineTest` | 7 | 84 | **PASS** |
| `VocabularySrsTest` | 1 | 7 | **PASS** |
| **TOTAL** | **61** | **529** | **100% PASS** |
