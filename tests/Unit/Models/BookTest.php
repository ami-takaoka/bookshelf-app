<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍のリレーションが定義されている(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();

        $book = Book::factory()
            ->for($user)
            ->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $book->genres()->attach($genre);

        $book->favoritedByUsers()->attach($user);

        $this->assertTrue($book->user->is($user));
        $this->assertTrue($book->reviews->contains($review));
        $this->assertTrue($book->genres->contains($genre));
        $this->assertTrue($book->favoritedByUsers->contains($user));
    }
}
