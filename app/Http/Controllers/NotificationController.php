<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * ログインユーザーの通知一覧を表示する。
     *
     * @return View
     */
    public function index(): View
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    /**
     * 指定した通知を既読にする。
     *
     * @param DatabaseNotification $notification
     * @return RedirectResponse
     */
    public function read(DatabaseNotification $notification): RedirectResponse
    {
        $this->authorize('read', $notification);

        $notification->markAsRead();

        return redirect()
            ->route('notifications.index')
            ->with('success', '通知を既読にしました');
    }
}