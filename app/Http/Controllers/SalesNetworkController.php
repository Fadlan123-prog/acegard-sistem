<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalesNetworkProfile;
use App\Models\User;
use App\Services\AuditService;
use App\Services\PasswordSetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Jaringan referral sales — pendaftaran mirip member MLM: sales/marketing
 * yang sudah aktif merekrut sales baru sendiri (self-service), tapi akun
 * barunya nonaktif sampai admin approve (lihat pendingApprovals()/approve()).
 */
class SalesNetworkController extends Controller
{
    /** Sales/marketing: halaman jaringan sendiri — kode referral + daftar rekrutan. */
    public function myNetwork(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun ini tidak terhubung ke data karyawan.');

        $profile = $employee->salesNetworkProfile;
        abort_unless($profile, 403, 'Anda belum terdaftar di jaringan sales.');

        $recruits = $employee->sponsoredRecruits()->with('employee')->latest()->get();

        return view('sales-network.my-network', compact('profile', 'recruits', 'employee'));
    }

    public function create(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun ini tidak terhubung ke data karyawan.');

        $profile = $employee->salesNetworkProfile;
        abort_unless($profile?->isActive(), 403, 'Profil jaringan Anda belum aktif, tidak bisa merekrut.');

        return view('sales-network.create');
    }

    public function store(Request $request)
    {
        $sponsorEmployee = $request->user()->employee;
        abort_unless($sponsorEmployee, 403, 'Akun ini tidak terhubung ke data karyawan.');

        $sponsorProfile = $sponsorEmployee->salesNetworkProfile;
        abort_unless($sponsorProfile?->isActive(), 403, 'Profil jaringan Anda belum aktif, tidak bisa merekrut.');

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $this->createPendingSales($data['name'], $data['email'], $data['phone'] ?? null, $sponsorEmployee, $request->user()->id);

        return redirect()->route('sales-network.my-network')
            ->with('success', "Rekrutan \"{$data['name']}\" berhasil didaftarkan, menunggu approval admin sebelum bisa aktif.");
    }

    /**
     * Publik (tanpa login) — kandidat sales daftar sendiri lewat kode referral
     * yang dibagikan sponsornya. Hasilnya sama persis dengan jalur store() di
     * atas (pending_approval, employee nonaktif sampai admin approve) — bedanya
     * cuma siapa yang mengisi form: sponsor (store) vs kandidat sendiri (ini).
     */
    public function publicRegisterForm()
    {
        return view('sales-network.public-register');
    }

