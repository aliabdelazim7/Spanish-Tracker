# Security & Data Protection Policies

Security is enforced at every layer of the Ruta Español platform.

---

## 1. Authentication & Token Management
- **Token Authority**: Laravel Sanctum acts as the single source of truth for user authentication and authorization.
- **Revocation**: Password resets and manual user logouts immediately revoke active tokens from the `personal_access_tokens` table.
- **Password Hashing**: Bcrypt with a minimum work factor of 12. Plaintext passwords are never logged or stored.

---

## 2. Authorization & Tenant Data Isolation
- Every protected endpoint inspects `request()->user()->id` to scope queries (`where('user_id', $user->id)`).
- A learner cannot view, modify, or submit progress for another learner's attempts, reviews, or story positions.
- Role-based authorization (`$user->role === 'admin'`) isolates administrative endpoints from learner access.

---

## 3. Server-Side Progress & XP Integrity
- **Anti-Tampering**: Lesson completion percentages, placement CEFR scores, and XP rewards are calculated strictly on the backend.
- **Duplicate Reward Prevention**: Database-level unique constraints and transactions with unique `idempotency_key` strings (`sourceType_sourceId_userId`) eliminate race conditions and repeated reward claiming.
- **Assessment Confidentiality**: Correct answers and pedagogical explanations are concealed from question lists and only revealed after an attempt answer is submitted to the backend.

---

## 4. AI & Audio Privacy
- **Zero Client Credential Leakage**: All AI vendor keys (`AI_API_KEY`) reside exclusively in backend environment variables.
- **Input Sanitization**: User inputs sent to the AI tutor undergo strict string length validation (`max:300` / `max:500`) to avoid token depletion attacks.
- **Audio Confidentiality**: Voice recordings are evaluated in-memory or through transient audio processing. Voice audio files are never permanently stored without explicit opt-in disclosure.
