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

    // REMINDER-05
    // 期日の3日前・当日・3日経過後で、通知タイミングに応じた本文が作成される
    public function test_reminder_body_matches_timing(): void
    {
        Carbon::setTestNow('2026-08-18 20:00:00');
        Notification::fake();

        $user = User::factory()->create();

        $threeDaysBeforeBook = Book::factory()->create([
            'title' => '3日前の書籍',
        ]);

        $dueDateBook = Book::factory()->create([
            'title' => '当日の書籍',
        ]);

        $threeDaysAfterBook = Book::factory()->create([
            'title' => '3日経過後の書籍',
        ]);

        $threeDaysBeforePlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $threeDaysBeforeBook->id,
            'target_date' => '2026-08-21',
            'status' => ReadingPlanStatus::Pending,
        ]);

        $dueDatePlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $dueDateBook->id,
            'target_date' => '2026-08-18',
            'status' => ReadingPlanStatus::Pending,
        ]);

        $threeDaysAfterPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $threeDaysAfterBook->id,
            'target_date' => '2026-08-15',
            'status' => ReadingPlanStatus::Pending,
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function (ReadingPlanReminderNotification $notification) use ($user, $threeDaysBeforePlan) {
                $data = $notification->toArray($user);

                return $data['reading_plan_id'] === $threeDaysBeforePlan->id
                    && $data['timing'] === 'three_days_before';
            }
        );

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function (ReadingPlanReminderNotification $notification) use ($user, $dueDatePlan) {
                $data = $notification->toArray($user);

                return $data['reading_plan_id'] === $dueDatePlan->id
                    && $data['timing'] === 'on_due_date';
            }
        );

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function (ReadingPlanReminderNotification $notification) use ($user, $threeDaysAfterPlan) {
                $data = $notification->toArray($user);

                return $data['reading_plan_id'] === $threeDaysAfterPlan->id
                    && $data['timing'] === 'three_days_after';
            }
        );
    }

    // REMINDER-06
    // 複数ユーザーに対象となる読書計画が存在する場合、各ユーザーに通知される
    public function test_reminders_are_created_for_multiple_users(): void
    {
        Carbon::setTestNow('2026-08-18 20:00:00');
        Notification::fake();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $readingPlan1 = ReadingPlan::factory()->create([
            'user_id' => $user1->id,
            'book_id' => $book1->id,
            'target_date' => '2026-08-21',
            'status' => ReadingPlanStatus::Pending,
        ]);

        $readingPlan2 = ReadingPlan::factory()->create([
            'user_id' => $user2->id,
            'book_id' => $book2->id,
            'target_date' => '2026-08-21',
            'status' => ReadingPlanStatus::Pending,
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        Notification::assertSentTo(
            $user1,
            ReadingPlanReminderNotification::class,
            function (ReadingPlanReminderNotification $notification) use ($user1, $readingPlan1) {
                $data = $notification->toArray($user1);

                return $data['reading_plan_id'] === $readingPlan1->id
                    && $data['timing'] === 'three_days_before';
            }
        );

        Notification::assertSentTo(
            $user2,
            ReadingPlanReminderNotification::class,
            function (ReadingPlanReminderNotification $notification) use ($user2, $readingPlan2) {
                $data = $notification->toArray($user2);

                return $data['reading_plan_id'] === $readingPlan2->id
                    && $data['timing'] === 'three_days_before';
            }
        );
    }

    // REMINDER-07
    // 読了済みの読書計画にはリマインダー通知を作成しない
    public function test_reminder_is_not_created_for_completed_reading_plan(): void
    {
        Carbon::setTestNow('2026-08-18 20:00:00');
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-08-21',
            'status' => ReadingPlanStatus::Completed,
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    // REMINDER-08
    // 3日前・当日・3日経過後のいずれにも該当しない読書計画には通知を作成しない
    public function test_reminder_is_not_created_when_timing_does_not_match(): void
    {
        Carbon::setTestNow('2026-08-18 20:00:00');
        Notification::fake();

        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-08-20',
            'status' => ReadingPlanStatus::Pending,
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }
}
