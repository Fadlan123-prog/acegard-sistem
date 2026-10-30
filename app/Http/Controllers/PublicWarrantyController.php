<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;                 // mobil film
use App\Models\CustomerBuilding;         // building film
use App\Models\CustomerPpf;              // ppf customer
use App\Models\CustomerPpfInstallation;  // ppf installation
use App\Models\WarrantyClaim;
use App\Models\WarrantyClaimItem;
use App\Models\User;
use App\Notifications\WarrantyClaimSubmitted;
use Illuminate\Support\Facades\Notification;
use Barryvdh\DomPDF\Facade\Pdf;

class PublicWarrantyController extends Controller
{
    public function form()
    {
        return view('public.warranty.form');
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'card_number' => ['required','string','max:255'],
            'phone_last4' => ['required','digits:4'],
        ]);

        $card = trim($data['card_number']);
        $last4 = $data['phone_last4'];
        $matches = [];

        // 1) Mobil film (customers.card_number)
        $car = Customer::with(['branch','customerProducts.part','customerProducts.product','customerProducts.categoryProduct'])
            ->where('card_number', $card)
            ->first();

        if ($car && $this->phoneMatches($car->phone_number, $last4)) {
            $matches[] = [
                'type' => 'car_film',
                'id'   => $car->id,
                'label'=> 'Kaca Film Mobil',
                'summary' => ($car->plat_number ?? '-') . ' | ' . ($car->name ?? '-'),
            ];
        }

        // 2) Building film (customer_building.card_number)
        $bld = CustomerBuilding::with(['branch'])
            ->where('card_number', $card)
            ->first();

        if ($bld && $this->phoneMatches($bld->phone_number, $last4)) {
            $matches[] = [
                'type' => 'building_film',
                'id'   => $bld->id,
                'label'=> 'Kaca Film Gedung',
                'summary' => ($bld->city ?? '-') . ' | ' . ($bld->name ?? '-'),
            ];
        }

        // 3) PPF (customer_ppf.card_number -> tampilkan semua installations)
        $ppfCustomer = CustomerPpf::with([
                'installations.ppfProduct',
                'installations.technicians:id,name',
                'branch'
            ])
            ->where('card_number', $card)
            ->first();

        if ($ppfCustomer && $this->phoneMatches($ppfCustomer->phone, $last4)) {
            $matches[] = [
                'type' => 'ppf',
                'id'   => $ppfCustomer->id, // catatan: ini customer_ppf id
                'label'=> 'PPF',
                'summary' => ($ppfCustomer->vehicle_plate ?? '-') . ' | ' . ($ppfCustomer->name ?? '-'),
            ];
        }

        if (count($matches) === 0) {
            // Pesan sama untuk kartu salah / HP salah: tidak membocorkan keberadaan kartu.
            return back()->with('error', 'Data garansi tidak ditemukan. Periksa Card Number dan 4 digit terakhir nomor HP.')->withInput($request->only('card_number'));
        }

        foreach ($matches as $m) {
            $this->markVerified($m['type'], (int) $m['id']);
        }

        // kalau ketemu lebih dari 1 (card number dipakai lintas kategori), tampilkan pick
        if (count($matches) > 1) {
            return view('public.warranty.pick', compact('matches', 'card'));
        }

        // 1 match -> langsung ke result
        $hit = $matches[0];
        return redirect()->route('warranty.result', [
            'type' => $hit['type'],
            'id'   => $hit['id'],
            'card_number' => $card,
        ]);
    }

    public function result(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:car_film,building_film,ppf'],
            'id'   => ['required', 'integer'],
            'card_number' => ['required', 'string', 'max:255'],
        ]);

        $type = $request->type;
        $id   = (int) $request->id;
        $card = trim($request->card_number);

        $this->requireVerified($type, $id);

        if ($type === 'car_film') {
            $data = Customer::with([
                    'branch',
                    'customerProducts.part',
                    'customerProducts.product',
                    'customerProducts.categoryProduct'
                ])
                ->where('id', $id)
                ->where('card_number', $card)
                ->firstOrFail();

        } elseif ($type === 'building_film') {
            $data = CustomerBuilding::with([
                'branch',
                'customerBuildingProducts.product',
                'customerBuildingProducts.categoryProductBuilding',
            ])
            ->where('id', $id)
            ->where('card_number', $card)
            ->firstOrFail();

            $buildingProduct = $data->customerBuildingProduct;

            if ($buildingProduct instanceof \Illuminate\Database\Eloquent\Collection) {
                $buildingProduct = $buildingProduct
                    ->sortByDesc('warantee_start')
                    ->first();
            }

            if ($buildingProduct) {
                $data->setAttribute('warantee_start', $buildingProduct->warantee_start);
                $data->setAttribute('warantee_end', $buildingProduct->warantee_end);
                $data->setAttribute('warantee_duration', $buildingProduct->warantee_duration);

                if (
                    empty($data->warantee_end) &&
                    !empty($data->warantee_start) &&
                    !empty($data->warantee_duration)
                ) {
                    $data->setAttribute(
                        'warantee_end',
                        \Carbon\Carbon::parse($data->warantee_start)
                            ->addYears((int) $data->warantee_duration)
                            ->toDateString()
                    );
                }
            }

        } else {
            $data = CustomerPpf::with([
                    'branch',
                    'installations.ppfProduct',
                    'installations.technicians:id,name'
                ])
                ->where('id', $id)
                ->where('card_number', $card)
                ->firstOrFail();
        }

        return view('public.warranty.result', compact('type', 'data', 'card'));
    }

    public function storeClaim(Request $request)
    {
        $data = $request->validate([
            'card_number' => ['required','string','max:255'],
            'type' => ['required','in:car_film,building_film,ppf'],
            'id'   => ['required','integer'],
            'complaint' => ['required','string','max:2000'],

            // car film: customer_product ids (opsional)
            'car_items' => ['nullable','array'],
            'car_items.*' => ['integer'],

            // ppf: harus pilih installation + panels
            'installation_id' => ['nullable','integer'],
            'ppf_panels' => ['nullable','array'],
            'ppf_panels.*' => ['string','max:80'],
        ]);

        $card = trim($data['card_number']);
        $type = $data['type'];

        $this->requireVerified($type, (int) $data['id']);


        if ($type === 'building_film') {
            $b = CustomerBuilding::where('id',$data['id'])->where('card_number',$card)->firstOrFail();

            $claim = $this->createClaim([
                'claim_date'   => now()->toDateString(),
                'warranty_type'=> 'building_film',
                'warrantable_type' => get_class($b),
                'warrantable_id'   => $b->id,
                'branch_id'    => $b->branch_id ?? null,
                'card_number'  => $card,
                'cause'        => 'unknown',
                'status'       => 'submitted',
                'is_chargeable'=> false,
                'quote_total'  => 0,
                'complaint'    => $data['complaint'],
            ]);

            $this->notifyAdmins($claim);

            // building claim = per customer building (otomatis 1 item)
            WarrantyClaimItem::create([
                'warranty_claim_id' => $claim->id,
                'item_type' => 'building_customer',
                'item_label'=> 'Claim Garansi Kaca Film Gedung',
                'action'    => 'replace',
                'charge_amount' => 0,
            ]);

        } elseif ($type === 'car_film') {
            $c = Customer::with(['customerProducts.part','customerProducts.product','customerProducts.categoryProduct'])
                ->where('id',$data['id'])->where('card_number',$card)->firstOrFail();

            $claim = $this->createClaim([
                'claim_date'   => now()->toDateString(),
                'warranty_type'=> 'car_film',
                'warrantable_type' => get_class($c),
                'warrantable_id'   => $c->id,
                'branch_id'    => $c->branch_id ?? null,
                'card_number'  => $card,
                'cause'        => 'unknown',
                'status'       => 'submitted',
                'is_chargeable'=> false,
                'quote_total'  => 0,
                'complaint'    => $data['complaint'],
            ]);

            $this->notifyAdmins($claim);

            // kalau customerProducts ada, simpan item yang dipilih; kalau tidak ada, buat item general
            $selected = collect($data['car_items'] ?? [])->map(fn($v)=>(int)$v)->all();
            $cp = $c->customerProducts ?? collect();

            if ($cp->count() && count($selected)) {
                foreach ($cp as $row) {
                    if (!in_array((int)$row->id, $selected)) continue;
                    $label = trim(($row->part->name ?? '').' | '.($row->categoryProduct->name ?? '').' | '.($row->product->name ?? ''));
                    WarrantyClaimItem::create([
                        'warranty_claim_id' => $claim->id,
                        'item_type' => 'car_product',
                        'item_id'   => $row->id,
                        'item_label'=> $label,
                        'action'    => 'replace',
                        'charge_amount' => 0,
                    ]);
                }
            } else {
                WarrantyClaimItem::create([
                    'warranty_claim_id' => $claim->id,
                    'item_type' => 'car_product',
                    'item_label'=> 'Claim Garansi (Umum)',
                    'action'    => 'replace',
                    'charge_amount' => 0,
                ]);
            }

        } else { // ppf
            // id = customer_ppf.id
            $ppfCustomer = CustomerPpf::with(['installations'])
                ->where('id',$data['id'])->where('card_number',$card)->firstOrFail();

            $installationId = (int)($data['installation_id'] ?? 0);
            $panels = $data['ppf_panels'] ?? [];

            if (!$installationId || empty($panels)) {
                return back()->with('error', 'Pilih instalasi & panel yang ingin diklaim.')->withInput();
            }

            $ins = CustomerPpfInstallation::with(['customer'])
                ->where('id',$installationId)
                ->where('customer_ppf_id', $ppfCustomer->id)
                ->firstOrFail();

            // validasi panel harus termasuk panels pemasangan
            $installedPanels = is_array($ins->panels)
                ? $ins->panels
                : (json_decode($ins->panels ?? '[]', true) ?: []);

            foreach ($panels as $p) {
                if (!in_array($p, $installedPanels)) {
                    return back()->with('error', "Panel {$p} tidak termasuk panel pemasangan.")->withInput();
                }
            }

            $claim = $this->createClaim([
                'claim_date'   => now()->toDateString(),
                'warranty_type'=> 'ppf',
                'warrantable_type' => get_class($ins),
                'warrantable_id'   => $ins->id,
                'branch_id'    => $ins->branch_id ?? null,
                'card_number'  => $card,
                'cause'        => 'unknown',
                'status'       => 'submitted',
                'is_chargeable'=> false,
                'quote_total'  => 0,
                'complaint'    => $data['complaint'],
            ]);

            $this->notifyAdmins($claim);

            $labels = $this->ppfPanelLabels();
            foreach ($panels as $panelKey) {
                WarrantyClaimItem::create([
                    'warranty_claim_id' => $claim->id,
                    'item_type' => 'ppf_panel',
                    'item_key'  => $panelKey,
                    'item_label'=> $labels[$panelKey] ?? $panelKey,
                    'action'    => 'replace',
                    'charge_amount' => 0,
                ]);
            }
        }

        return redirect()->route('warranty.form')
            ->with('success', "Claim berhasil dikirim. Nomor Claim: {$claim->claim_number}");
    }

    private function phoneMatches(?string $phone, string $last4): bool
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        return strlen($digits) >= 4 && hash_equals(substr($digits, -4), $last4);
    }

    private function markVerified(string $type, int $id): void
    {
        session()->put('warranty_verified', array_values(array_unique(
            array_merge((array) session('warranty_verified', []), ["{$type}:{$id}"])
        )));
    }

    private function requireVerified(string $type, int $id): void
    {
        abort_unless(
            in_array("{$type}:{$id}", (array) session('warranty_verified', []), true),
            403,
            'Silakan cek garansi lewat halaman pencarian terlebih dahulu.'
        );
    }

    /** Nomor klaim = MAX+1 per bulan; diulang bila dua pelanggan mengirim klaim bersamaan. */
    protected function createClaim(array $attributes): WarrantyClaim
    {
        $docs = app(\App\Services\DocumentNumberService::class);
        $prefix = 'CLM-' . now()->format('Ym') . '-';

        return $docs->retryOnDuplicate(fn () => WarrantyClaim::create(array_merge(
            ['claim_number' => $docs->nextSequential('warranty_claims', 'claim_number', $prefix)],
            $attributes
        )));
    }

    protected function ppfPanelLabels(): array
    {
        return [
            'hood' => 'Kap Mesin',
            'front_bumper' => 'Bumper Depan',
            'rear_bumper' => 'Bumper Belakang',
            'fender_left' => 'Fender Kiri',
            'fender_right' => 'Fender Kanan',
            'door_fl' => 'Pintu Depan Kiri',
            'door_fr' => 'Pintu Depan Kanan',
            'door_rl' => 'Pintu Belakang Kiri',
            'door_rr' => 'Pintu Belakang Kanan',
            'mirror_left' => 'Spion Kiri',
            'mirror_right' => 'Spion Kanan',
            'roof' => 'Atap',
            'trunk' => 'Bagasi',
            'side_skirt_left' => 'Side Skirt Kiri',
            'side_skirt_right' => 'Side Skirt Kanan',
        ];
    }

    protected function notifyAdmins(WarrantyClaim $claim): void
    {
        // Target role yang mau dapat notifikasi (ubah sesuai role kamu)
        $roles = ['superadmin', 'admin', 'cs'];

        // 1) semua superadmin/admin/cs (global)
        $global = User::query()
            ->whereHas('roles', fn($q) => $q->whereIn('name', $roles))
            ->get();

        // 2) user yang punya branch sesuai claim (via pivot branch_user)
        $branchUsers = collect();
        if (!empty($claim->branch_id)) {
            $branchUsers = User::query()
                ->whereHas('roles', fn($q) => $q->whereIn('name', $roles))
                ->whereHas('branches', fn($q) => $q->where('branches.id', $claim->branch_id))
                ->get();
        }

        $recipients = $global->merge($branchUsers)->unique('id');

        if ($recipients->count()) {
            Notification::send($recipients, new WarrantyClaimSubmitted($claim));
        }
    }

    public function downloadCard(Request $request, string $type, int $id)
{
    $request->validate([
        'card_number' => ['required','string','max:255'],
    ]);

    $card = trim($request->card_number);

    if (!in_array($type, ['car_film','building_film','ppf'], true)) {
        abort(404);
    }

    $this->requireVerified($type, $id);

    // Load data sesuai type (harus match id + card_number)
    if ($type === 'car_film') {
        $data = Customer::with(['branch','customerProducts.part','customerProducts.product','customerProducts.categoryProduct'])
            ->where('id', $id)->where('card_number', $card)->firstOrFail();
    } elseif ($type === 'building_film') {
        $data = CustomerBuilding::with(['branch'])
            ->where('id', $id)->where('card_number', $card)->firstOrFail();
    } else { // ppf
        $data = CustomerPpf::with(['branch','installations.ppfProduct','installations.technicians:id,name'])
            ->where('id', $id)->where('card_number', $card)->firstOrFail();
    }

    $filename = "ACEGARD-WARRANTY-CARD-{$type}-{$card}.pdf";

    return Pdf::setOptions([
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ])
        ->loadView('public.warranty.card', [
            'type' => $type,
            'data' => $data,
            'card' => $card,
        ])
        ->download($filename);
}

}
