<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookAuthTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // 書籍CRUD（認可の詳細）
    // =========================

    // BOOK-AUTH-01
    public function test_owner_can_see_edit_button_on_book_detail(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertSee('編集');
    }

    // BOOK-AUTH-02
    public function test_other_user_cannot_see_edit_button_on_book_detail(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertDontSee('編集');
    }

    // BOOK-AUTH-03
    public function test_owner_can_see_delete_button_on_book_detail(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertSee('削除');
    }

    // BOOK-AUTH-04
    public function test_other_user_cannot_see_delete_button_on_book_detail(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertDontSee('削除');
    }
}
