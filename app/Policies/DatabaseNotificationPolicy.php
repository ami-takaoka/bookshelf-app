<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class DatabaseNotificationPolicy
{
    /**
     * ユーザーが通知を既読にできるか判定する。
     *
     * 通知の所有者本人である場合に既読への変更を許可する。
     */
    public function read(User $user, DatabaseNotification $notification): bool
    {
        return $notification->notifiable_id === $user->id;
    }
}
