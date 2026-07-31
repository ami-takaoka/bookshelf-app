<?php

namespace Tests\Feature\Favorite;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // お気に入り
    // =========================

    // FAVORITE-01
    public function test_guest_is_redirected_to_login_when_favoriting_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('login'));
    }

    // FAVORITE-02
    public function test_book_can_be_favorited(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    // FAVORITE-03
    public function test_favorited_book_can_be_unfavorited(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        // あらかじめお気に入り登録しておく
        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    // FAVORITE-04
    public function test_404_is_returned_when_favoriting_non_existent_book(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('favorites.toggle', 9999));

        $response->assertNotFound();
    }

    // =========================
    // お気に入り一覧
    // =========================

    // FAVORITE-05
    public function test_favorited_books_are_displayed_in_favorites_list(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)
            ->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertSee('Laravel入門');
    }

    // FAVORITE-06
    public function test_book_detail_page_can_be_displayed_from_favorites_list(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)
            ->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertSee('Laravel入門');
    }

    // FAVORITE-07
    public function test_second_page_of_paginated_favorites_can_be_displayed(): void
    {
        $user = User::factory()->create();

        $books = Book::factory()->count(11)->create();

        foreach ($books as $book) {
            $user->favoriteBooks()->attach($book->id);
        }

        $response = $this->actingAs($user)
            ->get(route('favorites.index', ['page' => 2]));

        $response->assertStatus(200);

        // 2ページ目に表示される11冊目
        $response->assertSee($books[10]->title);
    }

    // FAVORITE-08
    public function test_message_is_displayed_when_no_favorites_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertSee('お気に入りに登録された書籍はありません。');
    }

    // FAVORITE-09
    public function test_guest_is_redirected_to_login_when_accessing_favorites_index(): void
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect(route('login'));
    }

    // FAVORITE-10
    public function test_book_is_removed_from_favorites_list_when_unfavorited(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        // お気に入り登録
        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)
            ->from(route('favorites.index'))
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('favorites.index'));

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('favorites.index'));

        $response->assertDontSee('Laravel入門');
    }
}
