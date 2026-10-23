# API Documentation

All endpoints are versioned under the `/api/v1` namespace.

---

## Authentication & Onboarding

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/api/v1/auth/register` | No | Registers new user, creates profile, issues Sanctum token |
| `POST` | `/api/v1/auth/login` | No | Validates credentials, issues Sanctum token |
| `GET` | `/api/v1/auth/me` | Sanctum | Retrieves authenticated user profile & stats |
| `POST` | `/api/v1/auth/logout` | Sanctum | Revokes active Sanctum token |
| `POST` | `/api/v1/auth/forgot-password` | No | Dispatches password reset notification safely |
| `GET` | `/api/v1/onboarding` | Sanctum | Retrieves onboarding profile status |
| `PATCH` | `/api/v1/onboarding` | Sanctum | Updates reason, daily goal, dialect, variant |

---

## Placement Testing Engine

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/api/v1/placement-tests/start` | Sanctum | Starts or resumes diagnostic test; hides answers |
| `GET` | `/api/v1/placement-tests/history` | Sanctum | Returns previous attempts |
| `GET` | `/api/v1/placement-tests/{id}` | Sanctum | Retrieves attempt details |
| `POST` | `/api/v1/placement-tests/{id}/answers` | Sanctum | Submits answer, evaluates points on backend |
| `POST` | `/api/v1/placement-tests/{id}/finish` | Sanctum | Finalizes attempt, evaluates CEFR level, awards XP |

---

## Curriculum & Lessons

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `GET` | `/api/v1/learning-path` | Sanctum | Full course hierarchy with completed lesson IDs |
| `GET` | `/api/v1/levels` | No | Published CEFR levels summary |
| `GET` | `/api/v1/units/{unitId}` | Sanctum | Unit details with lessons |
| `GET` | `/api/v1/lessons/{lessonId}` | Sanctum | Lesson details & exercises |
| `POST` | `/api/v1/lessons/{id}/start` | Sanctum | Starts / resumes lesson session |
| `POST` | `/api/v1/lessons/{id}/exercises/{exId}/answer` | Sanctum | Validates exercise answer on backend |
| `POST` | `/api/v1/lessons/{id}/complete` | Sanctum | Evaluates score %, awards XP (idempotent) |
| `GET` | `/api/v1/lessons/{id}/progress` | Sanctum | Retrieves latest attempt progress |

---

## Stories & Reading

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `GET` | `/api/v1/stories` | Sanctum | Lists stories with user completion state |
| `GET` | `/api/v1/stories/{id}` | Sanctum | Returns story with ordered segments & questions |
| `POST` | `/api/v1/stories/{id}/progress` | Sanctum | Updates current reading segment index |
| `POST` | `/api/v1/stories/{id}/complete` | Sanctum | Marks story complete & awards XP |

---

## Vocabulary & Spaced Repetition (SM-2)

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `GET` | `/api/v1/vocabulary` | Sanctum | Paginated user words + available catalog |
| `GET` | `/api/v1/vocabulary/reviews/due` | Sanctum | Words scheduled for review today |
| `POST` | `/api/v1/vocabulary/{id}/save` | Sanctum | Adds word to user revision queue |
| `DELETE` | `/api/v1/vocabulary/{id}/save` | Sanctum | Removes word from revision queue |
| `POST` | `/api/v1/vocabulary/{id}/review` | Sanctum | Evaluates SM-2 rating (0-5), updates interval |

---

## AI Tutor & Speaking Practice

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/api/v1/tutor/explain` | Sanctum | Explains grammar in Egyptian / Standard Arabic |
| `POST` | `/api/v1/tutor/correct` | Sanctum | Corrects user Spanish and highlights errors |
| `POST` | `/api/v1/tutor/practice` | Sanctum | Generates level-appropriate practice questions |
| `POST` | `/api/v1/tutor/speaking-feedback` | Sanctum | Analyzes spoken transcript against target |

---

## Dashboard & Analytics

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `GET` | `/api/v1/dashboard` | Sanctum | User stats, streak, next lesson recommendation |
| `GET` | `/api/v1/progress` | Sanctum | Skill breakdown percentages and recent XP |
| `GET` | `/api/v1/achievements` | Sanctum | All badges with user unlock timestamps |

---

## Spanish Journal & Writing Practice

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `GET` | `/api/v1/journal` | Sanctum | Lists user's journal entries (with filters & word count) |
| `POST` | `/api/v1/journal` | Sanctum | Creates a journal entry or draft |
| `GET` | `/api/v1/journal/prompts` | Sanctum | Returns level-appropriate Spanish writing prompts |
| `GET` | `/api/v1/journal/{id}` | Sanctum | Fetches entry with writing analysis & suggestions |
| `PUT` | `/api/v1/journal/{id}` | Sanctum | Updates journal entry (original draft or title) |
| `DELETE` | `/api/v1/journal/{id}` | Sanctum | Deletes a journal entry and its analysis |
| `POST` | `/api/v1/journal/{id}/analyze` | Sanctum | Triggers AI writing analysis without mutating original text |

---

## Learner Error Profile & My Mistakes

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `GET` | `/api/v1/my-mistakes` | Sanctum | Retrieves active weak concepts, mastery scores & explainable recommendations |
| `POST` | `/api/v1/my-mistakes/practice` | Sanctum | Submits targeted practice quiz answers, updates mastery score & awards XP |
