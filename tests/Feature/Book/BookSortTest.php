<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSortTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // 書籍ソート
    // =========================

    // SORT-01
    public function test_books_are_sorted_by_newest_by_default(): void
    {
        $oldBook = Book::factory()->create([
            'title' => '古い書籍',
            'created_at' => now()->subDay(),
        ]);

        $newBook = Book::factory()->create([
            'title' => '新しい書籍',
            'created_at' => now(),
        ]);

        $response = $this->get(route('books.index'));

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            $newBook->title,
            $oldBook->title,
        ]);
    }

    // SORT-02
    public function test_books_are_sorted_by_oldest(): void
    {
        $oldBook = Book::factory()->create([
            'title' => '古い書籍',
            'created_at' => now()->subDay(),
        ]);

        $newBook = Book::factory()->create([
            'title' => '新しい書籍',
            'created_at' => now(),
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'oldest',
        ]));

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            $oldBook->title,
            $newBook->title,
        ]);
    }

    // SORT-03
    public function test_books_are_sorted_by_title(): void
    {
        $zBook = Book::factory()->create([
            'title' => 'Zebra',
        ]);

        $aBook = Book::factory()->create([
            'title' => 'Apple',
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'title',
        ]));

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            $aBook->title,
            $zBook->title,
        ]);
    }

    // SORT-04
    public function test_books_are_sorted_by_rating(): void
    {
        $highRatedBook = Book::factory()->create([
            'title' => '高評価の本',
        ]);

        $lowRatedBook = Book::factory()->create([
            'title' => '低評価の本',
        ]);

        $noReviewBook = Book::factory()->create([
            'title' => 'レビューなしの本',
        ]);

        Review::factory()->create([
            'book_id' => $highRatedBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $lowRatedBook->id,
            'rating' => 3,
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'rating',
        ]));

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            $highRatedBook->title,
            $lowRatedBook->title,
            $noReviewBook->title,
        ]);
    }

    // SORT-05
    public function test_selected_sort_is_retained(): void
    {
        $response = $this->get(route('books.index', [
            'sort' => 'oldest',
        ]));

        $response->assertStatus(200);
        $response->assertSee(
            '<option value="oldest" selected>',
            false
        );
    }
}
