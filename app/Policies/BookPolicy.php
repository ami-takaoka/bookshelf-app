<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * ユーザーが書籍を更新できるか判定する。
     *
     * 書籍の登録者本人である場合に更新を許可する。
     */
    public function update(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }

    /**
     * ユーザーが書籍を削除できるか判定する。
     *
     * 書籍の登録者本人である場合に削除を許可する。
     */
    public function delete(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }
}
