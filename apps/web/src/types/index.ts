export interface User {
  id: number;
  name: string;
  email: string;
  role: 'learner' | 'admin';
  profile?: UserProfile;
}

export interface UserProfile {
  id: number;
  user_id: number;
  learning_reason: 'travel' | 'career' | 'education' | 'culture' | 'personal';
  daily_goal_minutes: number;
  experience_level: 'zero' | 'basics' | 'intermediate';
  explanation_dialect: 'egyptian_arabic' | 'msa';
  target_variant: 'spain' | 'latin_america';
  estimated_cefr_level: string | null;
  max_unlocked_level?: string;
  completed_levels?: string[];
  onboarding_completed: boolean;
  total_xp: number;
  current_streak: number;
  longest_streak: number;
  last_activity_date: string | null;
  timezone: string;
}

export interface Course {
  id: number;
  slug: string;
  title: string;
  title_ar: string;
  description_ar: string;
  levels?: CefrLevel[];
}

export interface CefrLevel {
  id: number;
  course_id: number;
  code: string;
  name: string;
  name_ar: string;
  description_ar: string;
  order_index: number;
  units?: Unit[];
  is_unlocked?: boolean;
  is_completed?: boolean;
  total_lessons_count?: number;
  completed_lessons_count?: number;
  progress_percent?: number;
  can_take_exam?: boolean;
}

export interface Unit {
  id: number;
  cefr_level_id: number;
  title: string;
  title_ar: string;
  description_ar: string;
  icon: string | null;
  skill_category?: string;
  order_index: number;
  lessons?: Lesson[];
  level?: CefrLevel;
}

export interface Lesson {
  id: number;
  unit_id: number;
  title: string;
  title_ar: string;
  objective_ar: string;
  grammar_notes_ar: string | null;
  estimated_minutes: number;
  xp_reward: number;
  order_index: number;
  exercises?: LessonExercise[];
  unit?: Unit;
}

export interface LessonExercise {
  id: number;
  lesson_id: number;
  exercise_type: 'multiple_choice' | 'translation' | 'fill_blank' | 'word_order' | 'matching_pairs' | 'listening' | 'reading' | 'speaking';
  prompt: string;
  prompt_ar: string;
  target_phrase?: string | null;
  audio_url?: string | null;
  options?: any;
  correct_answers?: any;
  explanation_ar?: string | null;
  hint_ar?: string | null;
  xp_value: number;
  order_index: number;
}

export interface PlacementQuestion {
  id: number;
  skill_category: string;
  difficulty_level: string;
  question_type: string;
  question_text: string;
  context_text: string | null;
  prompt_ar: string;
  audio_url: string | null;
  options: string[];
  points: number;
}

export interface PlacementAttempt {
  id: number;
  user_id: number;
  status: 'in_progress' | 'completed';
  total_score: number;
  estimated_level: string | null;
  skill_breakdown: Record<string, { earned: number; total: number; percentage: number; correct: number; count: number }> | null;
  summary_report_ar: string | null;
  recommended_unit_id: number | null;
  recommended_unit?: Unit | null;
  started_at: string;
  completed_at: string | null;
}

export interface Story {
  id: number;
  cefr_level: string;
  title_es: string;
  title_ar: string;
  summary_ar: string;
  topic: string;
  estimated_minutes: number;
  difficulty: string;
  audio_url?: string | null;
  xp_reward: number;
  segments_count?: number;
  questions_count?: number;
  is_completed?: boolean;
  current_segment_index?: number;
  segments?: StorySegment[];
  questions?: StoryQuestion[];
}

export interface StorySegment {
  id: number;
  story_id: number;
  order_index: number;
  speaker_name: string | null;
  spanish_text: string;
  arabic_translation: string;
  explanation_ar: string | null;
  audio_url: string | null;
  vocabulary_highlights: string[] | null;
}

