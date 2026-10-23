<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('level_exam_attempts', function (Blueprint $table) {
            $table->integer('attempt_number')->default(1)->after('cefr_level');
            $table->json('recommended_lessons')->nullable()->after('feedback_ar');
        });
    }

    public function down(): void
    {
        Schema::table('level_exam_attempts', function (Blueprint $table) {
            $table->dropColumn(['attempt_number', 'recommended_lessons']);
        });
    }
};
