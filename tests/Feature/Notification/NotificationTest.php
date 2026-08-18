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
}
