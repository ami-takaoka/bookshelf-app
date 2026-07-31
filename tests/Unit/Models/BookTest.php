<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class BookTest extends TestCase
{
    public function test_user_relationship_returns_belongs_to(): void
    {
        $book = new Book;

        $this->assertInstanceOf(
            BelongsTo::class,
            $book->user()
        );
    }

    public function test_reviews_relationship_returns_has_many(): void
    {
        $book = new Book;

        $this->assertInstanceOf(
            HasMany::class,
            $book->reviews()
        );
    }

    public function test_genres_relationship_returns_belongs_to_many(): void
    {
        $book = new Book;

        $this->assertInstanceOf(
            BelongsToMany::class,
            $book->genres()
        );
    }

    public function test_favorited_by_users_relationship_returns_belongs_to_many(): void
    {
        $book = new Book;

        $this->assertInstanceOf(
            BelongsToMany::class,
            $book->favoritedByUsers()
        );
    }
}
