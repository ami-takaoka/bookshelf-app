<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\View\View;

class RankingController extends Controller
{
    /**
     * レビュー評価をもとに書籍ランキングを表示する。
     *
     * @return View
     */
    public function index(): View
    {
        $rankedBooks = Book::query()
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->has('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('reviews_count')
            ->limit(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}