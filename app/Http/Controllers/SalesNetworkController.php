<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalesNetworkProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $profiles = SalesNetworkProfile::with(['employee.branch', 'sponsor'])
            ->where('status', 'pending_approval')
            ->latest()
            ->paginate(20);

        return view('sales-network.pending-approvals', compact('profiles'));
    }

    public function approve(Request $request, SalesNetworkProfile $profile)
    {
        abort_unless($profile->status === 'pending_approval', 422, 'Pendaftaran ini sudah diproses.');

        DB::transaction(function () use ($profile, $request) {
            $profile->update([
                'status'      => 'active',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);
            $profile->employee->update(['is_active' => true]);
        });

        return back()->with('success', "Pendaftaran {$profile->employee->name} berhasil disetujui.");
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

        return view('sales-network.admin-index', compact('profiles'));
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
