<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewLikeController extends Controller
{
    /**
     * ログインユーザーのレビューへのいいね状態を切り替える。
     *
     * @param Review $review
     * @return RedirectResponse
     */
    public function toggle(Review $review): RedirectResponse
    {
        auth()->user()->likedReviews()->toggle($review->id);

        return back();
    }
}