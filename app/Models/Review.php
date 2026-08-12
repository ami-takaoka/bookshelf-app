<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'rating',
        'comment',
    ];

    // ======================
    // リレーション
    // ======================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'review_like')
            ->withTimestamps();
    }

    // ======================
    // レポート集計
    // ======================

    public static function getSummary(Builder $reviews): array
    {
        return [
            'total_reviews' => $reviews->count(),
            'books_read' => (clone $reviews)
                ->distinct('book_id')
                ->count('book_id'),
            'average_rating' => (clone $reviews)->avg('rating') ?? 0,
        ];
    }

    public static function getRatingDistribution(Builder $reviews): Collection
    {
        $distribution = collect();

        for ($rating = 1; $rating <= 5; $rating++) {
            $distribution->push(
                (clone $reviews)
                    ->where('rating', $rating)
                    ->count()
            );
        }

        return $distribution;
    }

    public static function getTopRatedBooks(Builder $reviews): Collection
    {
        return (clone $reviews)
            ->with('book')
            ->where('rating', '>=', 4)
            ->orderByDesc('rating')
            ->limit(5)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->book->id,
                    'title' => $review->book->title,
                    'author' => $review->book->author,
                    'rating' => $review->rating,
                ];
            });
    }

    public static function getGenreRatings(Builder $reviews): Collection
    {
        $genreReviews = (clone $reviews)
            ->with('book.genres')
            ->get();

        return $genreReviews
            ->flatMap(function ($review) {
                return $review->book->genres->map(function ($genre) use ($review) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'rating' => $review->rating,
                    ];
                });
            })
            ->groupBy('id')
            ->map(function ($reviews) {
                return [
                    'id' => $reviews->first()['id'],
                    'name' => $reviews->first()['name'],
                    'average_rating' => $reviews->avg('rating'),
                    'count' => $reviews->count(),
                ];
            })
            ->sortByDesc('average_rating')
            ->take(5)
            ->values();
    }
}
