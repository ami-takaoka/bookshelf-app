<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $yamada = User::where('email', 'yamada@example.com')->firstOrFail();
        $suzuki = User::where('email', 'suzuki@example.com')->firstOrFail();

        $books = Book::whereIn('isbn', [
            '9784101010014',
            '9784422100524',
            '9784873115658',
            '9784863940246',
            '9784101010021',
            '9784309226712',
            '9784048930598',
            '9784478025819',
            '9784163902302',
        ])->get()->keyBy('isbn');

        $readingPlans = [
            // 山田太郎：3日前通知対象
            [
                'user_id' => $yamada->id,
                'book_id' => $books['9784101010014']->id,
                'target_date' => today()->addDays(3),
                'status' => ReadingPlanStatus::Pending,
            ],

            // 山田太郎：当日通知対象
            [
                'user_id' => $yamada->id,
                'book_id' => $books['9784422100524']->id,
                'target_date' => today(),
                'status' => ReadingPlanStatus::Pending,
            ],

            // 山田太郎：3日経過後通知対象
            [
                'user_id' => $yamada->id,
                'book_id' => $books['9784873115658']->id,
                'target_date' => today()->subDays(3),
                'status' => ReadingPlanStatus::Pending,
            ],

            // 山田太郎：期限切れ更新対象
            [
                'user_id' => $yamada->id,
                'book_id' => $books['9784863940246']->id,
                'target_date' => today()->subDay(),
                'status' => ReadingPlanStatus::Pending,
            ],

            // 山田太郎：通知対象外（未来）
            [
                'user_id' => $yamada->id,
                'book_id' => $books['9784101010021']->id,
                'target_date' => today()->addDays(5),
                'status' => ReadingPlanStatus::Pending,
            ],

            // 山田太郎：読了済み
            [
                'user_id' => $yamada->id,
                'book_id' => $books['9784309226712']->id,
                'target_date' => today()->subDay(),
                'status' => ReadingPlanStatus::Completed,
                'completed_at' => now(),
            ],

            // 山田太郎：期限切れ済み
            [
                'user_id' => $yamada->id,
                'book_id' => $books['9784048930598']->id,
                'target_date' => today()->subDays(5),
                'status' => ReadingPlanStatus::Expired,
            ],

            // 鈴木花子：別ユーザーの読書計画
            [
                'user_id' => $suzuki->id,
                'book_id' => $books['9784478025819']->id,
                'target_date' => today()->addDays(7),
                'status' => ReadingPlanStatus::Pending,
            ],

            // 鈴木花子：読了済み
            [
                'user_id' => $suzuki->id,
                'book_id' => $books['9784163902302']->id,
                'target_date' => today()->subDays(2),
                'status' => ReadingPlanStatus::Completed,
                'completed_at' => now()->subDays(2),
            ],
        ];

        foreach ($readingPlans as $readingPlan) {
            ReadingPlan::create($readingPlan);
        }
    }
}
