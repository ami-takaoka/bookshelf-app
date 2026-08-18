<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SanctumAuthTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // Sanctum認証
    // =========================

    // SANCTUM-01
    public function test_unauthenticated_user_cannot_create_book(): void
    {
        $response = $this->postJson('/api/v1/books', []);

        $response->assertUnauthorized();
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    // SANCTUM-02
    public function test_authenticated_user_can_create_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/books', [
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
            'user_id' => $user->id,
            'title' => 'Laravel実践',
            'author' => '山田太郎',
        ]);
    }

    // SANCTUM-03
    public function test_authenticated_user_cannot_update_another_users_book(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/books/'.$book->id, [
                'title' => '更新しようとした書籍',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => '2026-01-01',
                'description' => '更新テストです。',
                'image_url' => 'https://example.com/image.jpg',
                'genres' => [
                    $genre->id,
                ],
            ]);

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'This action is unauthorized.',
        ]);
    }

    // SANCTUM-04
    public function test_authenticated_user_cannot_delete_another_users_book(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/books/'.$book->id);

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'This action is unauthorized.',
        ]);
    }
}
