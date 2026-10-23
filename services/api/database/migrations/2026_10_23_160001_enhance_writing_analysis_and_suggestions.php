<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('writing_analyses', function (Blueprint $table) {
            $table->boolean('is_ai_generated')->default(false)->after('total_suggestions');
            $table->string('provider', 50)->default('local_rules_engine')->after('is_ai_generated');
        });

        Schema::table('writing_suggestions', function (Blueprint $table) {
            $table->text('natural_alternative')->nullable()->after('example_sentence_es');
            $table->boolean('is_understood')->default(false)->after('natural_alternative');
            $table->string('status', 20)->default('pending')->after('is_understood'); // pending, understood, review_later
            $table->text('user_notes')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('writing_analyses', function (Blueprint $table) {
            $table->dropColumn(['is_ai_generated', 'provider']);
        });

        Schema::table('writing_suggestions', function (Blueprint $table) {
            $table->dropColumn(['natural_alternative', 'is_understood', 'status', 'user_notes']);
        });
    }
};
