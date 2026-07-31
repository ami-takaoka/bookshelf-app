<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_books_relationship_returns_has_many(): void
    {
        $user = new User();

        $this->assertInstanceOf(
            HasMany::class,
            $user->books()
        );
    }

    public function test_reviews_relationship_returns_has_many(): void
    {
        $user = new User();

        $this->assertInstanceOf(
            HasMany::class,
            $user->reviews()
        );
    }

    public function test_favorite_books_relationship_returns_belongs_to_many(): void
    {
        $user = new User();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $user->favoriteBooks()
        );
    }

    public function test_liked_reviews_relationship_returns_belongs_to_many(): void
    {
        $user = new User();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $user->likedReviews()
        );
    }
}
