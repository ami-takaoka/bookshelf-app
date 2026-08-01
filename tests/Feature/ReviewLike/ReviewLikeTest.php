<?php

namespace Tests\Feature\ReviewLike;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // いいね
    // =========================

    // LIKE-01
    public function test_guest_is_redirected_to_login_when_liking_review(): void
    {
        $review = Review::factory()->create();

        $response = $this->post(route('reviews.like', $review));

        $response->assertRedirect(route('login'));
    }

    // LIKE-02
    public function test_review_can_be_liked(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('books.show', $review->book))
            ->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $review->book));

        $this->assertDatabaseHas('review_like', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    // LIKE-03
    public function test_liked_review_can_be_unliked(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create();

        // あらかじめいいねしておく
        $review->likedByUsers()->attach($user);

        $response = $this->actingAs($user)
            ->from(route('books.show', $review->book))
            ->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $review->book));

        $this->assertDatabaseMissing('review_like', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    // LIKE-04
    public function test_user_can_like_own_review(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('books.show', $review->book))
            ->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $review->book));

        $this->assertDatabaseHas('review_like', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    // LIKE-05
    public function test_404_is_returned_when_liking_non_existent_review(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reviews.like', 99999));

        $response->assertNotFound();
    }
}
