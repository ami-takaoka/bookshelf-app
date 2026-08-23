<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // 通知
    // =========================

    // NOTIFICATION-01
    public function test_authenticated_user_can_view_own_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        DatabaseNotification::create([
            'id' => 'notification-1',
            'type' => 'App\Notifications\ReadingPlanReminderNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '読書リマインダー',
                'body' => '自分の通知です。',
                'timing' => 'on_due_date',
                'reading_plan_id' => 1,
            ],
        ]);

        DatabaseNotification::create([
            'id' => 'notification-2',
            'type' => 'App\Notifications\ReadingPlanReminderNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $otherUser->id,
            'data' => [
                'title' => '読書リマインダー',
                'body' => '他ユーザーの通知です。',
                'timing' => 'on_due_date',
                'reading_plan_id' => 2,
            ],
        ]);

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertSee('読書リマインダー')
            ->assertSee('自分の通知です。')
            ->assertDontSee('他ユーザーの通知です。');
    }

    // NOTIFICATION-02
    public function test_unread_notification_displays_unread_label_and_read_button(): void
    {
        $user = User::factory()->create();

        DatabaseNotification::create([
            'id' => 'notification-unread',
            'type' => 'App\Notifications\ReadingPlanReminderNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '読書リマインダー',
                'body' => '未読通知です。',
                'timing' => 'on_due_date',
                'reading_plan_id' => 1,
            ],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertSee('未読')
            ->assertSee('既読にする');
    }

    // NOTIFICATION-03
    public function test_notification_can_be_marked_as_read(): void
    {
        $user = User::factory()->create();

        $notification = DatabaseNotification::create([
            'id' => 'notification-read',
            'type' => 'App\Notifications\ReadingPlanReminderNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '読書リマインダー',
                'body' => '既読にする通知です。',
                'timing' => 'on_due_date',
                'reading_plan_id' => 1,
            ],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('notifications.read', $notification));

        $response->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success', '通知を既読にしました');

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
        ]);

        $notification->refresh();

        $this->assertNotNull($notification->read_at);
    }

    // NOTIFICATION-04
    public function test_message_is_displayed_when_no_notifications_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertSee('通知はありません。');
    }

    // NOTIFICATION-05
    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $notification = DatabaseNotification::create([
            'id' => 'notification-other-user',
            'type' => 'App\Notifications\ReadingPlanReminderNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $otherUser->id,
            'data' => [
                'title' => '読書リマインダー',
                'body' => '他ユーザーの通知です。',
                'timing' => 'on_due_date',
                'reading_plan_id' => 1,
            ],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('notifications.read', $notification));

        $response->assertForbidden();

        $notification->refresh();

        $this->assertNull($notification->read_at);
    }

    // NOTIFICATION-06
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

    // NOTIFICATION-07
    // 複数の通知が存在する場合、通知日時の新しい順に表示される
    public function test_notifications_are_displayed_in_newest_order(): void
    {
        $user = User::factory()->create();

        $oldNotification = DatabaseNotification::create([
            'id' => 'notification-old',
            'type' => 'App\Notifications\ReadingPlanReminderNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '古い通知',
                'body' => '古い通知の本文です。',
                'timing' => 'three_days_before',
                'reading_plan_id' => 1,
            ],
            'created_at' => now()->subMinutes(10),
        ]);

        $newNotification = DatabaseNotification::create([
            'id' => 'notification-new',
            'type' => 'App\Notifications\ReadingPlanReminderNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '新しい通知',
                'body' => '新しい通知の本文です。',
                'timing' => 'on_due_date',
                'reading_plan_id' => 2,
            ],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertSeeInOrder([
                '新しい通知',
                '古い通知',
            ]);
    }

    // NOTIFICATION-08
    // 通知が存在する場合、通知のタイトル・本文・通知日時が表示される
    public function test_notification_title_and_body_are_displayed(): void
    {
        $user = User::factory()->create();

        DatabaseNotification::create([
            'id' => 'notification-details',
            'type' => 'App\Notifications\ReadingPlanReminderNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '通知タイトル',
                'body' => '通知本文が表示されます。',
                'timing' => 'on_due_date',
                'reading_plan_id' => 1,
            ],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertSee('通知タイトル')
            ->assertSee('通知本文が表示されます。');
    }

    // NOTIFICATION-09
    // 既読の通知には「未読」ラベルと「既読にする」ボタンが表示されない
    public function test_read_notification_does_not_display_unread_label_or_read_button(): void
    {
        $user = User::factory()->create();

        DatabaseNotification::create([
            'id' => 'notification-already-read',
            'type' => 'App\Notifications\ReadingPlanReminderNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '既読通知',
                'body' => 'すでに既読になっている通知です。',
                'timing' => 'on_due_date',
                'reading_plan_id' => 1,
            ],
            'read_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertSee('既読通知')
            ->assertDontSee('未読')
            ->assertDontSee('既読にする');
    }

    // NOTIFICATION-10
    // 3日前・当日・3日経過後の通知は、タイミングに応じた本文が表示される
    public function test_notification_body_matches_reminder_timing(): void
    {
        $user = User::factory()->create();

        DatabaseNotification::create([
            'id' => 'notification-three-days-before',
            'type' => 'App\Notifications\ReadingPlanReminderNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '3日前のリマインダー',
                'body' => '読書計画の期日まであと3日です。',
                'timing' => 'three_days_before',
                'reading_plan_id' => 1,
            ],
        ]);

        DatabaseNotification::create([
            'id' => 'notification-on-due-date',
            'type' => 'App\Notifications\ReadingPlanReminderNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '当日のリマインダー',
                'body' => '読書計画の期日は今日です。',
                'timing' => 'on_due_date',
                'reading_plan_id' => 2,
            ],
        ]);

        DatabaseNotification::create([
            'id' => 'notification-three-days-after',
            'type' => 'App\Notifications\ReadingPlanReminderNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => '3日経過後のリマインダー',
                'body' => '読書計画の期日から3日経過しています。',
                'timing' => 'three_days_after',
                'reading_plan_id' => 3,
            ],
        ]);

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk()
            ->assertSee('読書計画の期日まであと3日です。')
            ->assertSee('読書計画の期日は今日です。')
            ->assertSee('読書計画の期日から3日経過しています。');
    }
}
