<?php

namespace Tests\Feature\Screen;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_index_can_be_rendered(): void
    {
        Book::factory()->create([
            'title' => 'テスト書籍',
        ]);

        $response = $this->get(route('books.index'));

        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
    }

    public function test_book_detail_can_be_rendered(): void
    {
        $isbn = '9781234567890';

        $book = Book::factory()->create([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => $isbn,
        ]);

        Review::factory()->create([
            'book_id' => $book->id,
            'comment' => 'とても面白い本でした。',
        ]);

        $response = $this->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertSee('テスト著者');
        $response->assertSee($isbn);
        $response->assertSee('とても面白い本でした。');
    }

    public function test_ranking_screen_can_be_rendered(): void
    {
        // ランキング対象の書籍
        $book = Book::factory()->create([
            'title' => 'ランキング対象書籍',
        ]);

        // レビューを作成してランキング対象にする
        Review::factory()->create([
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertSee('ランキング対象書籍');
    }
}
