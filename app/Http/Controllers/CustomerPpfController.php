<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CustomerPpf;
use App\Models\SalesOrder;
use App\Models\CustomerPpfInstallation;
use App\Models\Employee;
use App\Models\PpfProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use Carbon\Carbon;

class CustomerPpfController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : $user->branches()->orderBy('name')->get();

        $query = CustomerPpf::query()
            ->with([
                'branch:id,name',
                'installations.ppfProduct:id,name',
                'installations.technicians:id,name',
                'installations.invoice',
            ]);

        if (!$user->hasRole('superadmin')) {
            $allowedBranchIds = $user->branches()->pluck('branches.id');
            $query->whereIn('branch_id', $allowedBranchIds);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('wsn', 'like', "%{$s}%")
                  ->orWhere('card_number', 'like', "%{$s}%")
                  ->orWhere('vehicle_plate', 'like', "%{$s}%");
            });
        }

        $customers = $query->latest()->paginate(10)->withQueryString();

        return view('customer.ppf.index', compact('customers', 'branches'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        // ambil cabang dari pivot branch_user
        $userBranches = $user->branches()->orderBy('name')->get();

        // superadmin bisa pilih cabang, selain itu auto-lock ke cabang user
        $lockBranch = !$user->hasRole('superadmin');

        // default branch: pakai cabang pertama dari pivot
        $defaultBranchId = $userBranches->first()?->id;

        // list branches untuk dropdown
        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : $userBranches;

        $employees = Employee::orderBy('name')->get();
        $ppfProducts = PpfProduct::orderBy('name')->get();

        $fromSo = $request->filled('from_so')
            ? SalesOrder::find($request->integer('from_so'))
            : null;

        $fromLead = $fromSo ? null : app(\App\Services\LeadConversion::class)->find($request->integer('from_lead') ?: null);

        return view('customer.ppf.create', compact(
            'fromLead',
            'branches',
            'employees',
            'ppfProducts',
            'lockBranch',
            'defaultBranchId',
            'fromSo',
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // branch dari pivot branch_user
        $allowedBranchIds = $user->hasRole('superadmin')
            ? Branch::pluck('id')->all()
            : $user->branches()->pluck('branches.id')->all();

        // daftar key panel yang valid (harus sama dengan blade)
        $panelKeys = [
            'hood','front_bumper','rear_bumper','fender_left','fender_right',
            'door_fl','door_fr','door_rl','door_rr','mirror_left','mirror_right',
            'roof','trunk','side_skirt_left','side_skirt_right',
        ];

        $validator = Validator::make($request->all(), [
            'branch_id' => ['required', Rule::in($allowedBranchIds)],

            'wsn'    => ['required','string','max:255'],
            'name'   => ['required','string','max:255'],
            'phone'  => ['required','string','max:255'],
            'email'  => ['nullable','email','max:255'],
            'address'=> ['required','string','max:255'],
            'city'   => ['required','string','max:255'],
            'country'=> ['nullable','string','max:255'],

            'vehicle_brand' => ['required','string','max:255'],
            'vehicle_model' => ['required','string','max:255'],
            'vehicle_year'  => ['required','string','max:10'],
            'vehicle_color' => ['required','string','max:255'],
            'vehicle_plate' => ['required','string','max:255'],

            'marketing_id'  => ['nullable','exists:employees,id'],

            // installations array
            'installations' => ['required','array','min:1'],

            'installations.*.ppf_product_id' => ['required','exists:ppf_products,id'],
            'installations.*.install_date'   => ['required','date'],

            // ✅ ini yang sebelumnya bikin error karena field belum ada di blade
            'installations.*.install_type'   => ['required', Rule::in(['fullbody','partial'])],

            'installations.*.warranty_years' => ['required','integer', Rule::in([5,7,8])],

            // teknisi max 4
            'installations.*.technicians'    => ['required','array','min:1','max:4'],
            'installations.*.technicians.*'  => ['required','exists:employees,id'],

            // panels (opsional, tapi validasinya kita cek manual untuk partial)
            'installations.*.panels'         => ['nullable','array'],
            'installations.*.panels.*'       => ['nullable', Rule::in($panelKeys)],

            'installations.*.coverage_override'   => ['nullable','string'],
            'installations.*.exclusions_override' => ['nullable','string'],
            'installations.*.notes'              => ['nullable','string'],

            // password konfirmasi admin
            'admin_password' => ['required','string'],
        ]);

        // ✅ validasi tambahan: kalau install_type=partial, panels wajib minimal 1
        $validator->after(function($v) use ($request) {
            $installs = $request->input('installations', []);
            foreach ($this->ppfTeamLimitErrors($installs) as $field => $msg) {
                $v->errors()->add($field, $msg);
            }
            foreach ($installs as $i => $ins) {
                $type = $ins['install_type'] ?? null;
                $panels = $ins['panels'] ?? [];
                if ($type === 'partial' && empty($panels)) {
                    $v->errors()->add("installations.$i.panels", "Panel wajib dipilih untuk pemasangan partial.");
                }
            }
        });

        $data = $validator->validate();

        // cek password admin
        if (!Hash::check($data['admin_password'], $user->password)) {
            return back()->withInput()->with('error', 'Password admin salah.');
        }

        DB::transaction(function () use ($data, $panelKeys, &$customer) {

            // ✅ card_number auto-generate (tidak divalidasi required)
            $cardNumber = $this->generateCardNumber($data['branch_id']);

            $customer = CustomerPpf::create([
                'branch_id'      => $data['branch_id'],
                'wsn'            => $data['wsn'],
                'card_number'    => $cardNumber,
                'name'           => $data['name'],
                'phone'          => $data['phone'],
                'email'          => $data['email'],
                'address'        => $data['address'],
                'city'           => $data['city'],
                'country'        => $data['country'] ?? 'Indonesia',
                'vehicle_brand'  => $data['vehicle_brand'],
                'vehicle_model'  => $data['vehicle_model'],
                'vehicle_year'   => $data['vehicle_year'],
                'vehicle_color'  => $data['vehicle_color'],
                'vehicle_plate'  => $data['vehicle_plate'],
                'marketing_id'   => $data['marketing_id'] ?? null,
            ]);

            foreach ($data['installations'] as $ins) {

                // ✅ warranty_code auto-generate (tidak dari form)
                $warrantyCode = $this->generateWarrantyCode();

                $installDate   = Carbon::parse($ins['install_date'])->toDateString();
                $warrantyYears = (int) $ins['warranty_years'];
                $warrantyEnd   = Carbon::parse($installDate)->addYears($warrantyYears)->toDateString();

                // panels
                $panels = $ins['panels'] ?? [];

                // kalau fullbody dan user tidak centang apa-apa, anggap semua panel
                if (($ins['install_type'] ?? '') === 'fullbody' && empty($panels)) {
                    $panels = $panelKeys;
                }

                $installation = CustomerPpfInstallation::create([
                    'customer_ppf_id' => $customer->id,
                    'ppf_product_id'  => $ins['ppf_product_id'],
                    'branch_id'       => $data['branch_id'],
                    'install_type'    => $ins['install_type'],
                    'install_date'    => $installDate,

                    'warranty_code'   => $warrantyCode,
                    'warranty_start'  => $installDate,
                    'warranty_years'  => $warrantyYears,
                    'warranty_end'    => $warrantyEnd,
                    'warranty_status' => 'active',

                    // ✅ ini yang sering lupa: harus disimpan ke kolom json
                    'panels'          => array_values($panels),

                    'coverage_override'   => $ins['coverage_override'] ?? null,
                    'exclusions_override' => $ins['exclusions_override'] ?? null,
                    'notes'               => $ins['notes'] ?? null,
                ]);

                // pivot technicians
                $installation->technicians()->sync($ins['technicians']);
            }
        });

        if (! $request->filled('from_so')) {
            app(\App\Services\LeadConversion::class)->markConverted($request->integer('from_lead') ?: null, $customer);
        }

        if ($request->filled('from_so')) {
            $so = SalesOrder::find($request->integer('from_so'));
            if ($so && $so->isCompleted() && !$so->isConverted()) {
                $so->update([
                    'converted_to_type' => CustomerPpf::class,
                    'converted_to_id'   => $customer->id,
                    'converted_at'      => now(),
                ]);
            }
        }

        return redirect()
            ->route('ppf-customers.index')
            ->with('success', 'Customer PPF berhasil ditambahkan.');
    }

    public function edit(CustomerPpf $ppfCustomer)
    {
        $user = auth()->user();

        // Cek akses cabang (non-superadmin hanya boleh akses cabang miliknya)
        if (!$user->hasRole('superadmin')) {
            $allowedBranchIds = $user->branches()->pluck('branches.id')->all();
            abort_unless(in_array($ppfCustomer->branch_id, $allowedBranchIds), 403);
        }

        $ppfCustomer->load([
            'branch:id,name',
            'installations.ppfProduct:id,name',
            'installations.technicians:id,name',
            'marketing:id,name',
        ]);

        $userBranches = $user->branches()->orderBy('name')->get();
        $lockBranch   = !$user->hasRole('superadmin');

        $branches = $user->hasRole('superadmin')
            ? Branch::orderBy('name')->get()
            : $userBranches;

        $defaultBranchId = $ppfCustomer->branch_id;

        $employees    = Employee::orderBy('name')->get();
        $ppfProducts  = PpfProduct::orderBy('name')->get();

        // daftar key panel yang valid (harus sama dengan blade)
        $panelKeys = [
            'hood','front_bumper','rear_bumper','fender_left','fender_right',
            'door_fl','door_fr','door_rl','door_rr','mirror_left','mirror_right',
            'roof','trunk','side_skirt_left','side_skirt_right',
        ];

        return view('customer.ppf.edit', compact(
            'ppfCustomer',
            'branches',
            'employees',
            'ppfProducts',
            'lockBranch',
            'defaultBranchId',
            'panelKeys'
        ));
    }

    public function update(Request $request, CustomerPpf $ppfCustomer)
    {
        $user = auth()->user();

        // Cek akses cabang (non-superadmin hanya boleh akses cabang miliknya)
        if (!$user->hasRole('superadmin')) {
            $allowedBranchIds = $user->branches()->pluck('branches.id')->all();
            abort_unless(in_array($ppfCustomer->branch_id, $allowedBranchIds), 403);
        }

        // branch yang boleh dipilih
        $allowedBranchIds = $user->hasRole('superadmin')
            ? Branch::pluck('id')->all()
            : $user->branches()->pluck('branches.id')->all();

        $panelKeys = [
            'hood','front_bumper','rear_bumper','fender_left','fender_right',
            'door_fl','door_fr','door_rl','door_rr','mirror_left','mirror_right',
            'roof','trunk','side_skirt_left','side_skirt_right',
        ];

        $validator = Validator::make($request->all(), [
            'branch_id' => ['required', Rule::in($allowedBranchIds)],

            'wsn'    => ['required','string','max:255'],
            'name'   => ['required','string','max:255'],
            'phone'  => ['required','string','max:255'],
            'email'  => ['nullable','email','max:255'],
            'address'=> ['required','string','max:255'],
            'city'   => ['required','string','max:255'],
            'country'=> ['nullable','string','max:255'],

            'vehicle_brand' => ['required','string','max:255'],
            'vehicle_model' => ['required','string','max:255'],
            'vehicle_year'  => ['required','string','max:10'],
            'vehicle_color' => ['required','string','max:255'],
            'vehicle_plate' => ['required','string','max:255'],

            'marketing_id'  => ['nullable','exists:employees,id'],

            // installations array
            'installations' => ['required','array','min:1'],

            // id installation (optional untuk update)
            'installations.*.id' => ['nullable','integer'],

            'installations.*.ppf_product_id' => ['required','exists:ppf_products,id'],
            'installations.*.install_date'   => ['required','date'],
            'installations.*.install_type'   => ['required', Rule::in(['fullbody','partial'])],
            'installations.*.warranty_years' => ['required','integer', Rule::in([5,7,8])],

            'installations.*.technicians'    => ['required','array','min:1','max:4'],
            'installations.*.technicians.*'  => ['required','exists:employees,id'],

            'installations.*.panels'         => ['nullable','array'],
            'installations.*.panels.*'       => ['nullable', Rule::in($panelKeys)],

            'installations.*.coverage_override'   => ['nullable','string'],
            'installations.*.exclusions_override' => ['nullable','string'],
            'installations.*.notes'               => ['nullable','string'],

            // password konfirmasi admin
            'admin_password' => ['required','string'],
        ]);

        // validasi tambahan:
        // - partial wajib pilih minimal 1 panel
        // - kalau installations.*.id ada, pastikan milik customer ini
        $validator->after(function($v) use ($request, $ppfCustomer) {
            $installs = $request->input('installations', []);
            foreach ($this->ppfTeamLimitErrors($installs) as $field => $msg) {
                $v->errors()->add($field, $msg);
            }
            foreach ($installs as $i => $ins) {
                $type   = $ins['install_type'] ?? null;
                $panels = $ins['panels'] ?? [];

                if ($type === 'partial' && empty($panels)) {
                    $v->errors()->add("installations.$i.panels", "Panel wajib dipilih untuk pemasangan partial.");
                }

                if (!empty($ins['id'])) {
                    $exists = $ppfCustomer->installations()->where('id', $ins['id'])->exists();
                    if (!$exists) {
                        $v->errors()->add("installations.$i.id", "Installation tidak valid untuk customer ini.");
                    }
                }
            }
        });

        $data = $validator->validate();

        // lock branch untuk non-superadmin (biar gak bisa pindah cabang)
        if (!$user->hasRole('superadmin')) {
            $data['branch_id'] = $ppfCustomer->branch_id;
        }

        // cek password admin
        if (!Hash::check($data['admin_password'], $user->password)) {
            return back()->withInput()->with('error', 'Password admin salah.');
        }

        DB::transaction(function () use ($data, $panelKeys, $ppfCustomer) {

            // Update customer utama (card_number TIDAK diubah)
            $ppfCustomer->update([
                'branch_id'      => $data['branch_id'],
                'wsn'            => $data['wsn'],
                'name'           => $data['name'],
                'phone'          => $data['phone'],
                'email'          => $data['email'],
                'address'        => $data['address'],
                'city'           => $data['city'],
                'country'        => $data['country'] ?? 'Indonesia',
                'vehicle_brand'  => $data['vehicle_brand'],
                'vehicle_model'  => $data['vehicle_model'],
                'vehicle_year'   => $data['vehicle_year'],
                'vehicle_color'  => $data['vehicle_color'],
                'vehicle_plate'  => $data['vehicle_plate'],
                'marketing_id'   => $data['marketing_id'] ?? null,
            ]);

            $keepInstallationIds = [];

            foreach ($data['installations'] as $ins) {

                $installDate   = Carbon::parse($ins['install_date'])->toDateString();
                $warrantyYears = (int) $ins['warranty_years'];
                $warrantyEnd   = Carbon::parse($installDate)->addYears($warrantyYears)->toDateString();

                $panels = $ins['panels'] ?? [];

                // fullbody: kalau kosong, anggap semua panel
                if (($ins['install_type'] ?? '') === 'fullbody' && empty($panels)) {
                    $panels = $panelKeys;
                }

                $payload = [
                    'ppf_product_id'  => $ins['ppf_product_id'],
                    'branch_id'       => $data['branch_id'],
                    'install_type'    => $ins['install_type'],
                    'install_date'    => $installDate,
                    'warranty_start'  => $installDate,
                    'warranty_years'  => $warrantyYears,
                    'warranty_end'    => $warrantyEnd,
                    'panels'          => array_values($panels),
                    'coverage_override'   => $ins['coverage_override'] ?? null,
                    'exclusions_override' => $ins['exclusions_override'] ?? null,
                    'notes'               => $ins['notes'] ?? null,
                ];

                // Update existing atau create baru
                if (!empty($ins['id'])) {
                    $installation = $ppfCustomer->installations()->where('id', $ins['id'])->firstOrFail();

                    // jangan ubah warranty_code & warranty_status secara paksa
                    $installation->fill($payload);
                    $installation->save();
                } else {
                    $installation = CustomerPpfInstallation::create(array_merge($payload, [
                        'customer_ppf_id' => $ppfCustomer->id,
                        'warranty_code'   => $this->generateWarrantyCode(),
                        'warranty_status' => 'active',
                    ]));
                }

                $installation->technicians()->sync($ins['technicians']);
                $keepInstallationIds[] = $installation->id;
            }

            // Hapus installations yang di-remove dari form
            $toDelete = $ppfCustomer->installations()
                ->whereNotIn('id', $keepInstallationIds)
                ->get();

            foreach ($toDelete as $installation) {
                $installation->technicians()->detach();
                $installation->delete();
            }
        });

        return redirect()
            ->route('ppf-customers.index')
            ->with('success', 'Customer PPF berhasil diupdate.');
    }

    private function generateCardNumber(int $branchId): string
    {
        // Format: PPF-{BRANCH}-{YYYYMMDD}-{SEQ}
        $prefix = "PPF-{$branchId}-" . date('Ymd') . "-";

        $last = CustomerPpf::where('card_number', 'like', $prefix . '%')
            ->orderBy('card_number', 'desc')
            ->value('card_number');

        $seq = 1;
        if ($last) {
            $lastSeq = (int) substr($last, -4);
            $seq = $lastSeq + 1;
        }

        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }

    private function generateWarrantyCode(): string
    {
        // Format: W-{YYYYMMDD}-{RANDOM}
        // (kalau mau 100% unik, tambahkan unique index di DB)
        return 'W-' . date('Ymd') . '-' . strtoupper(Str::random(8));
    }

    public function destroy(CustomerPpf $ppfCustomer)
    {
        $user = auth()->user();
        if (!$user->hasRole('superadmin')) {
            $allowedBranchIds = $user->branches()->pluck('branches.id')->all();
            abort_unless(in_array($ppfCustomer->branch_id, $allowedBranchIds), 403);
        }

        try {
            DB::transaction(function () use ($ppfCustomer) {
                // aman untuk case tanpa cascade / butuh trigger event
                $ppfCustomer->installations()->delete();
                $ppfCustomer->delete();
            });

            return redirect()
                ->route('ppf-customers.index')
                ->with('success', 'Customer PPF berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus Customer PPF: ' . $e->getMessage());
        }
    }



    /**
     * Maks 3 teknisi PPF (jabatan bukan Detailer) per instalasi; detailer tidak dihitung.
     *
     * @return array<string,string>  key field => pesan
     */
    private function ppfTeamLimitErrors(array $installations): array
    {
        $errors = [];
        $max = (int) config('commission.ppf.full_team_size', 3);

        foreach ($installations as $i => $ins) {
            $ids = array_map('intval', (array) ($ins['technicians'] ?? []));
            $teknisi = \App\Models\Employee::whereIn('id', $ids)
                ->where(fn ($q) => $q->whereNull('job_position')->orWhere('job_position', 'not like', '%detailer%'))
                ->count();

            if ($teknisi > $max) {
                $errors["installations.$i.technicians"] = "Maksimal {$max} teknisi PPF per instalasi (detailer tidak dihitung).";
            }
        }

        return $errors;
    }
}
