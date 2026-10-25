<?php

namespace App\Http\Controllers;

use App\Models\InternationalPartner;
use Illuminate\Http\Request;

class InternationalPartnerController extends Controller
{
    public function index(Request $request)
    {
        $partners = InternationalPartner::query()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('international-partners.index', compact('partners'));
    }

    public function create()
    {
        return view('international-partners.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        InternationalPartner::create($data);

        return redirect()->route('international-partners.index')->with('success', 'Partner internasional berhasil ditambahkan.');
    }

    public function edit(InternationalPartner $internationalPartner)
    {
        return view('international-partners.edit', ['partner' => $internationalPartner]);
    }

    public function update(Request $request, InternationalPartner $internationalPartner)
    {
        $internationalPartner->update($this->validated($request));

        return redirect()->route('international-partners.index')->with('success', 'Partner internasional berhasil diperbarui.');
    }

    public function destroy(InternationalPartner $internationalPartner)
    {
        if ($internationalPartner->importOrders()->exists() || $internationalPartner->exportOrders()->exists()) {
            return back()->with('error', 'Partner tidak bisa dihapus karena masih memiliki riwayat order.');
        }

        $internationalPartner->delete();

        return back()->with('success', 'Partner internasional berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type'             => ['required', 'in:supplier,buyer'],
            'name'             => ['required', 'string', 'max:255'],
            'country'          => ['required', 'string', 'max:255'],
            'contact_person'   => ['nullable', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:50'],
            'email'            => ['nullable', 'email', 'max:255'],
            'default_currency' => ['required', 'string', 'size:3'],
        ]);
    }
}
