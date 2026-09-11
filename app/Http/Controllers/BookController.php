<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookRequest;
use App\Http\Requests\IsbnSearchRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Services\GoogleBooksService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * 書籍一覧を表示する。
     *
     * キーワード、ジャンル、並び順による絞り込みに対応する。
     */
    public function index(Request $request): View
    {
        $genres = Genre::all();

        $books = Book::query()
            ->with('genres')
            ->withAvg('reviews', 'rating')
            ->keyword($request->keyword)
            ->genre($request->genre)
            ->sort($request->sort)
            ->paginate(10)
            ->withQueryString();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍登録画面を表示する。
     */
    public function create(): View
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を登録する。
     */
    public function store(BookRequest $request): RedirectResponse
    {
        $book = DB::transaction(function () use ($request): Book {
            $book = Book::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'author' => $request->author,
                'isbn' => $request->isbn,
                'published_date' => $request->published_date,
                'description' => $request->description,
                'image_url' => $request->image_url,
            ]);

            $book->genres()->sync($request->genres);

            return $book;
        });

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を登録しました');
    }

    /**
     * 書籍詳細を表示する。
     *
     * ジャンル、レビュー、レビューへのいいね情報を読み込む。
     */
    public function show(Book $book): View
    {
        $book->load([
            'genres',
            'reviews.user',
            'reviews.likedByUsers',
        ]);

        return view('books.show', compact('book'));
    }

    /**
     * 書籍編集画面を表示する。
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍を更新する。
     */
    public function update(
        BookRequest $request,
        Book $book
    ): RedirectResponse {
        $this->authorize('update', $book);

        DB::transaction(function () use ($request, $book): void {
            $book->update([
                'title' => $request->title,
                'author' => $request->author,
                'isbn' => $request->isbn,
                'published_date' => $request->published_date,
                'description' => $request->description,
                'image_url' => $request->image_url,
            ]);

            $book->genres()->sync($request->genres);
        });

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を更新しました');
    }

    /**
     * 書籍を削除する。
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を削除しました');
    }

    /**
     * ISBNからGoogle Books APIで書籍情報を取得する。
     */
    public function searchIsbn(
        IsbnSearchRequest $request,
        GoogleBooksService $googleBooksService
    ): JsonResponse {
        try {
            return response()->json(
                $googleBooksService->search($request->isbn)
            );
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}
