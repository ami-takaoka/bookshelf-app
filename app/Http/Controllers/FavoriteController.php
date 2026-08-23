<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * ログインユーザーのお気に入り状態を切り替える。
     *
     * @param Book $book
     * @return RedirectResponse
     */
    public function toggle(Book $book): RedirectResponse
    {
        auth()->user()->favoriteBooks()->toggle($book->id);

        return back();
    }

    /**
     * ログインユーザーのお気に入り書籍一覧を表示する。
     *
     * @return View
     */
    public function index(): View
    {
        $books = auth()->user()
            ->favoriteBooks()
            ->paginate(10);

        return view('favorites.index', compact('books'));
    }
}