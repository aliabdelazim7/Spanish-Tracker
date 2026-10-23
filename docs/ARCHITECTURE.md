# Architecture & System Design

## 1. High-Level Overview

**Ruta Español** is an educational technology (EdTech) platform designed to teach Castilian Spanish (`es-ES`) to Arabic-speaking learners, especially Egyptian users, structured around the Common European Framework of Reference for Languages (CEFR) from levels **A1 to C2**.

The platform is organized as a clean, decoupled monorepo:

```
/
├── apps/
│   └── web/            # Single-Page Application (React 19, TypeScript, Vite, Tailwind CSS, TanStack Query)
├── services/
│   └── api/            # REST API Service (PHP 8.4+, Laravel 12, Sanctum, PostgreSQL / SQLite)
├── docs/               # System architecture, database, API, deployment, security guides
├── package.json        # Root monorepo orchestration scripts
└── README.md           # Quickstart and project summary
```

---

## 2. Core Architecture Decisions

### 2.1 Backend Authority & Authentication (Laravel Sanctum)
- **Authority**: Laravel manages user identities, authorization policies, session tokens, rate limits, and learning progress.
- **Tokens**: Laravel Sanctum issues cryptographically signed Bearer tokens (or HttpOnly cookies if deployed on a unified subdomain).
- **Security**: The frontend client never stores long-lived secrets or external service API keys. All state modifications (answers, XP awards, streak updates) are validated and computed strictly on the backend.

### 2.2 Database Strategy (Supabase PostgreSQL + SQLite Dev Fallback)
- **Production Target**: Supabase PostgreSQL (utilizing pooled connections on port `6543` for serverless or PHP host environments).
- **Local/Test Fallback**: Standard SQLite driver enabled out-of-the-box in `.env.example` so developers and automated CI pipelines run with zero configuration overhead and 100% ANSI-SQL migration compatibility.

### 2.3 Provider-Independent AI Architecture
All AI capabilities are separated behind strict interfaces in `App\Services\Ai\LanguageTutorProviderInterface`:
- `explainGrammar(string $concept, string $dialect)`
- `correctText(string $userSpanishText, ?string $context)`
- `generatePractice(string $topic, string $level)`
- `evaluateSpeaking(string $transcript, string $targetPhrase)`

A fully functional, educational `MockLanguageTutorProvider` serves rich Egyptian Arabic grammatical explanations, pronunciation comparisons, and corrections locally without incurring external API costs or failing when credentials are absent.

### 2.4 Spaced Repetition (SuperMemo SM-2)
Vocabulary learning utilizes the scientific SuperMemo SM-2 algorithm (`SpacedRepetitionService`). Each review record calculates:
- Ease Factor (\(EF \ge 1.30\))
- Repetition counter
- Interval days (1 day \(\to\) 6 days \(\to\) \(I \times EF\))
- Resets on failed recall (rating \(< 3\)) to guarantee retention.

### 2.5 Gamification & Idempotency
- XP rewards are governed by `GamificationService` using database transactions and unique `idempotency_key` records (`sourceType_sourceId_userId`).
- Duplicate submissions of already-completed lessons or stories can never award duplicate XP.
- Streaks are computed based on the user's local timezone (`Africa/Cairo` by default), rewarding genuine consecutive daily study.

### 2.6 Spanish Journal & Non-Destructive AI Writing Analysis
- Learners write essays or journal entries prompted by their daily activities or CEFR level prompts.
- **Strict Invariant**: The user's original text (`journal_entries.content`) is immutable during analysis. Corrected text, suggestions, and feedback are stored separately in `writing_analyses` and `writing_suggestions`.
- Side-by-side visual diffs highlight errors in context with explanations in Egyptian/Standard Arabic.

### 2.7 Learner Error Profile & Explainable Recommendation Engine
- Recurrent mistakes from writing and lessons feed into `learner_error_concepts`.
- When an error occurs repeatedly (e.g. `ser-vs-estar`, `por-vs-para`), the system generates a `learning_recommendations` entry explaining *why* the learner received this recommendation.
- Learners can launch targeted quizzes directly from the "My Mistakes" screen (`/my-mistakes`) to strengthen weak rules and increase mastery scores.

### 2.8 Multilingual Architecture (i18next with RTL/LTR Support)
- Built on `i18next` and `react-i18next` with language persistence (`ar` default, `en` optional).
- Dynamically sets document direction (`document.documentElement.dir = 'rtl' | 'ltr'`) and adjusts typography and margins automatically across the UI.

### 2.9 Castilian Spanish Audio & Speech Synthesis
- Native Web Speech API integration calibrated for `es-ES` (Castilian Spanish) with fallback to natural browser speech voices.
- Interactive hover and click audio triggers on every vocabulary item, lesson exercise, and story segment.
- Real-time speech recognition for speaking practice with phonetic distance scoring and Egyptian Arabic pronunciation feedback.

### 2.10 Responsive Mobile & Tablet Layout
- Adaptive shell with persistent bottom navigation (`MobileTabBar`) on small screens (<768px).
- Fixed desktop sidebar hidden seamlessly on mobile, with touch-optimized targets (minimum 44x44px) and safe-area padding.
