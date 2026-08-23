<?php

namespace App\Policies;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;

class ReadingPlanPolicy
{
    /**
     * ユーザーが読書計画を更新できるか判定する。
     *
     * 読書計画の所有者本人で、かつ読了済みでない場合に更新を許可する。
     */
    public function update(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id
            && $readingPlan->status !== ReadingPlanStatus::Completed;
    }

    /**
     * ユーザーが読書計画を削除できるか判定する。
     *
     * 読書計画の所有者本人である場合に削除を許可する。
     */
    public function delete(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }

    /**
     * ユーザーが読書計画を読了に変更できるか判定する。
     *
     * 読書計画の所有者本人である場合に読了への変更を許可する。
     */
    public function complete(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }
}
