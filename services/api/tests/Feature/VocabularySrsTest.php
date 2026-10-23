<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VocabularyItem;
use Database\Seeders\VocabularySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VocabularySrsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(VocabularySeeder::class);
    }

    public function test_user_can_save_and_review_vocabulary_with_sm2(): void
    {
        $user = User::factory()->create();
        $user->profile()->create();

        $item = VocabularyItem::first();

        // 1. Save item
        $saveResp = $this->actingAs($user)->postJson("/api/v1/vocabulary/{$item->id}/save");
        $saveResp->assertStatus(200)
            ->assertJsonPath('user_vocabulary.vocabulary_item_id', $item->id);

        // 2. Perform 5-star review (successful recall)
        $reviewResp = $this->actingAs($user)->postJson("/api/v1/vocabulary/{$item->id}/review", [
            'quality_rating' => 5,
        ]);

        $reviewResp->assertStatus(200)
            ->assertJsonPath('result.interval_days', 1);

        // 3. Second review with 5 stars advances interval to 6
        $review2Resp = $this->actingAs($user)->postJson("/api/v1/vocabulary/{$item->id}/review", [
            'quality_rating' => 5,
        ]);

        $review2Resp->assertStatus(200)
            ->assertJsonPath('result.interval_days', 6);

        // 4. Failed review (rating 1) resets repetitions and interval to 1
        $failedResp = $this->actingAs($user)->postJson("/api/v1/vocabulary/{$item->id}/review", [
            'quality_rating' => 1,
        ]);

        $failedResp->assertStatus(200)
            ->assertJsonPath('result.interval_days', 1);
    }
}
