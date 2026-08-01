<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // 書籍一覧API
    // =========================

    // API-01
    public function test_book_list_api_returns_books_with_genres_average_rating_and_review_count(): void
    {
        $genre = Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        $book->genres()->attach($genre);

        Review::factory()->count(2)->create([
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $response = $this->getJson('/api/v1/books');

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'genres',
                    'average_rating',
                    'review_count',
                ],
            ],
        ]);

        $response->assertJsonFragment([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'review_count' => 2,
        ]);
    }

    // API-02
    public function test_book_list_api_can_be_filtered_by_keyword(): void
    {
        Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        Book::factory()->create([
            'title' => 'PHP入門',
            'author' => '佐藤花子',
        ]);

        $response = $this->getJson('/api/v1/books?keyword=Laravel');

        $response->assertOk();

        $response->assertJsonFragment([
            'title' => 'Laravel入門',
        ]);

        $response->assertJsonMissing([
            'title' => 'PHP入門',
        ]);
    }

    // API-03
    public function test_book_list_api_can_be_filtered_by_genre(): void
    {
        $laravelGenre = Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $phpGenre = Genre::factory()->create([
            'name' => 'PHP',
        ]);

        $laravelBook = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $phpBook = Book::factory()->create([
            'title' => 'PHP入門',
        ]);

        $laravelBook->genres()->attach($laravelGenre);
        $phpBook->genres()->attach($phpGenre);

        $response = $this->getJson("/api/v1/books?genre={$laravelGenre->id}");

        $response->assertOk();

        $response->assertJsonFragment([
            'title' => 'Laravel入門',
        ]);

        $response->assertJsonMissing([
            'title' => 'PHP入門',
        ]);
    }

    // API-04
    public function test_book_list_api_can_be_paginated(): void
    {
        Book::factory()->count(11)->create();

        $response = $this->getJson('/api/v1/books?page=2');

        $response->assertOk();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);
    }

    // =========================
    // 書籍詳細API
    // =========================

    // API-05
    public function test_book_detail_api_returns_book_with_genres_and_reviews(): void
    {
        $book = Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        $genre = Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $book->genres()->attach($genre);

        Review::factory()->create([
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても参考になりました。',
        ]);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'image_url',
                'genres',
                'reviews',
            ],
        ]);

        $response->assertJsonFragment([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'comment' => 'とても参考になりました。',
        ]);
    }

    // API-06
    public function test_404_is_returned_when_showing_non_existent_book(): void
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertNotFound();
    }

    // =========================
    // 書籍登録API
    // =========================

    // API-07
    public function test_book_can_be_created_via_api(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();

        $response = $this->postJson('/api/v1/books', [
            'user_id' => $user->id,
            'title' => 'Laravel実践',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
            'description' => 'Laravelを学ぶための書籍です。',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [
                $genre->id,
            ],
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('books', [
            'title' => 'Laravel実践',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
        ]);

        $book = Book::where('isbn', '9781234567890')->first();

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    // API-08
    public function test_validation_error_is_returned_when_creating_book_via_api(): void
    {
        $response = $this->postJson('/api/v1/books', []);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'user_id',
            'title',
            'author',
            'isbn',
            'published_date',
            'genres',
        ]);

        $this->assertDatabaseCount('books', 0);
    }

    // =========================
    // 書籍更新API
    // =========================

    // API-09
    public function test_book_can_be_updated_via_api(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
            'isbn' => '9781234567890',
        ]);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'user_id' => $user->id,
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
            'description' => '更新後の説明です。',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [
                $genre->id,
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9781234567890',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    // API-10
    public function test_404_is_returned_when_updating_non_existent_book(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();

        $response = $this->putJson('/api/v1/books/99999', [
            'user_id' => $user->id,
            'title' => 'Laravel実践',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2026-01-01',
            'description' => 'Laravelを学ぶための書籍です。',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [
                $genre->id,
            ],
        ]);

        $response->assertNotFound();
    }

    // API-11
    public function test_validation_error_is_returned_when_updating_book_via_api(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->putJson("/api/v1/books/{$book->id}", []);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'user_id',
            'title',
            'author',
            'isbn',
            'published_date',
            'genres',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    // =========================
    // 書籍削除API
    // =========================

    // API-12
    public function test_book_and_related_data_can_be_deleted_via_api(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();

        $book = Book::factory()->create();

        $book->genres()->attach($genre);

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $book->favoritedByUsers()->attach($user);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        $this->assertDatabaseMissing('favorites', [
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);
    }

    // API-13
    public function test_404_is_returned_when_deleting_non_existent_book(): void
    {
        $response = $this->deleteJson('/api/v1/books/99999');

        $response->assertNotFound();
    }
}
