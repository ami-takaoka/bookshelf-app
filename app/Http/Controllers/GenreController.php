<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenreRequest;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示する。
     *
     * @return View
     */
    public function index(): View
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンル登録画面を表示する。
     *
     * @return View
     */
    public function create(): View
    {
        return view('genres.create');
    }

    /**
     * 新しいジャンルを登録する。
     *
     * @param GenreRequest $request
     * @return RedirectResponse
     */
    public function store(GenreRequest $request): RedirectResponse
    {
        Genre::create($request->validated());

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを登録しました');
    }

    /**
     * 指定したジャンルの書籍一覧を表示する。
     *
     * @param Genre $genre
     * @return View
     */
    public function show(Genre $genre): View
    {
        $books = $genre->books()
            ->with('genres')
            ->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンル編集画面を表示する。
     *
     * @param Genre $genre
     * @return View
     */
    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * 指定したジャンルを更新する。
     *
     * @param GenreRequest $request
     * @param Genre $genre
     * @return RedirectResponse
     */
    public function update(GenreRequest $request, Genre $genre): RedirectResponse
    {
        $genre->update($request->validated());

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを更新しました');
    }

    /**
     * 指定したジャンルを削除する。
     *
     * 書籍が紐づいているジャンルは削除しない。
     *
     * @param Genre $genre
     * @return RedirectResponse
     */
    public function destroy(Genre $genre): RedirectResponse
    {
        if ($genre->books()->exists()) {
            return redirect()
                ->route('genres.index')
                ->with('error', '書籍が紐づいているジャンルは削除できません');
        }

        $genre->delete();

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを削除しました');
    }
}