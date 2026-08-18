<?php

namespace Tests\Feature\Book;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanDateTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // 読書計画（期限変更）
    // =========================

    // PLAN-DATE-01
    public function test_authenticated_user_can_view_reading_plan_edit_form(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => '編集対象の書籍',
        ]);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Pending,
            'target_date' => '2026-08-25',
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.edit', $readingPlan));

        $response->assertStatus(200);

        // 対象書籍名
        $response->assertSee($book->title);

        // 現在の状態
        $response->assertSee(
            ReadingPlanStatus::Pending->label()
        );

        // 現在の期日
        $response->assertSee('2026-08-25');
    }

    // PLAN-DATE-02
    public function test_authenticated_user_can_update_reading_plan_date(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => '2026-08-25',
        ]);

        $newTargetDate = '2026-09-01';

        $response = $this->actingAs($user)
            ->put(route('reading-plans.update', $readingPlan), [
                'target_date' => $newTargetDate,
            ]);

        $response->assertRedirect(route('reading-plans.index'));

        $response->assertSessionHas(
            'success',
            '読書計画を更新しました'
        );

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => $newTargetDate,
        ]);
    }

    // PLAN-DATE-03
    public function test_cannot_update_reading_plan_with_past_date(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => '2026-08-25',
        ]);

        $response = $this->actingAs($user)
            ->put(route('reading-plans.update', $readingPlan), [
                'target_date' => '2026-08-17',
            ]);

        $response->assertSessionHasErrors([
            'target_date' => '期日は本日以降の日付を入力してください',
        ]);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => '2026-08-25',
        ]);
    }

    // PLAN-DATE-04
    public function test_user_cannot_edit_another_users_reading_plan(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.edit', $readingPlan));

        $response->assertForbidden();
    }
}
