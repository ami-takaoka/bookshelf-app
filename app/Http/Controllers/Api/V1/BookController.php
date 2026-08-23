<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookRequest;
use App\Http\Requests\Api\BookUpdateRequest;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookResource;
use App\Http\Resources\BookStoreResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * 書籍一覧を取得する。
     *
     * キーワードとジャンルによる絞り込みに対応し、
     * レビュー件数と平均評価を含めてページネーションで返す。
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Book::with('genres')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre')) {
            $genreId = $request->genre;

            $query->whereHas('genres', function ($query) use ($genreId) {
                $query->where('genres.id', $genreId);
            });
        }

        $books = $query->paginate(10);

        return BookResource::collection($books);
    }

    /**
     * 新しい書籍を登録する。
     *
     * 認証ユーザーを登録者として設定し、指定されたジャンルを紐付ける。
     */
    public function store(BookRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;

        $genres = $validated['genres'];
        unset($validated['genres']);

        $book = DB::transaction(function () use ($validated, $genres): Book {
            $book = Book::create($validated);
            $book->genres()->attach($genres);

            return $book;
        });

        $book->load('genres');

        return (new BookStoreResource($book))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 指定した書籍の詳細を取得する。
     *
     * ジャンルとレビュー投稿者の情報を含めて返す。
     */
    public function show(Book $book): BookDetailResource
    {
        $book->load([
            'genres',
            'reviews.user',
        ]);

        return new BookDetailResource($book);
    }

    /**
     * 指定した書籍を更新する。
     *
     * 書籍の所有者のみ更新でき、ジャンルの紐付けも更新する。
     */
    public function update(
        BookUpdateRequest $request,
        Book $book
    ): JsonResponse {
        $this->authorize('update', $book);

        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $genres = $validated['genres'];
        unset($validated['genres']);

        DB::transaction(function () use ($book, $validated, $genres): void {
            $book->update($validated);
            $book->genres()->sync($genres);
        });

        $book->load('genres');

        return (new BookStoreResource($book))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * 指定した書籍を削除する。
     *
     * 書籍の所有者のみ削除できる。
     */
    public function destroy(Book $book): Response
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->noContent();
    }
}
