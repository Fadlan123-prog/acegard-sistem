<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionPaymentBatch;
use App\Models\Customer;
use App\Models\EmployeeHealthBalance;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Services\CommissionEligibilityService;
use App\Services\DashboardCacheService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TeknisiDashboardController extends Controller
{
    public function __construct(private readonly DashboardCacheService $cache) {}

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Akun Anda belum terhubung ke data karyawan. Hubungi superadmin.');
        }

        $isTeknisi  = $user->hasRole('teknisi');
        $foreignKey = $isTeknisi ? 'tukang_id' : 'kenek_id';

        $jobs          = $this->cache->getTeknisiActiveJobs($employee->id, $foreignKey);
        $completedJobs = $this->cache->getTeknisiCompletedJobs($employee->id, $foreignKey);
        $commissions   = $this->cache->getTeknisiCommissions($employee->id);

        $commissionSummary = [
            'pending_count'  => $commissions->where('status', 'pending')->count(),
            'pending_total'  => $commissions->where('status', 'pending')->sum('amount'),
            'eligible_count' => $commissions->where('status', 'eligible')->count(),
            'eligible_total' => $commissions->where('status', 'eligible')->sum('amount'),
            'paid_count'     => $commissions->where('status', 'paid')->count(),
            'paid_total'     => $commissions->where('status', 'paid')->sum('amount'),
        ];

        $nowWib = Carbon::now('Asia/Jakarta');
        $isWithdrawalDay = $nowWib->day === 15;

        // Pending withdrawal request this month (draft = menunggu approval)
        $pendingWithdrawal = CommissionPaymentBatch::query()
            ->where('employee_id', $employee->id)
            ->where('status', 'draft')
            ->whereYear('payment_date', $nowWib->year)
            ->whereMonth('payment_date', $nowWib->month)
            ->first();

        // Most recent withdrawal (any status) this month for display
        $latestWithdrawal = CommissionPaymentBatch::query()
            ->where('employee_id', $employee->id)
            ->whereYear('payment_date', $nowWib->year)
            ->whereMonth('payment_date', $nowWib->month)
            ->latest()
            ->first();

        // Can request if: it's the 15th, has eligible commissions not yet batched, no pending request
        $eligibleUnbatched = $commissions
            ->where('status', 'eligible')
            ->whereNull('commission_payment_batch_id');

        $canRequestWithdrawal = $isWithdrawalDay
            && $eligibleUnbatched->count() > 0
            && $pendingWithdrawal === null;

        $healthBalance = EmployeeHealthBalance::where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->first();

        // Ambil notifikasi penugasan yang belum dibaca, lalu langsung tandai sudah dibaca
        $jobNotifications = $user->unreadNotifications()
            ->where('type', \App\Notifications\JobAssigned::class)
            ->get();

        if ($jobNotifications->isNotEmpty()) {
            $user->unreadNotifications()
                ->where('type', \App\Notifications\JobAssigned::class)
                ->update(['read_at' => now()]);
        }

        // Sales Order yang sudah menyertakan nama karyawan ini tapi belum jadi Customer/Invoice
        // sungguhan (baru muncul di "Orderan Saya" setelah admin klik "Buat Customer & Invoice").
        // Ditampilkan read-only supaya teknisi tahu ada pekerjaan yang segera datang.
        $incomingSalesOrders = SalesOrder::query()
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where(function ($q) use ($foreignKey, $employee, $isTeknisi) {
                $q->where($foreignKey, $employee->id);
                // Gedung: penugasan lewat pivot technicians (tidak ada kenek di kategori ini)
                if ($isTeknisi) {
                    $q->orWhereHas('technicians', fn ($qt) => $qt->where('employees.id', $employee->id));
                }
            })
            ->orderBy('scheduled_date')
            ->get();

        return view('teknisi.dashboard', [
            'title'               => $isTeknisi ? 'Dashboard Teknisi' : 'Dashboard Kenek',
            'employee'            => $employee,
            'isTeknisi'           => $isTeknisi,
            'jobs'                => $jobs,
            'completedJobs'       => $completedJobs,
            'commissions'         => $commissions,
            'commissionSummary'   => $commissionSummary,
            'isWithdrawalDay'     => $isWithdrawalDay,
            'canRequestWithdrawal'=> $canRequestWithdrawal,
            'pendingWithdrawal'   => $pendingWithdrawal,
            'latestWithdrawal'    => $latestWithdrawal,
            'eligibleUnbatchedTotal' => $eligibleUnbatched->sum('amount'),
            'jobNotifications'    => $jobNotifications,
            'healthBalance'       => $healthBalance,
            'incomingSalesOrders' => $incomingSalesOrders,
            'upcomingBirthdays'   => \App\Models\Employee::upcomingBirthdays($employee->branch_id),
        ]);
    }

    public function acceptJob(Request $request, Customer $customer)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Akun tidak terhubung ke data karyawan.');
        }

        $this->authorizeJobAccess($user, $employee, $customer);

        $invoice = $customer->invoice;

        if (!$invoice || $invoice->work_status !== 'pending') {
            return back()->with('error', 'Order tidak dapat diterima karena status tidak valid.');
        }

        $invoice->update([
            'work_status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $this->cache->invalidateTeknisi($employee->id);

        return back()->with('success', 'Order berhasil diterima. Selamat bekerja!');
    }

    public function showCompleteForm(Request $request, Customer $customer)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Akun tidak terhubung ke data karyawan.');
        }

        $this->authorizeJobAccess($user, $employee, $customer);

        $invoice = $customer->invoice()->with('commissions')->first();

        if (!$invoice || !in_array($invoice->work_status, ['pending', 'accepted'])) {
            return back()->with('error', 'Order tidak dapat diselesaikan karena status tidak valid.');
        }

        $customer->load(['branch:id,name,city', 'tukang:id,name', 'kenek:id,name']);

        return view('teknisi.job-complete', [
            'title'    => 'Konfirmasi Penyelesaian Pekerjaan',
            'customer' => $customer,
            'invoice'  => $invoice,
        ]);
    }

    public function completeJob(Request $request, Customer $customer)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Akun tidak terhubung ke data karyawan.');
        }

        $this->authorizeJobAccess($user, $employee, $customer);

        $invoice = $customer->invoice;

        if (!$invoice || !in_array($invoice->work_status, ['pending', 'accepted'])) {
            return back()->with('error', 'Order tidak dapat diselesaikan karena status tidak valid.');
        }

        $request->validate([
            // 'image' alone also accepts svg/bmp/gif — svg with an embedded
            // <script> would be a stored-XSS vector when opened directly by
            // a supervisor during QC review (audit finding H-11), so pin the
            // allowlist to real raster formats like the other upload handlers.
            'job_photo'       => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'checklist_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ], [
            'job_photo.required'       => 'Foto pekerjaan wajib diunggah.',
            'job_photo.image'          => 'File harus berupa gambar.',
            'job_photo.mimes'          => 'Format foto harus JPG, PNG, atau WEBP.',
            'job_photo.max'            => 'Ukuran foto maksimal 5MB.',
            'checklist_photo.required' => 'Foto checklist wajib diunggah.',
            'checklist_photo.image'    => 'File harus berupa gambar.',
            'checklist_photo.mimes'    => 'Format foto harus JPG, PNG, atau WEBP.',
            'checklist_photo.max'      => 'Ukuran foto checklist maksimal 5MB.',
        ]);

        $jobPhotoPath      = $request->file('job_photo')->store('job-photos', 'public');
        $checklistPhotoPath = $request->file('checklist_photo')->store('job-photos', 'public');

        $invoice->update([
            'work_status'     => 'completed',
            'completed_at'    => now(),
            'job_photo'       => $jobPhotoPath,
            'checklist_photo' => $checklistPhotoPath,
            'qc_status'       => 'pending',
            'qc_notes'        => null,
            'qc_reviewed_by'  => null,
            'qc_reviewed_at'  => null,
        ]);

        app(CommissionEligibilityService::class)->syncByInvoice($invoice);

        $this->cache->invalidateTeknisi($employee->id);
        // Also bust the other installer on this job
        $customer->loadMissing('tukang', 'kenek');
        if ($customer->tukang_id && $customer->tukang_id !== $employee->id) {
            $this->cache->invalidateTeknisi($customer->tukang_id);
        }
        if ($customer->kenek_id && $customer->kenek_id !== $employee->id) {
            $this->cache->invalidateTeknisi($customer->kenek_id);
        }

        return redirect()
            ->route('teknisi.dashboard')
            ->with('success', 'Pekerjaan berhasil diselesaikan! Menunggu pemeriksaan QC sebelum invoice bisa dilunasi.');
    }

    public function requestWithdrawal(Request $request)
    {
        $user     = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Akun tidak terhubung ke data karyawan.');
        }

        $nowWib = Carbon::now('Asia/Jakarta');

        if ($nowWib->day !== 15) {
            return back()->with('error', 'Pengajuan pencairan hanya dapat dilakukan pada tanggal 15 setiap bulan (WIB).');
        }

        // Guard: no pending request this month
        $alreadyPending = CommissionPaymentBatch::query()
            ->where('employee_id', $employee->id)
            ->where('status', 'draft')
            ->whereYear('payment_date', $nowWib->year)
            ->whereMonth('payment_date', $nowWib->month)
            ->exists();

        if ($alreadyPending) {
            return back()->with('error', 'Anda sudah memiliki pengajuan pencairan yang sedang menunggu persetujuan bulan ini.');
        }

        $eligibleCommissions = Commission::query()
            ->where('employees_id', $employee->id)
            ->where('status', 'eligible')
            ->where('is_active', true)
            ->whereNull('commission_payment_batch_id')
            ->get();

        if ($eligibleCommissions->isEmpty()) {
            return back()->with('error', 'Tidak ada komisi yang siap dicairkan saat ini.');
        }

        $request->validate([
            'requester_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $total = $eligibleCommissions->sum('amount');

        DB::transaction(function () use ($employee, &$eligibleCommissions, &$total, $nowWib, $request) {
            // Baca ulang di bawah lock: komisi yang berubah sejak halaman dibuka tidak ikut.
            $eligibleCommissions = Commission::where('employees_id', $employee->id)
                ->where('status', 'eligible')
                ->where('is_active', true)
                ->whereNull('commission_payment_batch_id')
                ->lockForUpdate()
                ->get();
            $total = $eligibleCommissions->sum('amount');

            if ($eligibleCommissions->isEmpty()) {
                return;
            }

            $batchNumber = 'WD-' . $nowWib->format('Ym') . '-' . strtoupper(substr($employee->name, 0, 3)) . '-' . $employee->id;
            // Pengajuan yang pernah ditolak/disetujui pada bulan yang sama tidak boleh bentrok nomor.
            $baseNumber = $batchNumber;
            for ($n = 2; CommissionPaymentBatch::where('batch_number', $batchNumber)->exists(); $n++) {
                $batchNumber = $baseNumber . '-' . $n;
            }

            $batch = CommissionPaymentBatch::create([
                'employee_id'     => $employee->id,
                'batch_number'    => $batchNumber,
                'period_from'     => $nowWib->copy()->startOfMonth()->toDateString(),
                'period_to'       => $nowWib->toDateString(),
                'payment_date'    => $nowWib->toDateString(),
                'status'          => 'draft',
                'total_amount'    => $total,
                'created_by'      => auth()->id(),
                'requester_notes' => $request->input('requester_notes'),
            ]);

            Commission::whereIn('id', $eligibleCommissions->pluck('id'))
                ->update(['commission_payment_batch_id' => $batch->id]);
        });

        $this->cache->invalidateTeknisi($employee->id);

        return back()->with('success', 'Pengajuan pencairan komisi Rp ' . number_format($total) . ' berhasil dikirim. Menunggu persetujuan superadmin.');
    }

    public function komisSaya(Request $request)
    {
        $user     = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Akun Anda belum terhubung ke data karyawan. Hubungi superadmin.');
        }

        $commissions = Commission::query()
            ->with([
                'customer:id,name,vehicle_brand,vehicle_model,plat_number,install_type',
                'invoice:id,invoice_number,status',
                'batch:id,status,batch_number,paid_at',
            ])
            ->where('employees_id', $employee->id)
            ->whereNotIn('status', ['cancelled'])
            ->latest()
            ->paginate(20);

        $summary = [
            'pending_total'  => Commission::where('employees_id', $employee->id)->where('is_active', true)->where('status', 'pending')->sum('amount'),
            'eligible_total' => Commission::where('employees_id', $employee->id)->where('is_active', true)->where('status', 'eligible')->sum('amount'),
            'paid_total'     => Commission::where('employees_id', $employee->id)->where('is_active', true)->where('status', 'paid')->sum('amount'),
        ];

        return view('teknisi.komisi', [
            'title'       => 'Komisi Saya',
            'employee'    => $employee,
            'commissions' => $commissions,
            'summary'     => $summary,
            'isTeknisi'   => $user->hasRole('teknisi'),
        ]);
    }

    public function riwayatPencairan(Request $request)
    {
        $user     = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Akun Anda belum terhubung ke data karyawan. Hubungi superadmin.');
        }

        $batches = CommissionPaymentBatch::query()
            ->with(['commissions'])
            ->where('employee_id', $employee->id)
            ->latest()
            ->paginate(15);

        $totalPaid = CommissionPaymentBatch::where('employee_id', $employee->id)
            ->where('status', 'paid')
            ->sum('total_amount');

        return view('teknisi.pencairan', [
            'title'      => 'Riwayat Pencairan',
            'employee'   => $employee,
            'batches'    => $batches,
            'totalPaid'  => $totalPaid,
            'isTeknisi'  => $user->hasRole('teknisi'),
        ]);
    }

    public function ordersList(Request $request)
    {
        $user     = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Akun Anda belum terhubung ke data karyawan. Hubungi superadmin.');
        }

        $isTeknisi  = $user->hasRole('teknisi');
        $foreignKey = $isTeknisi ? 'tukang_id' : 'kenek_id';

        $status = $request->get('status', 'all');

        $query = Customer::query()
            ->with(['invoice', 'branch:id,name,city', 'tukang:id,name', 'kenek:id,name'])
            ->where($foreignKey, $employee->id)
            ->whereHas('invoice');

        if ($status === 'active') {
            $query->whereHas('invoice', fn($q) => $q->whereIn('work_status', ['pending', 'accepted']));
        } elseif ($status === 'completed') {
            $query->whereHas('invoice', fn($q) => $q->where('work_status', 'completed'));
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        // Commission totals per customer for display
        $customerIds = $orders->pluck('id')->all();
        $commissionMap = \DB::table('commissions')
            ->where('employees_id', $employee->id)
            ->whereIn('customer_id', $customerIds)
            ->whereNotIn('status', ['cancelled'])
            ->select('customer_id', \DB::raw('SUM(amount) as total'), \DB::raw('status'))
            ->groupBy('customer_id', 'status')
            ->get()
            ->groupBy('customer_id');

        // Sales Order yang sudah ditugaskan tapi belum dikonversi jadi Customer/
        // Invoice — sama seperti pratinjau di dashboard (lihat
        // TeknisiDashboardController::dashboard()), supaya "Semua Orderan" juga
        // tidak terasa kosong begitu teknisi baru ditugaskan lewat
        // SalesOrderController::assign(), sebelum admin sempat membuat invoice-nya.
        $incomingSalesOrders = SalesOrder::query()
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where(function ($q) use ($foreignKey, $employee, $isTeknisi) {
                $q->where($foreignKey, $employee->id);
                if ($isTeknisi) {
                    $q->orWhereHas('technicians', fn ($qt) => $qt->where('employees.id', $employee->id));
                }
            })
            ->orderBy('scheduled_date')
            ->get();

        return view('teknisi.orders.index', [
            'title'               => 'Semua Orderan',
            'employee'            => $employee,
            'isTeknisi'           => $isTeknisi,
            'orders'              => $orders,
            'status'              => $status,
            'commissionMap'       => $commissionMap,
            'incomingSalesOrders' => $incomingSalesOrders,
        ]);
    }

    public function orderDetail(Request $request, Customer $customer)
    {
        $user     = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'Akun Anda belum terhubung ke data karyawan. Hubungi superadmin.');
        }

        $isTeknisi  = $user->hasRole('teknisi');
        $foreignKey = $isTeknisi ? 'tukang_id' : 'kenek_id';

        if ((int) $customer->$foreignKey !== (int) $employee->id) {
            abort(403, 'Anda tidak memiliki akses ke order ini.');
        }

        $customer->load(['branch:id,name,city', 'tukang:id,name', 'kenek:id,name']);
        $invoice = $customer->invoice()
            ->with(['commissions' => fn($q) => $q->where('employees_id', $employee->id)])
            ->first();

        $myCommissions = $invoice?->commissions ?? collect();

        return view('teknisi.orders.show', [
            'title'         => 'Detail Order',
            'employee'      => $employee,
            'isTeknisi'     => $isTeknisi,
            'customer'      => $customer,
            'invoice'       => $invoice,
            'myCommissions' => $myCommissions,
        ]);
    }

    private function authorizeJobAccess($user, $employee, Customer $customer): void
    {
        $isTeknisi = $user->hasRole('teknisi');
        $foreignKey = $isTeknisi ? 'tukang_id' : 'kenek_id';

        if ((int) $customer->$foreignKey !== (int) $employee->id) {
            abort(403, 'Anda tidak memiliki akses ke order ini.');
        }
    }
}
