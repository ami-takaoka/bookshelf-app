<?php

namespace App\Http\Controllers;
use Illuminate\View\View;
use App\Models\ReadingPlan;


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
}
