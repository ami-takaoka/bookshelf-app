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
        $reviews = [
            // 吾輩は猫である
            ['email' => 'yamada@example.com', 'isbn' => '9784101010014', 'rating' => 5, 'comment' => '猫の視点から描かれる人間社会がとても面白かったです。'],
            ['email' => 'suzuki@example.com', 'isbn' => '9784101010014', 'rating' => 4, 'comment' => 'ユーモアのある文章で楽しく読むことができました。'],
            ['email' => 'tanaka@example.com', 'isbn' => '9784101010014', 'rating' => 4, 'comment' => '時代を超えて楽しめる作品だと思います。'],

            // 人を動かす
            ['email' => 'suzuki@example.com', 'isbn' => '9784422100524', 'rating' => 5, 'comment' => '人との接し方を見直すきっかけになりました。'],
            ['email' => 'tanaka@example.com', 'isbn' => '9784422100524', 'rating' => 5, 'comment' => '仕事でも日常生活でも役立つ内容が多かったです。'],
            ['email' => 'sato@example.com', 'isbn' => '9784422100524', 'rating' => 4, 'comment' => '具体例が多く、実践しやすい内容でした。'],

            // リーダブルコード
            ['email' => 'yamada@example.com', 'isbn' => '9784873115658', 'rating' => 5, 'comment' => '読みやすいコードを書くための考え方がよく理解できました。'],
            ['email' => 'sato@example.com', 'isbn' => '9784873115658', 'rating' => 5, 'comment' => 'プログラミングを学ぶ人におすすめしたい一冊です。'],
            ['email' => 'takahashi@example.com', 'isbn' => '9784873115658', 'rating' => 4, 'comment' => '実際の開発ですぐに活用できる内容でした。'],

            // 7つの習慣
            ['email' => 'yamada@example.com', 'isbn' => '9784863940246', 'rating' => 4, 'comment' => '自分の行動や考え方を振り返るきっかけになりました。'],
            ['email' => 'tanaka@example.com', 'isbn' => '9784863940246', 'rating' => 5, 'comment' => '何度も読み返したいと思える内容でした。'],
            ['email' => 'takahashi@example.com', 'isbn' => '9784863940246', 'rating' => 4, 'comment' => '仕事だけでなく人生全体に活かせる考え方だと思います。'],

            // 坊っちゃん
            ['email' => 'suzuki@example.com', 'isbn' => '9784101010021', 'rating' => 4, 'comment' => 'テンポが良く、最後まで楽しく読めました。'],
            ['email' => 'sato@example.com', 'isbn' => '9784101010021', 'rating' => 3, 'comment' => '主人公のまっすぐな性格が印象的でした。'],
            ['email' => 'takahashi@example.com', 'isbn' => '9784101010021', 'rating' => 4, 'comment' => '登場人物が個性的で面白かったです。'],

            // サピエンス全史
            ['email' => 'yamada@example.com', 'isbn' => '9784309226712', 'rating' => 5, 'comment' => '人類の歴史を新しい視点から考えることができました。'],
            ['email' => 'suzuki@example.com', 'isbn' => '9784309226712', 'rating' => 4, 'comment' => '壮大なテーマですが、とても興味深く読めました。'],
            ['email' => 'sato@example.com', 'isbn' => '9784309226712', 'rating' => 5, 'comment' => '歴史と科学の両方を楽しめる一冊でした。'],

            // Clean Code
            ['email' => 'suzuki@example.com', 'isbn' => '9784048930598', 'rating' => 5, 'comment' => 'コードの品質について深く考えるきっかけになりました。'],
            ['email' => 'tanaka@example.com', 'isbn' => '9784048930598', 'rating' => 4, 'comment' => '実務で意識したい考え方が多く紹介されています。'],
            ['email' => 'takahashi@example.com', 'isbn' => '9784048930598', 'rating' => 5, 'comment' => 'エンジニアとして成長するために役立つ一冊です。'],

            // 嫌われる勇気
            ['email' => 'yamada@example.com', 'isbn' => '9784478025819', 'rating' => 4, 'comment' => '対話形式なので難しい内容でも読みやすかったです。'],
            ['email' => 'tanaka@example.com', 'isbn' => '9784478025819', 'rating' => 5, 'comment' => '自分らしく生きることについて考えさせられました。'],
            ['email' => 'sato@example.com', 'isbn' => '9784478025819', 'rating' => 4, 'comment' => '人間関係について新しい視点を得られました。'],

            // 火花
            ['email' => 'suzuki@example.com', 'isbn' => '9784163902302', 'rating' => 3, 'comment' => '芸人として生きることの厳しさが伝わってきました。'],
            ['email' => 'sato@example.com', 'isbn' => '9784163902302', 'rating' => 4, 'comment' => '登場人物の葛藤がリアルに描かれていました。'],

            // FACTFULNESS
            ['email' => 'yamada@example.com', 'isbn' => '9784822289607', 'rating' => 5, 'comment' => '思い込みではなくデータを見ることの大切さを学びました。'],
            ['email' => 'tanaka@example.com', 'isbn' => '9784822289607', 'rating' => 5, 'comment' => '世界に対する見方が大きく変わる一冊でした。'],
            ['email' => 'takahashi@example.com', 'isbn' => '9784822289607', 'rating' => 4, 'comment' => '具体的なデータが多く説得力がありました。'],

            // コンテナ物語
            ['email' => 'suzuki@example.com', 'isbn' => '9784822251468', 'rating' => 4, 'comment' => '物流が世界経済に与えた影響を詳しく知ることができました。'],
            ['email' => 'sato@example.com', 'isbn' => '9784822251468', 'rating' => 4, 'comment' => '身近なコンテナの歴史が想像以上に興味深かったです。'],
            ['email' => 'takahashi@example.com', 'isbn' => '9784822251468', 'rating' => 3, 'comment' => '経済と歴史のつながりを学べる内容でした。'],
        ];

        foreach ($reviews as $reviewData) {
            $user = User::where('email', $reviewData['email'])->first();
            $book = Book::where('isbn', $reviewData['isbn'])->first();

            Review::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => $reviewData['rating'],
                'comment' => $reviewData['comment'],
            ]);
        }
    }
}
