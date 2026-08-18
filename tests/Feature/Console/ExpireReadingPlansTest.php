<?php

namespace Tests\Feature\Console;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireReadingPlansTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // =========================
    // 自動失効バッチ
    // =========================

    // EXPIRE-01
    // 期日を過ぎても未読了の読書計画が存在する場合、
    // ステータスが「期限切れ」に更新される
    public function test_overdue_uncompleted_reading_plan_is_marked_as_expired(): void
    {
        Carbon::setTestNow('2026-08-18 20:00:00');

        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-08-17',
            'status' => ReadingPlanStatus::Pending,
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::Expired->value,
        ]);
    }

    // EXPIRE-02
    // 期日内または読了済みの読書計画が存在する場合、
    // ステータスは更新されない
    public function test_reading_plan_status_is_not_updated_when_not_expirable(): void
    {
        Carbon::setTestNow('2026-08-18 20:00:00');

        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 期日内の未着手
        $pendingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-08-19',
            'status' => ReadingPlanStatus::Pending,
        ]);

        // 期日を過ぎているが読了済み
        $completedPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-08-17',
            'status' => ReadingPlanStatus::Completed,
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $pendingPlan->id,
            'status' => ReadingPlanStatus::Pending->value,
        ]);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $completedPlan->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);
    }
}
