<?php

namespace Tests\Feature\Ranking;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // ランキング
    // =========================

    // RANKING-01
    public function test_books_are_ranked_by_average_rating_and_review_count_with_top_10(): void
    {
        // 1位：平均5.0、レビュー3件
        $bookA = Book::factory()->create([
            'title' => 'Book A',
        ]);

        Review::factory()->count(3)->create([
            'book_id' => $bookA->id,
            'rating' => 5,
        ]);

        // 2位：平均5.0、レビュー2件
        $bookB = Book::factory()->create([
            'title' => 'Book B',
        ]);

        Review::factory()->count(2)->create([
            'book_id' => $bookB->id,
            'rating' => 5,
        ]);

        // 3位：平均5.0、レビュー1件
        $bookC = Book::factory()->create([
            'title' => 'Book C',
        ]);

        Review::factory()->create([
            'book_id' => $bookC->id,
            'rating' => 5,
        ]);

        // 4位：平均4.0、レビュー1件
        $bookD = Book::factory()->create([
            'title' => 'Book D',
        ]);

        Review::factory()->create([
            'book_id' => $bookD->id,
            'rating' => 4,
        ]);

        // 5位〜10位：平均3.0
        $topBooks = [$bookA, $bookB, $bookC, $bookD];

        for ($i = 5; $i <= 10; $i++) {
            $book = Book::factory()->create([
                'title' => "Book {$i}",
            ]);

            Review::factory()->create([
                'book_id' => $book->id,
                'rating' => 3,
            ]);

            $topBooks[] = $book;
        }

        // 11位：TOP10外
        $outsideTop10 = Book::factory()->create([
            'title' => 'Outside Top 10',
        ]);

        Review::factory()->create([
            'book_id' => $outsideTop10->id,
            'rating' => 2,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);

        // 平均評価が高い順、同じ平均評価ならレビュー数が多い順
        $response->assertSeeInOrder([
            'Book A',
            'Book B',
            'Book C',
            'Book D',
            'Book 5',
            'Book 6',
            'Book 7',
            'Book 8',
            'Book 9',
            'Book 10',
        ]);

        // TOP10外の書籍は表示されない
        $response->assertDontSee('Outside Top 10');
    }

    // RANKING-02
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

    // RANKING-03
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

    // RANKING-04
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

    // RANKING-05
    public function test_message_is_displayed_when_no_reviewed_books_exist(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertSee('まだレビューが投稿された書籍がありません。');
    }
}
