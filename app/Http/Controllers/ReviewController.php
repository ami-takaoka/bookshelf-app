<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * 指定した書籍にレビューを投稿する。
     *
     * @param ReviewRequest $request
     * @param Book $book
     * @return RedirectResponse
     */
    public function store(
        ReviewRequest $request,
        Book $book
    ): RedirectResponse {
        Review::create([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()
            ->route('books.show', $book)
            ->with('success', 'レビューを投稿しました');
    }

    /**
     * 指定したレビューの編集画面を表示する。
     *
     * @param Review $review
     * @return View
     */
    public function edit(Review $review): View
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * 指定したレビューを更新する。
     *
     * @param ReviewRequest $request
     * @param Review $review
     * @return RedirectResponse
     */
    public function update(
        ReviewRequest $request,
        Review $review
    ): RedirectResponse {
        $this->authorize('update', $review);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()
            ->route('books.show', $review->book)
            ->with('success', 'レビューを更新しました');
    }

    /**
     * 指定したレビューを削除する。
     *
     * @param Review $review
     * @return RedirectResponse
     */
    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $book = $review->book;

        $review->delete();

        return redirect()
            ->route('books.show', $book)
            ->with('success', 'レビューを削除しました');
    }
}