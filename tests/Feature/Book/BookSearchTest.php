<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // 書籍検索・フィルタ
    // =========================

    // SEARCH-01
    public function test_books_can_be_searched_by_title(): void
    {
        $targetBook = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $otherBook = Book::factory()->create([
            'title' => 'PHP実践ガイド',
        ]);

        $response = $this->get(route('books.index', [
            'keyword' => 'Laravel',
        ]));

        $response->assertStatus(200);
        $response->assertSee($targetBook->title);
        $response->assertDontSee($otherBook->title);
    }

    // SEARCH-02
    public function test_books_can_be_searched_by_author(): void
    {
        $targetBook = Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        $otherBook = Book::factory()->create([
            'title' => 'PHP実践ガイド',
            'author' => '鈴木花子',
        ]);

        $response = $this->get(route('books.index', [
            'keyword' => '山田太郎',
        ]));

        $response->assertStatus(200);
        $response->assertSee($targetBook->title);
        $response->assertDontSee($otherBook->title);
    }

    // SEARCH-03
    public function test_search_keyword_is_retained(): void
    {
        $response = $this->get(route('books.index', [
            'keyword' => 'Laravel',
        ]));

        $response->assertStatus(200);
        $response->assertSee('value="Laravel"', false);
    }

    // SEARCH-04
    public function test_books_can_be_filtered_by_genre(): void
    {
        $targetGenre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $otherGenre = Genre::factory()->create([
            'name' => 'ビジネス',
        ]);

        $targetBook = Book::factory()->create([
            'title' => '吾輩は猫である',
        ]);

        $otherBook = Book::factory()->create([
            'title' => '人を動かす',
        ]);

        $targetBook->genres()->attach($targetGenre);
        $otherBook->genres()->attach($otherGenre);

        $response = $this->get(route('books.index', [
            'genre' => $targetGenre->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee($targetBook->title);
        $response->assertDontSee($otherBook->title);
    }

    // SEARCH-05
    public function test_selected_genre_is_retained(): void
    {
        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $response = $this->get(route('books.index', [
            'genre' => $genre->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee(
            '<option value="'.$genre->id.'" selected>',
            false
        );
    }

    // SEARCH-06
    public function test_message_is_displayed_when_no_books_match_search_conditions(): void
    {
        $response = $this->get(route('books.index', [
            'keyword' => '存在しない書籍',
        ]));

        $response->assertStatus(200);
        $response->assertSee('書籍が見つかりませんでした。');
    }

    // SEARCH-07
    public function test_search_conditions_are_retained_when_paginating(): void
    {
        $genre = Genre::factory()->create([
            'name' => '小説',
        ]);

        $books = Book::factory()->count(11)->create([
            'title' => 'Laravel入門',
        ]);

        $books->each(function (Book $book) use ($genre) {
            $book->genres()->attach($genre);
        });

        $response = $this->get(route('books.index', [
            'keyword' => 'Laravel',
            'genre' => $genre->id,
            'sort' => 'oldest',
        ]));

        $response->assertStatus(200);

        $response->assertSee(
            '?keyword=Laravel&amp;genre='.$genre->id.'&amp;sort=oldest&amp;page=2',
            false
        );
    }
}
