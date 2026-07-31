<?php

namespace Tests\Unit\Models;

use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    public function test_user_relationship_returns_belongs_to(): void
    {
        $review = new Review;

        $this->assertInstanceOf(
            BelongsTo::class,
            $review->user()
        );
    }

    public function test_book_relationship_returns_belongs_to(): void
    {
        $review = new Review;

        $this->assertInstanceOf(
            BelongsTo::class,
            $review->book()
        );
    }

    public function test_liked_by_users_relationship_returns_belongs_to_many(): void
    {
        $review = new Review;

        $this->assertInstanceOf(
            BelongsToMany::class,
            $review->likedByUsers()
        );
    }
}
