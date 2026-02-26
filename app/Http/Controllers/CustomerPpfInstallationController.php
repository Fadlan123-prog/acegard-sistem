<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CustomerPpf;
use App\Models\CustomerPpfInstallation;
use App\Models\Employee;
use App\Models\PpfProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CustomerPpfInstallationController extends Controller
{
    public function index()
    {
        $installations = CustomerPpfInstallation::with([
                'customerPpf',
                'ppfProduct',
                'branch',
                'tukang',
                'kenek',
                'technicians:id,name',
            ])
            ->latest()
            ->paginate(20);

        return view('ppf.installations.index', compact('installations'));
    }

    public function create()
    {
        $customers = CustomerPpf::orderBy('name')->get();
        $products  = PpfProduct::orderBy('brand')->orderBy('name')->get();
        $branches  = Branch::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();

        return view('ppf.installations.create', compact(
            'customers', 'products', 'branches', 'employees'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_ppf_id' => ['required', 'exists:customer_ppf,id'],
            'ppf_product_id'  => ['required', 'exists:ppf_products,id'],
            'branch_id'       => ['required', 'exists:branches,id'],

            'install_type'    => ['required', 'in:fullbody,partial'],
            'install_date'    => ['required', 'date'],

            'tukang_id'       => ['nullable', 'exists:employees,id'],
            'kenek_id'        => ['nullable', 'exists:employees,id'],

            'warranty_code'   => ['required', 'string', 'max:255', 'unique:customer_ppf_installations,warranty_code'],
            'warranty_start'  => ['required', 'date'],
            'warranty_years'  => ['nullable', 'integer', 'min:1'],
            'warranty_status' => ['required', 'in:active,expired,void'],

            // panels & technicians (opsional tapi biasanya dipakai)
            'panels'          => ['nullable', 'array'],
            'panels.*'        => ['string', 'max:80'],

            'technicians'     => ['nullable', 'array', 'max:4'],
            'technicians.*'   => ['integer', 'exists:employees,id'],

            // ✅ upload image langsung root (bukan installations.*)
            'checklist_image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'job_order_image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],

            'notes'           => ['nullable', 'string'],
        ]);

        // technicians dipisah karena bukan kolom table installations (pivot)
        $techIds = $data['technicians'] ?? [];
        unset($data['technicians']);

        // kalau warranty_years kosong → pakai default dari product
        if (empty($data['warranty_years'])) {
            $product = PpfProduct::findOrFail($data['ppf_product_id']);
            $data['warranty_years'] = (int) ($product->default_warranty_years ?? 5);
        }

        // hitung warranty_end
        $start = Carbon::parse($data['warranty_start']);
        $data['warranty_end'] = $start->copy()->addYears((int)$data['warranty_years'])->toDateString();

        // ✅ upload checklist/job order (jadikan path string)
        if ($request->hasFile('checklist_image')) {
            $data['checklist_image'] = $request->file('checklist_image')->store('ppf/docs', 'public');
        }

        if ($request->hasFile('job_order_image')) {
            $data['job_order_image'] = $request->file('job_order_image')->store('ppf/docs', 'public');
        }

        $installation = CustomerPpfInstallation::create($data);

        // ✅ sync pivot technicians
        if (method_exists($installation, 'technicians')) {
            $installation->technicians()->sync($techIds);
        }

        return redirect()
            ->route('ppf-installations.index')
            ->with('success', 'Pemasangan PPF + garansi berhasil dibuat.');
    }

    public function edit(CustomerPpfInstallation $ppf_installation)
    {
        $customers = CustomerPpf::orderBy('name')->get();
        $products  = PpfProduct::orderBy('brand')->orderBy('name')->get();
        $branches  = Branch::orderBy('name')->get();
        $employees = Employee::orderBy('name')->get();

        return view('ppf.installations.edit', [
            'installation' => $ppf_installation->load('technicians:id,name'),
            'customers'    => $customers,
            'products'     => $products,
            'branches'     => $branches,
            'employees'    => $employees,
        ]);
    }

    public function update(Request $request, CustomerPpfInstallation $ppf_installation)
    {
        $data = $request->validate([
            'customer_ppf_id' => ['required', 'exists:customer_ppf,id'],
            'ppf_product_id'  => ['required', 'exists:ppf_products,id'],
            'branch_id'       => ['required', 'exists:branches,id'],

            'install_type'    => ['required', 'in:fullbody,partial'],
            'install_date'    => ['required', 'date'],

            'tukang_id'       => ['nullable', 'exists:employees,id'],
            'kenek_id'        => ['nullable', 'exists:employees,id'],

            'warranty_code'   => ['required', 'string', 'max:255', 'unique:customer_ppf_installations,warranty_code,' . $ppf_installation->id],
            'warranty_start'  => ['required', 'date'],
            'warranty_years'  => ['nullable', 'integer', 'min:1'],
            'warranty_status' => ['required', 'in:active,expired,void'],

            'panels'          => ['nullable', 'array'],
            'panels.*'        => ['string', 'max:80'],

            'technicians'     => ['nullable', 'array', 'max:4'],
            'technicians.*'   => ['integer', 'exists:employees,id'],

            'checklist_image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'job_order_image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],

            'notes'           => ['nullable', 'string'],
        ]);

        $techIds = $data['technicians'] ?? [];
        unset($data['technicians']);

        if (empty($data['warranty_years'])) {
            $product = PpfProduct::findOrFail($data['ppf_product_id']);
            $data['warranty_years'] = (int) ($product->default_warranty_years ?? 5);
        }

        $start = Carbon::parse($data['warranty_start']);
        $data['warranty_end'] = $start->copy()->addYears((int)$data['warranty_years'])->toDateString();

        // ✅ replace file jika upload baru
        if ($request->hasFile('checklist_image')) {
            if ($ppf_installation->checklist_image) {
                Storage::disk('public')->delete($ppf_installation->checklist_image);
            }
            $data['checklist_image'] = $request->file('checklist_image')->store('ppf/docs', 'public');
        }

        if ($request->hasFile('job_order_image')) {
            if ($ppf_installation->job_order_image) {
                Storage::disk('public')->delete($ppf_installation->job_order_image);
            }
            $data['job_order_image'] = $request->file('job_order_image')->store('ppf/docs', 'public');
        }

        $ppf_installation->update($data);

        if (method_exists($ppf_installation, 'technicians')) {
            $ppf_installation->technicians()->sync($techIds);
        }

        return redirect()
            ->route('ppf-installations.index')
            ->with('success', 'Pemasangan PPF + garansi berhasil diperbarui.');
    }

    public function destroy(CustomerPpfInstallation $ppf_installation)
    {
        // detach technicians
        if (method_exists($ppf_installation, 'technicians')) {
            $ppf_installation->technicians()->detach();
        }

        // delete images
        if ($ppf_installation->checklist_image) {
            Storage::disk('public')->delete($ppf_installation->checklist_image);
        }
        if ($ppf_installation->job_order_image) {
            Storage::disk('public')->delete($ppf_installation->job_order_image);
        }

        $ppf_installation->delete();

        return redirect()
            ->route('ppf-installations.index')
            ->with('success', 'Pemasangan PPF + garansi berhasil dihapus.');
    }
}
