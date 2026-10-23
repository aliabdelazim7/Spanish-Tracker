# Automated Testing & Quality Assurance

## 1. Backend Automated Testing

The backend suite tests critical user workflows, calculation integrity, authorization checks, and gamification logic using PHPUnit / Laravel Test Runner:

```bash
cd services/api
php artisan test
```

### Test Coverage Highlights

- **`Tests\Feature\AuthTest`**:
  - `test_user_can_register_and_receives_token`: Verifies registration, profile creation, and Sanctum token issuance.
  - `test_user_can_login_with_valid_credentials`: Verifies credential verification and token return.
  - `test_login_fails_with_invalid_credentials`: Verifies secure 422 error handling.
  - `test_authenticated_user_can_fetch_profile`: Verifies `/auth/me` with profile relations.
  - `test_unauthenticated_request_is_rejected`: Verifies 401 response on protected routes.

- **`Tests\Feature\PlacementEngineTest`**:
  - `test_user_can_start_placement_test`: Verifies question list generation with hidden answers.
  - `test_user_can_submit_answer_and_finish_test`: Verifies answer submission, skill breakdown evaluation, level estimation, and recommendation.

- **`Tests\Feature\LessonProgressionTest`**:
  - `test_user_can_start_lesson_and_answer_exercise`: Verifies multi-exercise validation, lesson completion, XP award, and duplicate completion prevention.

- **`Tests\Feature\VocabularySrsTest`**:
  - `test_user_can_save_and_review_vocabulary_with_sm2`: Verifies SM-2 algorithm transitions (rating 5 advances interval from 1 to 6, rating 1 resets interval to 1).

- **`Tests\Feature\AiTutorTest`**:
  - `test_ai_tutor_explain_concept`: Verifies grammar explanations and audit logging.
  - `test_ai_tutor_corrects_user_text`: Verifies grammatical analysis of Spanish text.

---

## 2. Frontend Production Verification

The React/TypeScript frontend compiles cleanly with zero TypeScript errors:

```bash
cd apps/web
npm run build
```

This verifies type integrity, JSX correctness, Tailwind CSS processing, and bundler chunk generation.
