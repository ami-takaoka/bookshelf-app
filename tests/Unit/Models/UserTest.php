<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_ユーザーのリレーションが定義されている(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $user->favoriteBooks()->attach($book);

        $user->likedReviews()->attach($review);

        $this->assertTrue($user->books->contains($book));
        $this->assertTrue($user->reviews->contains($review));
        $this->assertTrue($user->favoriteBooks->contains($book));
        $this->assertTrue($user->likedReviews->contains($review));
    }
}
