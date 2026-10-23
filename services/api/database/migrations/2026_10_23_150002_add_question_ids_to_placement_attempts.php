<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('placement_attempts', function (Blueprint $table) {
            $table->json('question_ids')->nullable()->after('status');
            $table->text('academic_disclaimer_ar')->nullable()->after('summary_report_ar');
        });
    }

    public function down(): void
    {
        Schema::table('placement_attempts', function (Blueprint $table) {
            $table->dropColumn(['question_ids', 'academic_disclaimer_ar']);
        });
    }
};
