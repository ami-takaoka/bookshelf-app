<?php

namespace Tests\Feature\Review;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // レビュー投稿
    // =========================

    // REVIEW-01
    public function test_guest_is_redirected_to_login_when_posting_review(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('reviews.store', $book), [
            'rating' => 5,
            'comment' => 'とても面白い本でした。',
        ]);

        $response->assertRedirect(route('login'));
    }

    // REVIEW-02
    public function test_review_post_form_is_displayed(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertSee('レビューを投稿');
        $response->assertSee('評価');
        $response->assertSee('コメント');
        $response->assertSee('投稿する');
    }

    // REVIEW-03
    public function test_rating_is_required_when_posting_review(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('reviews.store', $book), [
                'rating' => '',
                'comment' => 'とても面白い本でした。',
            ]);

        $response->assertRedirect(route('books.show', $book));

        $response->assertSessionHasErrors([
            'rating' => '評価を選択してください',
        ]);
    }

    // REVIEW-04
    public function test_rating_must_be_between_1_and_5_when_posting_review(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('reviews.store', $book), [
                'rating' => 6,
                'comment' => 'とても面白い本でした。',
            ]);

        $response->assertRedirect(route('books.show', $book));

        $response->assertSessionHasErrors([
            'rating' => '評価は1から5の間で選択してください',
        ]);
    }

    // REVIEW-05
    public function test_comment_is_required_when_posting_review(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('reviews.store', $book), [
                'rating' => 5,
                'comment' => '',
            ]);

        $response->assertRedirect(route('books.show', $book));

        $response->assertSessionHasErrors([
            'comment' => 'レビューを入力してください',
        ]);
    }

    // REVIEW-06
    public function test_review_can_be_posted(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => 5,
                'comment' => 'とても面白い本でした。',
            ]);

        $response->assertRedirect(route('books.show', $book));

        $response->assertSessionHas('success', 'レビューを投稿しました');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても面白い本でした。',
        ]);
    }

    // =========================
    // レビュー編集
    // =========================

    // REVIEW-07
    public function test_guest_is_redirected_to_login_when_accessing_review_edit_page(): void
    {
        $review = Review::factory()->create();

        $response = $this->get(route('reviews.edit', $review));

        $response->assertRedirect(route('login'));
    }

    // REVIEW-08
    public function test_403_is_returned_when_accessing_another_users_review_edit_page(): void
    {
        $user = User::factory()->create();

        $anotherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reviews.edit', $review));

        $response->assertForbidden();
    }

    // REVIEW-09
    public function test_review_edit_form_displays_current_review_data(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても面白い本でした。',
        ]);

        $response = $this->actingAs($user)
            ->get(route('reviews.edit', $review));

        $response->assertStatus(200);
        $response->assertSee('レビューの編集');
        $response->assertSee($book->title);
        $response->assertSee('とても面白い本でした。');
        $response->assertSee('更新する');
    }

    // REVIEW-10
    public function test_required_fields_are_validated_when_updating_review(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('reviews.edit', $review))
            ->put(route('reviews.update', $review), [
                'rating' => '',
                'comment' => '',
            ]);

        $response->assertRedirect(route('reviews.edit', $review));

        $response->assertSessionHasErrors([
            'rating' => '評価を選択してください',
            'comment' => 'レビューを入力してください',
        ]);
    }

    // REVIEW-11
    public function test_review_can_be_updated(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => '更新前のレビューです。',
        ]);

        $response = $this->actingAs($user)
            ->put(route('reviews.update', $review), [
                'rating' => 5,
                'comment' => '更新後のレビューです。',
            ]);

        $response->assertRedirect(route('books.show', $book));

        $response->assertSessionHas(
            'success',
            'レビューを更新しました'
        );

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => '更新後のレビューです。',
        ]);
    }

    // REVIEW-12
    public function test_book_detail_page_can_be_displayed_when_canceling_review_edit(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('books.show', $book));

        $response->assertStatus(200);
    }

    // =========================
    // レビュー削除
    // =========================

    // REVIEW-13
    public function test_guest_is_redirected_to_login_when_deleting_review(): void
    {
        $review = Review::factory()->create();

        $response = $this->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('login'));
    }

    // REVIEW-14
    public function test_403_is_returned_when_deleting_another_users_review(): void
    {
        $user = User::factory()->create();

        $anotherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $anotherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('reviews.destroy', $review));

        $response->assertForbidden();
    }

    // REVIEW-15
    public function test_review_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('books.show', $book));

        $response->assertSessionHas(
            'success',
            'レビューを削除しました'
        );

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    // REVIEW-16
    public function test_review_likes_are_deleted_when_review_is_deleted(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $review->likedByUsers()->attach($user);

        $this->actingAs($user)
            ->delete(route('reviews.destroy', $review));

        $this->assertDatabaseMissing('review_like', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    // =========================
    // 例外処理
    // =========================

    // REVIEW-17
    public function test_404_is_returned_when_editing_non_existent_review(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reviews.edit', 99999));

        $response->assertNotFound();
    }

    // REVIEW-18
    public function test_404_is_returned_when_deleting_non_existent_review(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('reviews.destroy', 99999));

        $response->assertNotFound();
    }
}