export interface StoryQuestion {
  id: number;
  story_id: number;
  after_segment_index: number;
  question_es: string;
  question_ar: string;
  options: string[];
  correct_answer: string;
  explanation_ar: string;
}

export interface VocabularyItem {
  id: number;
  spanish_word: string;
  phonetic: string | null;
  part_of_speech: string;
  gender: string | null;
  is_irregular: boolean;
  arabic_meaning: string;
  example_sentence_es: string;
  example_sentence_ar: string;
  audio_url: string | null;
  cefr_level: string;
  topic: string;
}

export interface UserVocabulary {
  id: number;
  user_id: number;
  vocabulary_item_id: number;
  mastery_level: 'learning' | 'reviewing' | 'mastered';
  repetitions: number;
  interval_days: number;
  ease_factor: number;
  next_review_at: string;
  last_reviewed_at: string | null;
  successful_reviews: number;
  failed_reviews: number;
  vocabulary_item?: VocabularyItem;
}

export interface Achievement {
  id: number;
  code: string;
  title_ar: string;
  description_ar: string;
  icon: string;
  badge_category: string;
  xp_reward: number;
  is_unlocked?: boolean;
  unlocked_at?: string | null;
}

export interface JournalEntry {
  id: number;
  user_id: number;
  title: string | null;
  content: string;
  prompt_topic: string | null;
  cefr_level: string;
  status: 'draft' | 'submitted' | 'analyzed';
  word_count: number;
  entry_date: string;
  created_at: string;
  updated_at: string;
  analysis?: WritingAnalysis;
}

export interface WritingAnalysis {
  id: number;
  journal_entry_id: number;
  user_id: number;
  corrected_content: string;
  overall_feedback_ar: string | null;
  overall_feedback_en: string | null;
  fluency_score: number;
  total_errors: number;
  total_suggestions: number;
  created_at: string;
  suggestions?: WritingSuggestion[];
}

export interface WritingSuggestion {
  id: number;
  writing_analysis_id: number;
  original_text: string;
  suggested_text: string;
  error_category: 'grammar' | 'verb_conjugation' | 'gender_agreement' | 'vocabulary' | 'spelling' | 'naturalness';
  severity: 'error' | 'suggestion';
  explanation_ar: string;
  explanation_en: string | null;
  example_sentence_es: string | null;
  related_concept_slug: string | null;
}

export interface LearnerErrorConcept {
  id: number;
  user_id: number;
  concept_slug: string;
  concept_name_ar: string;
  concept_name_en: string;
  category: string;
  total_errors_count: number;
  successful_practices_count: number;
  mastery_score: number;
  last_occurred_at: string | null;
  last_practiced_at: string | null;
}

export interface LearningRecommendation {
  id: number;
  user_id: number;
  title_ar: string;
  title_en: string;
  reason_ar: string;
  reason_en: string;
  action_type: string;
  action_payload: any;
  is_dismissed: boolean;
}

export interface LevelExamQuestion {
  id: number;
  skill_category: string;
  question_text: string;
  prompt_ar: string;
  audio_url: string | null;
  options: string[];
  points: number;
  order_index: number;
}

export interface LevelExamData {
  level: {
    code: string;
    name: string;
    name_ar: string;
  };
  total_lessons: number;
  completed_lessons: number;
  completion_percent: number;
  already_passed: boolean;
  questions: LevelExamQuestion[];
  pass_threshold_percent: number;
}

export interface LevelExamSubmitResponse {
  passed: boolean;
  score_percent: number;
  current_level: string;
  next_level: string | null;
  feedback_ar: string;
  xp_awarded: number;
  skill_breakdown: Record<string, { earned: number; total: number }>;
  detailed_results: Array<{
    question_id: number;
    question_text: string;
    user_answer: string;
    correct_answer: string;
    is_correct: boolean;
    explanation_ar?: string;
  }>;
  unlocked_levels: string[];
  max_unlocked_level: string;
}
