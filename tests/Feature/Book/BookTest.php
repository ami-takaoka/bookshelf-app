<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // 書籍一覧
    // =========================

    // BOOK-01
    public function test_pagination_is_displayed_when_more_than_ten_books_exist(): void
    {
        $genre = Genre::factory()->create();

        Book::factory()
            ->count(11)
            ->create()
            ->each(function ($book) use ($genre) {
                $book->genres()->attach($genre->id);
            });

        $response = $this->get(route('books.index'));

        $response->assertStatus(200);
        $response->assertSee('?page=2', false);
    }

    // BOOK-02
    public function test_book_detail_can_be_displayed(): void
    {
        $book = Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        $response = $this->get(route('books.index'));

        $response->assertStatus(200);
        $response->assertSee('Laravel入門');

        $response = $this->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertSee('Laravel入門');
        $response->assertSee('山田太郎');
    }

    // =========================
    // 書籍登録
    // =========================
    // BOOK-03
    public function test_guest_cannot_access_book_create_page(): void
    {
        $response = $this->get(route('books.create'));

        $response->assertRedirect(route('login'));
    }

    // BOOK-04
    public function test_book_create_page_can_be_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.create'));

        $response->assertStatus(200);
        $response->assertSee('書籍の登録');
        $response->assertSee('タイトル');
        $response->assertSee('著者');
        $response->assertSee('ISBN-13');
        $response->assertSee('出版日');
        $response->assertSee('ジャンル');
    }

    // BOOK-05
    public function test_book_cannot_be_created_without_title(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => '',
                'author' => 'テスト著者',
                'isbn' => '9781234567890',
                'published_date' => '2025-01-01',
                'description' => 'テスト',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_book_cannot_be_created_without_author(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'Laravel入門',
                'author' => '',
                'isbn' => '9781234567890',
                'published_date' => '2025-01-01',
                'description' => 'テスト',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('author');
    }

    public function test_book_cannot_be_created_without_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '',
            'published_date' => '2025-01-01',
            'description' => 'テスト',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_book_cannot_be_created_without_published_date(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '',
            'description' => 'テスト',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('published_date');
    }

    public function test_book_cannot_be_created_without_genres(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'description' => 'テスト',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [],
        ]);

        $response->assertSessionHasErrors('genres');
    }

    public function test_book_cannot_be_created_with_invalid_image_url(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'description' => 'テスト',
            'image_url' => 'not-url',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('image_url');
    }

    public function test_book_cannot_be_created_with_invalid_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '12345',
                'published_date' => '2025-01-01',
                'description' => 'テスト',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_book_cannot_be_created_with_invalid_published_date(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => 'abc',
                'description' => 'テスト',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('published_date');
    }

    // BOOK-06
    public function test_book_cannot_be_created_with_duplicate_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Book::factory()->create([
            'isbn' => '9781234567890',
        ]);

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'description' => 'テスト',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasErrors('isbn');
    }

    // BOOK-07
    public function test_authenticated_user_can_create_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'description' => 'テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('success', '書籍を登録しました');

        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'user_id' => $user->id,
        ]);
    }

    // BOOK-08
    public function test_book_create_cancel_button_redirects_to_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.create'));

        $response->assertStatus(200);
        $response->assertSee(route('books.index'), false);
    }

    // 認可（追加テスト）
    public function test_guest_cannot_create_book(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->post(route('books.store'), [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'description' => 'テスト用の説明です。',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('books', [
            'title' => 'テスト書籍',
        ]);
    }

    // =========================
    // 書籍編集
    // =========================

    // BOOK-09
    public function test_guest_cannot_access_book_edit_page(): void
    {
        $book = Book::factory()->create();

        $response = $this->get(route('books.edit', $book));

        $response->assertRedirect(route('login'));
    }

    // BOOK-10
    public function test_user_cannot_edit_other_users_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('books.edit', $book));

        $response->assertForbidden();
    }

    // BOOK-10 補足（更新処理）
    public function test_user_cannot_update_other_users_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
            'title' => '更新前タイトル',
        ]);

        $response = $this->actingAs($otherUser)
            ->put(route('books.update', $book), [
                'title' => '更新後タイトル',
                'author' => 'テスト著者',
                'isbn' => '9781234567890',
                'published_date' => '2025-01-01',
                'description' => 'テスト',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前タイトル',
        ]);
    }

    // BOOK-11
    public function test_book_edit_page_can_be_displayed(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'description' => 'テスト用の説明',
            'image_url' => 'https://example.com/book.jpg',
        ]);

        $response = $this->actingAs($user)
            ->get(route('books.edit', $book));

        $response->assertStatus(200);
        $response->assertSee('Laravel入門');
        $response->assertSee('山田太郎');
        $response->assertSee('9781234567890');
        $response->assertSee('テスト用の説明');
        $response->assertSee('https://example.com/book.jpg');
    }

    // BOOK-12
    public function test_book_cannot_be_updated_without_title(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => '',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => '2025-01-01',
                'description' => 'テスト',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_book_cannot_be_updated_without_author(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => 'Laravel入門',
                'author' => '',
                'isbn' => '9781234567890',
                'published_date' => '2025-01-01',
                'description' => 'テスト',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('author');
    }

    public function test_book_cannot_be_updated_without_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '',
                'published_date' => '2025-01-01',
                'description' => 'テスト',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_book_cannot_be_updated_without_published_date(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => '',
                'description' => 'テスト',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('published_date');
    }

    public function test_book_cannot_be_updated_without_genres(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => '2025-01-01',
                'description' => 'テスト',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [],
            ]);

        $response->assertSessionHasErrors('genres');
    }

    public function test_book_cannot_be_updated_with_invalid_image_url(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => '2025-01-01',
                'description' => 'テスト',
                'image_url' => 'not-url',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('image_url');
    }

    public function test_book_cannot_be_updated_with_invalid_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '12345',
                'published_date' => '2025-01-01',
                'description' => 'テスト',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('isbn');
    }

    public function test_book_cannot_be_updated_with_invalid_published_date(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '9781234567890',
                'published_date' => 'abc',
                'description' => 'テスト',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('published_date');
    }

    // BOOK-13
    public function test_book_cannot_be_updated_with_duplicate_isbn(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Book::factory()->create([
            'isbn' => '9781111111111',
        ]);

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '9782222222222',
        ]);

        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => 'Laravel入門',
                'author' => '山田太郎',
                'isbn' => '9781111111111',
                'published_date' => '2025-01-01',
                'description' => 'テスト',
                'image_url' => 'https://example.com/book.jpg',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('isbn');
    }

    // BOOK-14
    public function test_authenticated_user_can_update_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '更新前タイトル',
            'author' => '更新前著者',
        ]);

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => '9781234567890',
            'published_date' => '2025-01-01',
            'description' => '更新後の説明',
            'image_url' => 'https://example.com/book.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を更新しました');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
            'author' => '更新後著者',
        ]);
    }

    // BOOK-15
    public function test_book_edit_cancel_button_redirects_to_detail(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('books.edit', $book));

        $response->assertStatus(200);
        $response->assertSee(route('books.show', $book), false);
    }

    // =========================
    // 書籍削除
    // =========================

    // BOOK-16
    public function test_guest_cannot_delete_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->delete(route('books.destroy', $book));

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    // BOOK-17
    public function test_user_cannot_delete_other_users_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->delete(route('books.destroy', $book));

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    // BOOK-18
    public function test_authenticated_user_can_delete_book(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('books.destroy', $book));

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('success', '書籍を削除しました');

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    // =========================
    // 例外処理
    // =========================

    // BOOK-19
    public function test_non_existent_book_detail_page_returns_404(): void
    {
        $response = $this->get(route('books.show', 9999));

        $response->assertNotFound();
    }

    // BOOK-20
    public function test_non_existent_book_edit_page_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.edit', 9999));

        $response->assertNotFound();
    }

    // BOOK-21
    public function test_deleting_non_existent_book_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('books.destroy', 9999));

        $response->assertNotFound();
    }
}
