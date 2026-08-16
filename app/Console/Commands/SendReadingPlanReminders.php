<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendReadingPlanReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reading-plans:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '読書計画の期限切れ更新とリマインダー通知を実行する';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->expireReadingPlans();
        $this->sendReminders();

        return self::SUCCESS;
    }

    private function expireReadingPlans(): void
    {
        ReadingPlan::query()
            ->whereDate('target_date', '<', today())
            ->where('status', '!=', ReadingPlanStatus::Completed)
            ->update([
                'status' => ReadingPlanStatus::Expired,
            ]);
    }

    private function sendReminders(): void
    {
        $this->sendReminderForTargetDate(
            today()->addDays(3),
            'three_days_before'
        );

        $this->sendReminderForTargetDate(
            today(),
            'on_due_date'
        );

        $this->sendReminderForTargetDate(
            today()->subDays(3),
            'three_days_after'
        );
    }

    private function sendReminderForTargetDate(Carbon $targetDate, string $timing): void
    {
        $readingPlans = ReadingPlan::query()
            ->whereDate('target_date', $targetDate)
            ->where('status', '!=', ReadingPlanStatus::Completed)
            ->with(['user', 'book'])
            ->get();

        foreach ($readingPlans as $readingPlan) {
            $exists = $readingPlan->user
                ->notifications()
                ->where('data->reading_plan_id', $readingPlan->id)
                ->where('data->timing', $timing)
                ->exists();

            if ($exists) {
                continue;
            }

            $readingPlan->user->notify(
                new ReadingPlanReminderNotification(
                    $readingPlan,
                    $timing
                )
            );
        }
    }
}
