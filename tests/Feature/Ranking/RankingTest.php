<?php

namespace Tests\Feature\Ranking;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_books_are_ranked_by_average_rating(): void
    {
        $bookA = Book::factory()->create([
            'title' => 'Book A',
        ]);

        $bookB = Book::factory()->create([
            'title' => 'Book B',
        ]);

        $bookC = Book::factory()->create([
            'title' => 'Book C',
        ]);

        Review::factory()->count(2)->create([
            'book_id' => $bookA->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $bookB->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $bookC->id,
            'rating' => 4,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            'Book A',
            'Book B',
            'Book C',
        ]);
    }

    public function test_book_detail_page_can_be_accessed_from_ranking(): void
    {
        $book = Book::factory()->create([
            'title' => 'Book',
        ]);

        Review::factory()->count(2)->create([
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        // ランキング画面が表示される
        $rankingResponse = $this->get(route('ranking.index'));

        $rankingResponse->assertStatus(200);
        $rankingResponse->assertSee('Book');

        // ランキングに表示された書籍の詳細画面へアクセスできる
        $detailResponse = $this->get(route('books.show', $book));

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Book');
    }

    public function test_books_without_reviews_are_not_displayed(): void
    {
        $bookWithReview = Book::factory()->create([
            'title' => 'Reviewed Book',
        ]);

        $bookWithoutReview = Book::factory()->create([
            'title' => 'No Review Book',
        ]);

        Review::factory()->create([
            'book_id' => $bookWithReview->id,
            'rating' => 5,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertSee('Reviewed Book');
        $response->assertDontSee('No Review Book');
    }

    public function test_guest_can_view_ranking_page(): void
    {
        $book = Book::factory()->create([
            'title' => 'Book',
        ]);

        Review::factory()->create([
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertSee('Book');
    }

    public function test_message_is_displayed_when_no_reviewed_books_exist(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertSee('まだレビューが投稿された書籍がありません。');
    }
}
