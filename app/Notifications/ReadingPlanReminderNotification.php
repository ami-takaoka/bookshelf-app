<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    /**
     * 新しい通知インスタンスを生成する。
     */
    public function __construct(
        private ReadingPlan $readingPlan,
        private string $timing,
    ) {}

    /**
     * 通知の配信チャンネルを定義する。
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * データベースに保存する通知データを配列として返す。
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

    /**
     * 通知本文を生成する。
     */
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
