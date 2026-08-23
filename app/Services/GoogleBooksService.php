<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class GoogleBooksService
{
    /**
     * ISBNから書籍情報を検索する。
     *
     * @throws Exception
     */
    public function search(string $isbn): array
    {
        $response = Http::get(
            'https://www.googleapis.com/books/v1/volumes',
            [
                'q' => 'isbn:'.$isbn,
            ]
        );

        if ($response->failed()) {
            throw new Exception('通信エラーが発生しました。', 500);
        }

        $data = $response->json();

        if (($data['totalItems'] ?? 0) === 0) {
            throw new Exception('該当する書籍が見つかりませんでした。', 404);
        }

        $book = $data['items'][0]['volumeInfo'];

        return [
            'title' => $book['title'] ?? null,
            'author' => implode(', ', $book['authors'] ?? []),
            'isbn' => $isbn,
            'published_date' => $book['publishedDate'] ?? null,
            'description' => $book['description'] ?? null,
            'image_url' => $book['imageLinks']['thumbnail'] ?? null,
        ];
    }
}
