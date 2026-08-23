<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * ログインユーザーの読書レポートを表示する。
     *
     * レビューをもとに、総合サマリー、評価分布、
     * 高評価書籍、ジャンル別評価を集計する。
     */
    public function index(): View
    {
        $reviews = Review::where('user_id', auth()->id());

        $stats = [
            'summary' => Review::getSummary(clone $reviews),
            'rating_distribution' => Review::getRatingDistribution(clone $reviews),
            'top_rated_books' => Review::getTopRatedBooks(clone $reviews),
            'genre_ratings' => Review::getGenreRatings(clone $reviews),
        ];

        return view('reports.index', compact('stats'));
    }
}
