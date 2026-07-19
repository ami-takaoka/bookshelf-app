<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        Review::all()->each(function ($review) use ($users) {
            $likeUserIds = $users
                ->where('id', '!=', $review->user_id)
                ->shuffle()
                ->take(rand(0, 3))
                ->pluck('id');

            $review->likedByUsers()->syncWithoutDetaching($likeUserIds);
        });
    }
}
