<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private ReadingPlan $readingPlan,
        private string $timing,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => '読書リマインダー',
            'body' => $this->createBody(),
            'timing' => $this->timing,
            'reading_plan_id' => $this->readingPlan->id,
        ];
    }

    private function createBody(): string
    {
        $title = $this->readingPlan->book->title;

        return match ($this->timing) {
            'three_days_before' => "「{$title}」の読書予定日まであと3日です。",
            'on_due_date' => "「{$title}」の読書予定日です。",
            'three_days_after' => "「{$title}」の読書予定日から3日経過しました。",
            default => throw new \LogicException('想定外の通知タイミングです。'),
        };
    }
}
