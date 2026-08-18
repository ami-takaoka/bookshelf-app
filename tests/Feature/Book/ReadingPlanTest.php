<?php

namespace Tests\Feature\Book;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    // =========================
    // 読書計画
    // =========================

    // PLAN-01
    public function test_authenticated_user_can_view_own_reading_plans(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create();
        $otherBook = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $otherBook->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertStatus(200);
        $response->assertSee($book->title);
        $response->assertDontSee($otherBook->title);
    }

    // PLAN-02
    public function test_reading_plans_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();

        $pendingBook = Book::factory()->create([
            'title' => '未着手の書籍',
        ]);

        $completedBook = Book::factory()->create([
            'title' => '読了した書籍',
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $pendingBook->id,
            'status' => ReadingPlanStatus::Pending,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $completedBook->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index', [
                'status' => ReadingPlanStatus::Pending->value,
            ]));

        $response->assertStatus(200);
        $response->assertSee($pendingBook->title);
        $response->assertDontSee($completedBook->title);
    }

    // PLAN-03
    public function test_all_reading_plans_are_displayed_when_all_is_selected(): void
    {
        $user = User::factory()->create();

        $pendingBook = Book::factory()->create([
            'title' => '未着手の書籍',
        ]);

        $completedBook = Book::factory()->create([
            'title' => '読了した書籍',
        ]);

        $expiredBook = Book::factory()->create([
            'title' => '期限切れの書籍',
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $pendingBook->id,
            'status' => ReadingPlanStatus::Pending,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $completedBook->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $expiredBook->id,
            'status' => ReadingPlanStatus::Expired,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertStatus(200);
        $response->assertSee($pendingBook->title);
        $response->assertSee($completedBook->title);
        $response->assertSee($expiredBook->title);
    }

    // PLAN-04
    public function test_book_title_links_to_book_detail(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'title' => '詳細確認用の書籍',
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertStatus(200);

        $response->assertSee(
            route('books.show', $book),
            false
        );
    }

    // PLAN-05
    public function test_message_is_displayed_when_no_reading_plans_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertStatus(200);
        $response->assertSee('該当する読書計画はありません。');
    }

    // PLAN-06
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('reading-plans.index'));

        $response->assertRedirect(route('login'));
    }

    // PLAN-07
    public function test_reading_plan_can_be_completed(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Pending,
            'completed_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reading-plans.complete', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas(
            'success',
            '読書計画を読了しました'
        );

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);

        $this->assertNotNull(
            ReadingPlan::find($readingPlan->id)->completed_at
        );
    }

    // PLAN-08
    public function test_completed_reading_plan_does_not_show_complete_or_edit_buttons(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => '読了済みの書籍',
        ]);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertStatus(200);
        $response->assertSee($book->title);
        $response->assertDontSee('読了する');
        $response->assertDontSee(
            route('reading-plans.edit', $readingPlan),
            false
        );
    }

    // PLAN-09
    public function test_user_cannot_complete_another_users_reading_plan(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'status' => ReadingPlanStatus::Pending,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reading-plans.complete', $readingPlan));

        $response->assertForbidden();
    }

    // PLAN-10
    public function test_authenticated_user_can_view_reading_plan_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reading-plans.create'));

        $response->assertStatus(200);
        $response->assertViewIs('reading-plans.create');
    }

    // PLAN-11
    public function test_authenticated_user_can_create_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $targetDate = now()->addWeek()->toDateString();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => $targetDate,
            ]);

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas(
            'success',
            '読書計画を登録しました'
        );

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
            'status' => ReadingPlanStatus::Pending->value,
        ]);
    }

    // PLAN-12
    public function test_cannot_create_duplicate_pending_reading_plan_for_same_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Pending,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => now()->addWeek()->toDateString(),
            ]);

        $response->assertSessionHasErrors([
            'book_id' => 'この書籍は未完了の読書計画として既に登録されています',
        ]);

        $this->assertDatabaseCount('reading_plans', 1);
    }

    // PLAN-13
    public function test_can_create_new_reading_plan_for_completed_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $targetDate = now()->addWeek()->toDateString();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => $targetDate,
            ]);

        $response->assertRedirect(route('reading-plans.index'));

        $response->assertSessionHas(
            'success',
            '読書計画を登録しました'
        );

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $targetDate,
            'status' => ReadingPlanStatus::Pending->value,
        ]);

        $this->assertDatabaseCount('reading_plans', 2);
    }

    // PLAN-14
    public function test_authenticated_user_can_delete_reading_plan(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));

        $response->assertSessionHas(
            'success',
            '読書計画を削除しました'
        );

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    // PLAN-15
    public function test_user_cannot_delete_another_users_reading_plan(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }
}
