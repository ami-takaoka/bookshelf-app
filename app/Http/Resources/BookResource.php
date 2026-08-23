<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * 書籍一覧のリソースを配列に変換する。
     *
     * ジャンル、平均評価、レビュー件数を含む書籍情報を返す。
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'genres' => GenreResource::collection($this->genres),
            'average_rating' => $this->reviews_avg_rating,
            'review_count' => $this->reviews_count,
        ];
    }
}