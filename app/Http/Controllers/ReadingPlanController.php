<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\ReadingPlanRequest;
use App\Models\ReadingPlan;
use App\Models\Book;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ReadingPlanController extends Controller
{
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

    public function create(): View
    {
        $books = Book::orderBy('title')->get();

        return view('reading-plans.create', compact('books'));
    }

    public function store(ReadingPlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        ReadingPlan::create([
            'user_id' => auth()->id(),
            'book_id' => $validated['book_id'],
            'target_date' => $validated['target_date'],
            'status' => ReadingPlanStatus::Pending,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を登録しました');
    }

    public function edit(ReadingPlan $readingPlan): View
    {
        return view('reading-plans.edit', compact('readingPlan'));
    }

    public function update(ReadingPlanRequest $request,ReadingPlan $readingPlan): RedirectResponse
    {
        $validated = $request->validated();

        $readingPlan->update([
            'target_date' => $validated['target_date'],
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を更新しました');
    }

    

}
