<?php

namespace Tests\Feature\Console;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendReadingPlanRemindersTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // =========================
    // リマインダーバッチ
    // =========================

    // REMINDER-01
    public function test_reminder_is_created_three_days_before_target_date(): void
    {
        Carbon::setTestNow('2026-08-18 20:00:00');

        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create([
            'title' => 'リマインダー対象書籍',
        ]);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-08-21',
            'status' => ReadingPlanStatus::Pending,
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function (ReadingPlanReminderNotification $notification) use ($user, $readingPlan) {
                $data = $notification->toArray($user);

                return $data['reading_plan_id'] === $readingPlan->id
                    && $data['timing'] === 'three_days_before';
            }
        );
    }

    // REMINDER-02
    public function test_reminder_is_created_on_target_date(): void
    {
        Carbon::setTestNow('2026-08-18 20:00:00');

        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create([
            'title' => '当日リマインダー対象書籍',
        ]);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-08-18',
            'status' => ReadingPlanStatus::Pending,
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function (ReadingPlanReminderNotification $notification) use ($user, $readingPlan) {
                $data = $notification->toArray($user);

                return $data['reading_plan_id'] === $readingPlan->id
                    && $data['timing'] === 'on_due_date';
            }
        );
    }

    // REMINDER-03
    public function test_reminder_is_created_three_days_after_target_date(): void
    {
        Carbon::setTestNow('2026-08-18 20:00:00');

        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create([
            'title' => '3日経過リマインダー対象書籍',
        ]);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-08-15',
            'status' => ReadingPlanStatus::Pending,
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function (ReadingPlanReminderNotification $notification) use ($user, $readingPlan) {
                $data = $notification->toArray($user);

                return $data['reading_plan_id'] === $readingPlan->id
                    && $data['timing'] === 'three_days_after';
            }
        );
    }

    // REMINDER-04
    public function test_duplicate_reminder_is_not_created(): void
    {
        Carbon::setTestNow('2026-08-18 20:00:00');

        $user = User::factory()->create();
        $book = Book::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-08-21',
            'status' => ReadingPlanStatus::Pending,
        ]);

        $user->notify(
            new ReadingPlanReminderNotification(
                $readingPlan,
                'three_days_before'
            )
        );

        $this->assertDatabaseCount('notifications', 1);

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        $this->assertDatabaseCount('notifications', 1);
    }
}
