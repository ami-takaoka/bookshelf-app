<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyReadingReportTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // マイ読書レポート
    // =========================

    // REPORT-01
    public function test_authenticated_user_can_view_my_reading_report(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('マイ読書レポート');
    }

    // REPORT-02
    public function test_basic_summary_is_displayed(): void
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 3,
        ]);

        Review::factory()->create([
            'user_id' => User::factory()->create()->id,
            'book_id' => Book::factory()->create()->id,
            'rating' => 1,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('2');
        $response->assertSee('4.0');
    }

    // REPORT-02 平均評価が存在しない場合
    public function test_dash_is_displayed_when_average_rating_does_not_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('-');
    }

    // REPORT-03
    public function test_rating_distribution_is_displayed(): void
    {
        $user = User::factory()->create();

        $books = Book::factory()->count(5)->create();

        foreach ($books as $index => $book) {
            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => $index + 1,
            ]);
        }

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);

        $response->assertSee('★');
        $response->assertSee('★★');
        $response->assertSee('★★★');
        $response->assertSee('★★★★');
        $response->assertSee('★★★★★');
        $response->assertSee('1件');
    }

    // REPORT-04
    public function test_top_rated_books_are_displayed_in_rating_order(): void
    {
        $user = User::factory()->create();

        $highRatedBook = Book::factory()->create([
            'title' => '5星の本',
        ]);

        $middleRatedBook = Book::factory()->create([
            'title' => '4星の本',
        ]);

        $lowRatedBook = Book::factory()->create([
            'title' => '3星の本',
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $highRatedBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $middleRatedBook->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $lowRatedBook->id,
            'rating' => 3,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $highRatedBook->title,
            $middleRatedBook->title,
        ]);

        $response->assertDontSee($lowRatedBook->title);

        $response->assertSee(
            route('books.show', $highRatedBook),
            false
        );
    }

    // REPORT-05
    public function test_message_is_displayed_when_no_top_rated_books_exist(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('4星以上の書籍がありません');
    }

    // REPORT-06　ジャンル別評価傾向の表示・平均評価・件数・リンク・評価順
    public function test_genre_ratings_are_displayed_in_rating_order(): void
    {
        $user = User::factory()->create();

        $highGenre = Genre::factory()->create([
            'name' => '高評価ジャンル',
        ]);

        $lowGenre = Genre::factory()->create([
            'name' => '低評価ジャンル',
        ]);

        $highBook = Book::factory()->create([
            'title' => '高評価書籍',
        ]);

        $lowBook = Book::factory()->create([
            'title' => '低評価書籍',
        ]);

        $highBook->genres()->attach($highGenre);
        $lowBook->genres()->attach($lowGenre);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $highBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $lowBook->id,
            'rating' => 3,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);

        // 評価順に表示される
        $response->assertSeeInOrder([
            $highGenre->name,
            $lowGenre->name,
        ]);

        // 平均評価
        $response->assertSee('5.0');
        $response->assertSee('3.0');

        // レビュー件数
        $response->assertSee('1件のレビュー');

        // ジャンル詳細へのリンク
        $response->assertSee(
            route('genres.show', $highGenre),
            false
        );

        $response->assertSee(
            route('genres.show', $lowGenre),
            false
        );
    }

    // REPORT-06 補足　ジャンル別評価傾向は最大5件まで表示される
    public function test_genre_ratings_are_limited_to_five(): void
    {
        $user = User::factory()->create();

        $genreData = [
            ['name' => '1位ジャンル', 'rating' => 5],
            ['name' => '2位ジャンル', 'rating' => 4],
            ['name' => '3位ジャンル', 'rating' => 3],
            ['name' => '4位ジャンル', 'rating' => 2],
            ['name' => '5位ジャンル', 'rating' => 1],
            ['name' => '6位ジャンル', 'rating' => 1],
        ];

        foreach ($genreData as $data) {
            $genre = Genre::factory()->create([
                'name' => $data['name'],
            ]);

            $book = Book::factory()->create();

            $book->genres()->attach($genre);

            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => $data['rating'],
            ]);
        }

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            '1位ジャンル',
            '2位ジャンル',
            '3位ジャンル',
            '4位ジャンル',
            '5位ジャンル',
        ]);

        $response->assertDontSee('6位ジャンル');
    }

    // REPORT-07
    public function test_message_is_displayed_when_no_genre_ratings_exist(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee(
            'ジャンルが設定された書籍のレビューがありません'
        );
    }

    // REPORT-08
    public function test_guest_cannot_access_my_reading_report(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }
}
