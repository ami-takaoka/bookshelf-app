<?php

namespace Tests\Feature\Genre;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // ジャンル一覧
    // =========================

    // GENRE-01
    public function test_genre_list_with_book_count_can_be_displayed(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $books = Book::factory()->count(2)->create();

        foreach ($books as $book) {
            $book->genres()->attach($genre);
        }

        $response = $this->actingAs($user)
            ->get(route('genres.index'));

        $response->assertStatus(200);
        $response->assertSee('Laravel');
        $response->assertSee('2冊');
    }

    // GENRE-02
    public function test_genre_detail_page_can_be_displayed(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $response = $this->actingAs($user)
            ->get(route('genres.show', $genre));

        $response->assertStatus(200);
        $response->assertSee('ジャンル: Laravel');
    }

    // GENRE-03
    public function test_message_is_displayed_when_no_genres_are_registered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('genres.index'));

        $response->assertStatus(200);
        $response->assertSee('ジャンルが登録されていません。');
    }

    // GENRE-04
    public function test_genre_with_no_books_is_displayed_in_genre_list(): void
    {
        $user = User::factory()->create();

        Genre::factory()->create([
            'name' => 'PHP',
        ]);

        $response = $this->actingAs($user)
            ->get(route('genres.index'));

        $response->assertStatus(200);
        $response->assertSee('PHP');
        $response->assertSee('0冊');
    }

    // =========================
    // ジャンル詳細
    // =========================

    // GENRE-05
    public function test_books_related_to_genre_are_displayed(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $book->genres()->attach($genre);

        $response = $this->actingAs($user)
            ->get(route('genres.show', $genre));

        $response->assertStatus(200);
        $response->assertSee('ジャンル: Laravel');
        $response->assertSee('Laravel入門');
    }

    // GENRE-06
    public function test_message_is_displayed_when_genre_has_no_books(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $response = $this->actingAs($user)
            ->get(route('genres.show', $genre));

        $response->assertStatus(200);
        $response->assertSee('このジャンルの書籍はまだ登録されていません。');
    }

    // GENRE-07
    public function test_second_page_of_paginated_books_can_be_displayed(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();

        $books = Book::factory()->count(11)->create();

        foreach ($books as $book) {
            $book->genres()->attach($genre);
        }

        $response = $this->actingAs($user)
            ->get(route('genres.show', ['genre' => $genre, 'page' => 2]));

        $response->assertStatus(200);

        $response->assertSee($books->last()->title);
        $response->assertDontSee($books->first()->title);
    }

    // GENRE-08
    public function test_404_is_returned_when_genre_does_not_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('genres.show', 9999));

        $response->assertNotFound();
    }

    // =========================
    // ジャンル登録
    // =========================

    // GENRE-09
    public function test_guest_is_redirected_to_login_when_accessing_genre_create_page(): void
    {
        $response = $this->get(route('genres.create'));

        $response->assertRedirect(route('login'));
    }

    // GENRE-10
    public function test_genre_name_is_required_when_creating_genre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('genres.store'), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }

    // GENRE-11
    public function test_genre_name_must_be_unique_when_creating_genre(): void
    {
        $user = User::factory()->create();

        Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $response = $this->actingAs($user)
            ->post(route('genres.store'), [
                'name' => 'Laravel',
            ]);

        $response->assertSessionHasErrors('name');
    }

    // GENRE-12
    public function test_genre_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('genres.store'), [
                'name' => 'Laravel',
            ]);

        $response->assertRedirect(route('genres.index'));

        $response->assertSessionHas('success', 'ジャンルを登録しました');

        $this->assertDatabaseHas('genres', [
            'name' => 'Laravel',
        ]);
    }

    // GENRE-13
    public function test_genre_index_page_can_be_displayed_when_canceling_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('genres.index'));

        $response->assertStatus(200);
    }

    // =========================
    // ジャンル編集
    // =========================

    // GENRE-14
    public function test_guest_is_redirected_to_login_when_accessing_genre_edit_page(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get(route('genres.edit', $genre));

        $response->assertRedirect(route('login'));
    }

    // GENRE-15
    public function test_genre_edit_form_displays_current_genre_name(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $response = $this->actingAs($user)
            ->get(route('genres.edit', $genre));

        $response->assertStatus(200);
        $response->assertSee('value="Laravel"', false);
    }

    // GENRE-16
    public function test_genre_name_is_required_when_updating_genre(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $response = $this->actingAs($user)
            ->put(route('genres.update', $genre), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }

    // GENRE-17
    public function test_genre_name_must_be_unique_when_updating_genre(): void
    {
        $user = User::factory()->create();

        $genre1 = Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $genre2 = Genre::factory()->create([
            'name' => 'PHP',
        ]);

        $response = $this->actingAs($user)
            ->put(route('genres.update', $genre2), [
                'name' => 'Laravel',
            ]);

        $response->assertSessionHasErrors('name');
    }

    // GENRE-18
    public function test_genre_can_be_updated(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $response = $this->actingAs($user)
            ->put(route('genres.update', $genre), [
                'name' => 'PHP',
            ]);

        $response->assertRedirect(route('genres.index'));

        $response->assertSessionHas('success', 'ジャンルを更新しました');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => 'PHP',
        ]);
    }

    // GENRE-19
    public function test_genre_index_page_can_be_displayed_when_canceling_edit(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('genres.index'));

        $response->assertStatus(200);
    }

    // GENRE-20
    public function test_404_is_returned_when_editing_non_existent_genre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('genres.edit', 9999));

        $response->assertNotFound();
    }

    // =========================
    // ジャンル削除
    // =========================

    // GENRE-21
    public function test_guest_is_redirected_to_login_when_deleting_genre(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('login'));
    }

    // GENRE-22
    public function test_genre_can_be_deleted_when_no_books_are_attached(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $response = $this->actingAs($user)
            ->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));

        $response->assertSessionHas('success', 'ジャンルを削除しました');

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    // GENRE-23
    public function test_genre_cannot_be_deleted_when_books_are_attached(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => 'Laravel',
        ]);

        $book = Book::factory()->create();

        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)
            ->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));

        $response->assertSessionHas(
            'error',
            '書籍が紐づいているジャンルは削除できません'
        );

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }

    // GENRE-24
    public function test_404_is_returned_when_deleting_non_existent_genre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('genres.destroy', 9999));

        $response->assertNotFound();
    }
}