    public function publicRegister(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'referral_code' => ['required', 'string', 'max:20'],
        ], [
            'referral_code.required' => 'Kode referral wajib diisi — minta ke sponsor Anda.',
        ]);

        $sponsorProfile = SalesNetworkProfile::where('referral_code', strtoupper(trim($data['referral_code'])))
            ->where('status', 'active')
            ->with('employee')
            ->first();

        if (! $sponsorProfile || ! $sponsorProfile->employee) {
            return back()
                ->withErrors(['referral_code' => 'Kode referral tidak ditemukan atau sponsornya belum aktif.'])
                ->withInput();
        }

        $sponsorUserId = User::where('employee_id', $sponsorProfile->employee_id)->value('id');

        $this->createPendingSales($data['name'], $data['email'], $data['phone'] ?? null, $sponsorProfile->employee, $sponsorUserId);

        return redirect()->route('sales-network.public-register')
            ->with('success', "Pendaftaran \"{$data['name']}\" berhasil dikirim. Sponsor Anda: {$sponsorProfile->employee->name}. Menunggu approval admin sebelum akun bisa dipakai.");
    }

    /** Dipakai oleh store() (sponsor login merekrut) dan publicRegister() (kandidat daftar sendiri). */
    private function createPendingSales(string $name, string $email, ?string $phone, Employee $sponsorEmployee, ?int $createdByUserId): void
    {
        DB::transaction(function () use ($name, $email, $phone, $sponsorEmployee, $createdByUserId) {
            $newEmployee = Employee::create([
                'branch_id'    => $sponsorEmployee->branch_id,
                'name'         => $name,
                'phone_number' => $phone,
                'job_position' => 'Sales',
                'is_active'    => false, // aktif setelah admin approve
            ]);

            $tempPassword = Str::random(16);
            $user = User::create([
                'name'        => $name,
                'email'       => $email,
                'password'    => Hash::make($tempPassword),
                'employee_id' => $newEmployee->id,
            ]);
            $user->assignRole('sales');
            $user->branches()->sync([$sponsorEmployee->branch_id]);

            SalesNetworkProfile::create([
                'employee_id'   => $newEmployee->id,
                'referral_code' => $this->generateReferralCode(),
                'sponsor_id'    => $sponsorEmployee->id,
                'status'        => 'pending_approval',
                'created_by'    => $createdByUserId,
            ]);
        });
    }

    /** Admin: daftar pendaftaran sales yang menunggu approval. */
    public function pendingApprovals()
    {
        $profiles = SalesNetworkProfile::with(['employee.branch', 'employee.user', 'sponsor'])
            ->where('status', 'pending_approval')
            ->latest()
            ->paginate(20);

        return view('sales-network.pending-approvals', compact('profiles'));
    }

    /**
     * Setujui pendaftaran + tentukan cara sales mendapat password:
     *  - invite : sistem mengirim tautan buat-password ke email pendaftar
     *             (48 jam, sekali pakai). Admin TIDAK pernah tahu passwordnya.
     *  - manual : admin menentukan password sementara (wajib re-konfirmasi
     *             password admin sendiri); sales dipaksa menggantinya saat
     *             login pertama.
     */
    public function approve(Request $request, SalesNetworkProfile $profile)
    {
        abort_unless($profile->status === 'pending_approval', 422, 'Pendaftaran ini sudah diproses.');

        $manual = $request->input('password_mode') === 'manual';

        // Field admin dinamai "current_password" (bukan "admin_password") karena
        // namanya masuk $dontFlash — kalau validasi gagal, isinya tidak ikut
        // tersimpan di sesi sebagai old input.
        $data = $request->validate([
            'password_mode'    => ['required', Rule::in(['invite', 'manual'])],
            'password'         => $manual
                ? array_merge(PasswordSetupService::policy(), ['confirmed'])
                : ['nullable'],
            'current_password' => $manual ? ['required', 'current_password'] : ['nullable'],
        ], [
            'password.min'                      => 'Password minimal ' . (int) config('password_setup.min_length', 10) . ' karakter.',
            'password.letters'                  => 'Password harus mengandung huruf.',
            'password.mixed'                    => 'Password harus mengandung huruf besar dan huruf kecil.',
            'password.numbers'                  => 'Password harus mengandung angka.',
            'password.confirmed'                => 'Konfirmasi password tidak sama.',
            'current_password.required'         => 'Masukkan password Anda untuk konfirmasi.',
            'current_password.current_password' => 'Password Anda salah.',
        ]);

        $user = User::where('employee_id', $profile->employee_id)->first();
        abort_unless($user, 422, 'Akun login pendaftar tidak ditemukan.');

        if ($manual && Str::lower($data['password']) === Str::lower($user->email)) {
            throw ValidationException::withMessages(['password' => 'Password tidak boleh sama dengan email.']);
        }

        DB::transaction(function () use ($profile, $request, $manual, $user, $data) {
            $profile->update([
                'status'      => 'active',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);
            $profile->employee->update(['is_active' => true]);

            if ($manual) {
                $user->forceFill([
                    'password'             => Hash::make($data['password']),
                    'must_change_password' => true,
                    'password_changed_at'  => null,
                    'remember_token'       => Str::random(60),
                ])->save();
            }
        });

        $name = $profile->employee->name;
        AuditService::log(
            'approve',
            'Sales Network',
            "Menyetujui pendaftaran {$name} (" . ($manual ? 'password sementara oleh admin' : 'tautan buat-password via email') . ')',
            auditableId: $profile->id,
            auditableType: 'SalesNetworkProfile'
        );

        if ($manual) {
            return back()->with('success', "{$name} disetujui. Berikan password sementara ke sales secara langsung (jangan lewat grup). Sales wajib menggantinya saat login pertama.");
        }

        [$sent, $note] = $this->sendInvitation($user);

        return $sent
            ? back()->with('success', "{$name} disetujui. Tautan buat-password dikirim ke {$user->email} (berlaku 48 jam).")
            : back()->with('success', "{$name} disetujui.")->with('warning', $note);
    }

    /** Kirim ulang undangan — hanya untuk sales aktif yang belum pernah memilih passwordnya. */
    public function resendInvite(Request $request, SalesNetworkProfile $profile)
    {
        abort_unless($profile->isActive(), 422, 'Hanya anggota aktif yang bisa dikirimi undangan.');

        $user = User::where('employee_id', $profile->employee_id)->first();
        abort_unless($user, 422, 'Akun login anggota tidak ditemukan.');
        abort_if($user->must_change_password || $user->password_changed_at !== null, 422, 'Anggota ini sudah mengatur passwordnya.');

        [$sent, $note] = $this->sendInvitation($user);

        AuditService::log('resend_invite', 'Sales Network', "Kirim ulang undangan ke {$user->email}", auditableId: $profile->id, auditableType: 'SalesNetworkProfile');

        return $sent
            ? back()->with('success', "Undangan dikirim ulang ke {$user->email}.")
            : back()->with('warning', $note);
    }

    /** @return array{0:bool,1:string} [terkirim?, catatan untuk admin bila gagal] */
    private function sendInvitation(User $user): array
    {
        // Mailer "log" hanya menulis email ke storage/logs — tidak ada yang sampai ke pendaftar.
        if (config('mail.default') === 'log') {
            return [false, 'Email TIDAK terkirim: MAIL_MAILER masih "log". Atur SMTP di .env, lalu kirim ulang undangan dari menu Jaringan Sales.'];
        }

        // Akun non-sales (admin, owner, marketing, dst.) tidak memakai tautan email;
        // tautannya pun akan ditolak saat ditukar, jadi jangan dikirim sama sekali.
        if (! $user->canUseEmailPasswordSetup()) {
            return [false, 'Akun ini tidak memakai tautan email (role-nya tidak termasuk yang diizinkan). Ubah password lewat menu Users.'];
        }

        try {
            $status = app(PasswordSetupService::class)->sendLink($user, 'invitation');
        } catch (\Throwable $e) {
            Log::error('Pengiriman undangan sales gagal', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return [false, 'Email gagal dikirim (periksa pengaturan SMTP). Anda bisa kirim ulang dari menu Jaringan Sales.'];
        }

        if ($status === Password::RESET_THROTTLED) {
            return [false, 'Undangan baru saja dikirim. Tunggu 1 menit sebelum mengirim ulang.'];
        }

        return $status === Password::RESET_LINK_SENT
            ? [true, '']
            : [false, 'Undangan tidak dapat dikirim untuk akun ini.'];
    }

    public function reject(Request $request, SalesNetworkProfile $profile)
    {
        abort_unless($profile->status === 'pending_approval', 422, 'Pendaftaran ini sudah diproses.');

        $employee = $profile->employee;
        $user = User::where('employee_id', $employee->id)->first();

        DB::transaction(function () use ($profile, $employee, $user) {
            $profile->delete();
            $user?->delete();
            $employee->delete();
        });

        return back()->with('success', 'Pendaftaran ditolak dan dihapus.');
    }

    /** Admin: daftar semua anggota jaringan (aktif & pending) lintas cabang. */
    public function adminIndex()
    {
        $profiles = SalesNetworkProfile::with(['employee.branch', 'sponsor'])
            ->latest()
            ->paginate(20);

        // Anggota aktif yang belum pernah memilih passwordnya → boleh dikirimi ulang undangan.
        $needsInvite = User::with(['roles', 'employee'])
            ->whereIn('employee_id', $profiles->pluck('employee_id'))
            ->where('must_change_password', false)
            ->whereNull('password_changed_at')
            ->get()
            ->filter(fn (User $u) => $u->canUseEmailPasswordSetup())   // hanya role sales yang sudah aktif
            ->pluck('employee_id')
            ->flip();

        return view('sales-network.admin-index', compact('profiles', 'needsInvite'));
    }

    /**
     * Admin: masukkan karyawan yang SUDAH ADA (role sales/marketing) ke
     * jaringan secara langsung — dipakai untuk bikin akar jaringan (mis.
     * "Marketing Pusat") yang tidak punya sponsor sama sekali, karena jalur
     * self-service di atas selalu butuh sponsor yang sudah aktif.
     */
    public function adminCreate()
    {
        $employees = Employee::whereDoesntHave('salesNetworkProfile')
            ->whereHas('user', fn ($q) => $q->whereHas('roles', fn ($r) => $r->whereIn('name', ['sales', 'marketing'])))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $sponsorCandidates = SalesNetworkProfile::with('employee')
            ->where('status', 'active')
            ->get();

        return view('sales-network.admin-create', compact('employees', 'sponsorCandidates'));
    }

    public function adminStore(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id', 'unique:sales_network_profiles,employee_id'],
            'sponsor_id'  => ['nullable', 'exists:employees,id'],
        ]);

        SalesNetworkProfile::create([
            'employee_id'   => $data['employee_id'],
            'referral_code' => $this->generateReferralCode(),
            'sponsor_id'    => $data['sponsor_id'] ?? null,
            'status'        => 'active',
            'approved_by'   => $request->user()->id,
            'approved_at'   => now(),
            'created_by'    => $request->user()->id,
        ]);

        return redirect()->route('sales-network.admin-index')
            ->with('success', 'Anggota jaringan berhasil ditambahkan.');
    }

    private function generateReferralCode(): string
    {
        do {
            $code = 'REF-' . strtoupper(Str::random(6));
        } while (SalesNetworkProfile::where('referral_code', $code)->exists());

        return $code;
    }
}
