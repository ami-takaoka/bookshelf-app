<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookRequest;
use App\Http\Requests\Api\BookUpdateRequest;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookResource;
use App\Http\Resources\BookStoreResource;
use App\Models\Book;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::with('genres')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->get();

        return BookResource::collection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BookRequest $request)
    {
        $validated = $request->validated();

        $genres = $validated['genres'];

        unset($validated['genres']);

        $book = Book::create($validated);

        $book->genres()->attach($genres);

        $book->load('genres');

        return (new BookStoreResource($book))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $book->load([
            'genres',
            'reviews.user',
        ]);

        return new BookDetailResource($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookUpdateRequest $request, Book $book)
    {
        $validated = $request->validated();

        $genres = $validated['genres'];

        unset($validated['genres']);

        $book->update($validated);

        $book->genres()->sync($genres);

        $book->load('genres');

        return (new BookStoreResource($book))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $book->delete();

        return response()->noContent();
    }
}
