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
        // kalau url absolute http(s)://..., ambil path+query biar tidak pindah port 80
        if (preg_match('~^https?://~i', $url)) {
            $path  = parse_url($url, PHP_URL_PATH) ?? '/';
            $query = parse_url($url, PHP_URL_QUERY);
            $url = $path . ($query ? '?'.$query : '');
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
<div class="col-lg-6">
                  <div class="p-3 rounded-3 bg-light">
                    <div class="fw-semibold mb-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
	<path fill="#249f00" d="m9.55 15.15l8.475-8.475q.3-.3.7-.3t.7.3t.3.713t-.3.712l-9.175 9.2q-.3.3-.7.3t-.7-.3L4.55 13q-.3-.3-.288-.712t.313-.713t.713-.3t.712.3z" />
</svg>
                      A claim may be accepted if
                    </div>
                    <ul class="mb-0 small text-muted">
                      <li>Peeling/lifting edges caused by improper adhesion or finishing.</li>
                      <li>Bubbles/silvering caused by installation technique (not impact damage).</li>
                      <li>Wrinkles/creased areas caused during installation.</li>
                      <li>Misalignment/poor fitment (coverage not aligned to the panel properly).</li>
                      <li>Premature lifting at corners/contours due to incorrect stretching or squeegee technique.</li>
                      <li>Contamination under film (visible dust/hair trapped) beyond acceptable tolerance, verified during inspection.</li>
                      <li>Any defect that is confirmed by inspection as workmanship-related, not user/environment-related.</li>
                    </ul>
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="p-3 rounded-3 bg-light">
                    <div class="fw-semibold mb-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path fill="none" stroke="#ff1b1b" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12L6 6m6 6l6 6m-6-6l6-6m-6 6l-6 6" />
                      </svg>
                      A claim may be rejected if
                    </div>
                    <ul class="mb-0 small text-muted">
                      <li>The warranty is expired/void.</li>
                      <li>Damage is caused by impact/accident, scratches from external causes, vandalism, or misuse.</li>
                      <li>Damage is caused by chemical exposure (strong solvents), improper washing tools, high-pressure misuse, or extreme conditions outside normal use.</li>
                      <li>The claimed panel/area is not part of the installation record.</li>
                      <li>The film has been repaired/removed/reinstalled/modified by a non-authorized party.</li>
                      <li>Discoloration or yellowing caused by aging, exposure to sunlight, heat, or environmental factors is not covered under warranty.</li>
                    </ul>
                  </div>
                </div>

                <div class="col-12">
                  <div class="p-3 rounded-3 border">
                    <div class="fw-semibold mb-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path fill="#ff9a1b" d="M14.102 2.664c.628-.416 1.692-.713 2.495.09l4.647 4.648c.806.804.508 1.868.091 2.495a2.95 2.95 0 0 1-.863.85c-.334.213-.756.374-1.211.35a9 9 0 0 1-.658-.071l-.068-.01a9 9 0 0 0-.707-.073c-.504-.025-.698.06-.76.12l-2.49 2.491c-.08.08-.18.258-.256.6c-.073.33-.105.736-.113 1.186c-.007.432.008.874.024 1.3l.001.047c.015.423.03.855.009 1.194c-.065 1.031-.868 1.79-1.658 2.141c-.79.35-1.917.437-2.7-.347l-2.25-2.25L3.53 21.53a.75.75 0 1 1-1.06-1.06l4.104-4.105l-2.25-2.25c-.783-.784-.697-1.91-.346-2.7c.35-.79 1.11-1.593 2.14-1.658c.34-.021.772-.006 1.195.009l.047.001c.426.015.868.031 1.3.024c.45-.008.856-.04 1.186-.113c.342-.076.52-.177.6-.257l2.49-2.49c.061-.061.146-.256.12-.76a9 9 0 0 0-.073-.707l-.009-.068a9 9 0 0 1-.071-.658c-.025-.455.136-.877.348-1.211c.216-.34.515-.64.851-.863" />
                      </svg>
                      Important Notes
                    </div>
                    <ul class="mb-0 small text-muted">
                      <li>All claims require inspection and verification by Acegard before approval.</li>
                      <li>If the issue is not covered, the claim may be treated as chargeable and a quotation will be provided before work begins.</li>
                      <li>By submitting a claim, the customer agrees to inspection and documentation (photos/videos) for verification.</li>
                    </ul>
                  </div>
                </div>
