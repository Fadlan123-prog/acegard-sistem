<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifs = auth()->user()->notifications()->latest()->paginate(20);
        return view('notifications.index', compact('notifs'));
    }

    public function open(string $id)
    {
    $user = auth()->user();

    $n = $user->notifications()->where('id', $id)->firstOrFail();
    $data = $n->data ?? [];

    // mark read
    $n->markAsRead();

    // set active branch dari notif (biar route branch.access ga 403)
    $branchId = (int)($data['branch_id'] ?? 0);
    if ($branchId && !$user->hasRole('superadmin')) {
        $allowed = $user->branches()->where('branches.id', $branchId)->exists();
        if ($allowed) {
            session(['active_branch_id' => $branchId]);
        }
    }

    // ✅ PAKAI claim_id kalau ada (paling aman)
    if (!empty($data['claim_id'])) {
        return redirect()->route('admin.claims.show', $data['claim_id']);
    }

    // fallback: kalau ada url, pastikan RELATIVE
    $url = $data['url'] ?? null;

    if ($url) {
        // Strip to path+query for ANY absolute or protocol-relative URL
        // (http://, https://, or a bare "//host/..."). The previous check
        // only matched "^https?://" — "//evil.com" skipped it entirely,
        // and browsers still treat "//host" as a cross-origin redirect, so
        // that was a live open-redirect bypass (audit finding M-13).
        if (preg_match('~^(?:https?:)?//~i', $url)) {
            $path  = parse_url($url, PHP_URL_PATH) ?? '/';
            $query = parse_url($url, PHP_URL_QUERY);
            $url = $path . ($query ? '?'.$query : '');
        }

        // Belt-and-suspenders: whatever the parsing above produced, it must
        // start with exactly one "/" before it's trusted as same-origin.
        if (!str_starts_with($url, '/') || str_starts_with($url, '//') || str_starts_with($url, '/\\')) {
            $url = '/';
        }

        return redirect()->to($url);
    }

    return redirect()->route('dashboard');
    }

    public function readAll()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    }
}
