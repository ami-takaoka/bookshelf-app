<?php

namespace Tests\Feature\Book;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IsbnSearchTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // ISBN検索
    // =========================

    // ISBN-01
    public function test_book_information_can_be_retrieved_by_isbn(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 1,
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'Laravel入門',
                            'authors' => ['山田太郎'],
                            'publishedDate' => '2025-01-01',
                            'description' => 'Laravelの入門書です。',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/book.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.isbn', [
                'isbn' => '9781234567890',
            ]));

        $response->assertStatus(200);

        $response->assertJson([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'description' => 'Laravelの入門書です。',
            'image_url' => 'https://example.com/book.jpg',
        ]);
    }

    // ISBN-02
    public function test_success_message_is_displayed_after_isbn_search(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.create'));

        $response->assertStatus(200);
        $response->assertSee('書籍情報を取得しました。');
    }

    // ISBN-03
    public function test_validation_error_is_displayed_when_isbn_is_not_13_digits(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.isbn', [
                'isbn' => '123456789',
            ]));

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'isbn' => 'ISBNは13桁で入力してください。',
        ]);
    }

    // ISBN-04
    public function test_not_found_is_returned_when_book_does_not_exist(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 0,
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.isbn', [
                'isbn' => '9781234567890',
            ]));

        $response->assertNotFound();

        $response->assertJson([
            'error' => '該当する書籍が見つかりませんでした。',
        ]);
    }

    // ISBN-05
    public function test_error_is_returned_when_api_communication_fails(): void
    {
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([], 500),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.isbn', [
                'isbn' => '9781234567890',
            ]));

        $response->assertStatus(500);

        $response->assertJson([
            'error' => '通信エラーが発生しました。',
        ]);
    }
}
