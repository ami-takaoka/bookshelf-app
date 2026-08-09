<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $comments = [
            1 => 'あまりおすすめできません。',
            2 => '少し物足りなく感じました。',
            3 => '普通に楽しめました。',
            4 => 'とても良い内容でした。',
            5 => 'ぜひおすすめしたい一冊です。',
        ];

        $userIds = User::pluck('id');
        $books = Book::all();

        foreach ($books as $book) {

            // 各書籍に2～4件のレビューを作成
            $reviewCount = random_int(2, 4);

            // 投稿者を重複しないようランダムに選択
            $reviewUserIds = $userIds->random($reviewCount);

            foreach ($reviewUserIds as $userId) {

                // 評価を1～5でランダムに設定
                $rating = random_int(1, 5);

                Review::create([
                    'user_id' => $userId,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => $comments[$rating],
                ]);
            }
        }
    }
}
