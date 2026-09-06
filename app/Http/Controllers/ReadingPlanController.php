<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\ReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    /**
     * ログインユーザーの読書計画一覧を表示する。
     *
     * ステータスが指定されている場合は、指定したステータスで絞り込む。
     */
    public function index(): View
    {
        $currentStatus = request('status');

        $readingPlans = ReadingPlan::with('book')
            ->where('user_id', auth()->id())
            ->when($currentStatus, function ($query) use ($currentStatus) {
                $query->where('status', $currentStatus);
            })
            ->latest('target_date')
            ->get();

        return view('reading-plans.index', compact(
            'readingPlans',
            'currentStatus'
        ));
    }

    /**
     * 読書計画の登録画面を表示する。
     */
    public function create(): View
    {
        $books = Book::orderBy('title')->get();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 新しい読書計画を登録する。
     */
    public function store(ReadingPlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        ReadingPlan::create([
            'user_id' => auth()->id(),
            'book_id' => $validated['book_id'],
            'target_date' => $validated['target_date'],
            'status' => ReadingPlanStatus::InProgress,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を登録しました');
    }

    /**
     * 指定した読書計画の編集画面を表示する。
     */
    public function edit(ReadingPlan $readingPlan): View
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    /**
     * 指定した読書計画を更新する。
     */
    public function update(
        ReadingPlanRequest $request,
        ReadingPlan $readingPlan
    ): RedirectResponse {

        $this->authorize('update', $readingPlan);

        $validated = $request->validated();

        $readingPlan->update([
            'target_date' => $validated['target_date'],
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を更新しました');
    }

    /**
     * 指定した読書計画を読了状態に更新する。
     */
    public function complete(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('complete', $readingPlan);

        if ($readingPlan->status === ReadingPlanStatus::Completed) {
            return redirect()
                ->route('reading-plans.index')
                ->with('error', 'この読書計画はすでに読了しています。');
        }

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を読了しました');
    }

    /**
     * 指定した読書計画を削除する。
     */
    public function destroy(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました');
    }
}
