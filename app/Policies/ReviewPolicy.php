<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * ユーザーがレビューを更新できるか判定する。
     *
     * レビューの投稿者本人である場合に更新を許可する。
     */
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    /**
     * ユーザーがレビューを削除できるか判定する。
     *
     * レビューの投稿者本人である場合に削除を許可する。
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }
}
