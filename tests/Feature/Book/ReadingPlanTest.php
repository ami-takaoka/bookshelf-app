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
    public function test_completed_reading_plan_cannot_be_completed_again(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)
            ->post(route('reading-plans.complete', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));

        $response->assertSessionHas(
            'error',
            'この読書計画はすでに読了しています。'
        );

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);

        $readingPlan->refresh();

        $this->assertNotNull($readingPlan->completed_at);
    }

    // PLAN-10
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

    // PLAN-11
    public function test_authenticated_user_can_view_reading_plan_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reading-plans.create'));

        $response->assertStatus(200);
        $response->assertViewIs('reading-plans.create');
    }

    // PLAN-12
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

    // PLAN-13
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

    // PLAN-14
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

    // PLAN-15
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

    // PLAN-16
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

    // PLAN-17
    public function test_guest_is_redirected_to_login_when_accessing_create_page(): void
    {
        $response = $this->get(route('reading-plans.create'));

        $response->assertRedirect(route('login'));
    }

    // PLAN-18
    public function test_guest_is_redirected_to_login_when_accessing_edit_page(): void
    {
        $readingPlan = ReadingPlan::factory()->create();

        $response = $this->get(route('reading-plans.edit', $readingPlan));

        $response->assertRedirect(route('login'));
    }

    // PLAN-19
    public function test_guest_is_redirected_to_login_when_deleting_reading_plan(): void
    {
        $readingPlan = ReadingPlan::factory()->create();

        $response = $this->delete(
            route('reading-plans.destroy', $readingPlan)
        );

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    // PLAN-20
    public function test_cannot_create_reading_plan_with_past_date(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => now()->subDay()->toDateString(),
            ]);

        $response->assertSessionHasErrors([
            'target_date' => '期日は本日以降の日付を入力してください',
        ]);

        $this->assertDatabaseMissing('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    // PLAN-21
    public function test_completed_reading_plan_cannot_be_edited(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.edit', $readingPlan));

        $response->assertForbidden();
    }

    // PLAN-22
    public function test_create_page_cancel_redirects_to_reading_plan_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reading-plans.create'));

        $response->assertStatus(200);
        $response->assertSee(
            route('reading-plans.index'),
            false
        );
    }

    // PLAN-23
    public function test_edit_page_cancel_redirects_to_reading_plan_index(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Pending,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.edit', $readingPlan));

        $response->assertStatus(200);
        $response->assertSee(
            route('reading-plans.index'),
            false
        );
    }

    // PLAN-24
    public function test_cannot_create_reading_plan_without_book(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => '',
                'target_date' => now()->addWeek()->toDateString(),
            ]);

        $response->assertSessionHasErrors([
            'book_id' => '書籍を選択してください',
        ]);

        $this->assertDatabaseCount('reading_plans', 0);
    }

    // PLAN-25
    public function test_cannot_create_reading_plan_without_target_date(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => '',
            ]);

        $response->assertSessionHasErrors([
            'target_date' => '期日を入力してください',
        ]);

        $this->assertDatabaseMissing('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    // PLAN-26
    public function test_cannot_create_reading_plan_with_non_existent_book(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => 999999,
                'target_date' => now()->addWeek()->toDateString(),
            ]);

        $response->assertSessionHasErrors([
            'book_id' => '選択した書籍が存在しません',
        ]);

        $this->assertDatabaseCount('reading_plans', 0);
    }
}
