<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('Login.index');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(
            [
                'email'    => ['required', 'email'],
                'password' => ['required'],
            ],
            [
                'email.required'    => 'Email wajib diisi.',
                'email.email'       => 'Format email tidak valid.',
                'password.required' => 'Password wajib diisi.',
            ]
        );

        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Login gagal: email atau password salah.',
                ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        $allowedRoles = [
            'superadmin', 'owner', 'admin',
            'manager_operasional', 'manager_marketing',
            'marketing', 'finance', 'purchasing',
            'customer_service', 'teknisi', 'kenek',
            // Restrukturisasi ERP (2026-08)
            'it_manager', 'development', 'export_import',
            'strategi_pengembangan', 'designer', 'videographer',
        ];

        if (!$user->hasAnyRole($allowedRoles)) {
            return $this->logoutWithError(
                $request,
                'role',
                'Akun Anda tidak memiliki akses yang sesuai.'
            );
        }

        $request->session()->forget('active_branch_id');

        // Superadmin
        if ($user->hasRole('superadmin')) {
            $this->setDefaultBranchForSuperadmin();
            AuditService::logLogin($user, $request);
            return redirect()->intended(route('dashboard'));
        }

        // Teknisi / kenek bypass branch access middleware
        if ($user->hasAnyRole(['teknisi', 'kenek'])) {
            AuditService::logLogin($user, $request);
            return redirect()->intended(route('teknisi.dashboard'));
        }

        // Restrukturisasi ERP (2026-08) — company-wide roles, no branch needed,
        // same as teknisi/kenek. Each lands on its own module home page since
        // none of these roles use the generic /dashboard (superadmin|admin|...).
        if ($user->hasRole('development')) {
            AuditService::logLogin($user, $request);
            return redirect()->intended(route('development-reports.index'));
        }

        if ($user->hasRole('export_import')) {
            AuditService::logLogin($user, $request);
            return redirect()->intended(route('import-orders.index'));
        }

        if ($user->hasAnyRole(['strategi_pengembangan', 'designer', 'videographer'])) {
            AuditService::logLogin($user, $request);
            return redirect()->intended(route('creative-tasks.index'));
        }

        // All other staff roles — require at least one assigned branch
        $adminBranch = $this->getDefaultBranchForStaff($user);

        if (!$adminBranch) {
            return $this->logoutWithError(
                $request,
                'branch',
                'Akun Anda belum memiliki akses cabang. Hubungi administrator.'
            );
        }

        session(['active_branch_id' => $adminBranch->id]);
        AuditService::logLogin($user, $request);

        // Role-specific dashboard redirects
        if ($user->hasRole('owner')) {
            return redirect()->intended(route('owner.dashboard'));
        }

        if ($user->hasAnyRole(['marketing', 'manager_marketing'])) {
            return redirect()->intended(route('marketing.dashboard'));
        }

        if ($user->hasRole('customer_service')) {
            return redirect()->intended(route('cs.dashboard'));
        }

        if ($user->hasRole('manager_operasional')) {
            return redirect()->intended(route('manager-ops.dashboard'));
        }

        if ($user->hasRole('finance')) {
            return redirect()->intended(route('finance.dashboard'));
        }

        if ($user->hasAnyRole(['purchasing', 'it_manager'])) {
            return redirect()->intended(route('purchasing.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            AuditService::logLogout($user, $request);
        }

        Auth::logout();

        $request->session()->forget('active_branch_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function setDefaultBranchForSuperadmin(): void
    {
        $defaultBranch = Branch::query()
            ->where('name', 'like', '%pusat%')
            ->orWhere('city', 'like', '%pusat%')
            ->orderBy('id')
            ->first();

        if (!$defaultBranch) {
            $defaultBranch = Branch::query()
                ->orderBy('id')
                ->first();
        }

        if ($defaultBranch) {
            session(['active_branch_id' => $defaultBranch->id]);
        }
    }

    private function getDefaultBranchForStaff($user): ?Branch
    {
        return $user->branches()
            ->orderBy('branches.id')
            ->first();
    }

    private function logoutWithError(Request $request, string $key, string $message)
    {
        Auth::logout();

        $request->session()->forget('active_branch_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors([
                $key => $message,
            ]);
    }
}
