<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MitraController extends Controller
{
    public function index(Request $request)
    {
        $query = Mitra::withCount('invoices');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $mitras = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('mitras.index', compact('mitras'));
    }

    public function create()
    {
        return view('mitras.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'type'     => ['required', Rule::in(['mitra', 'non_franchise'])],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'email'    => ['nullable', 'email', 'max:255'],
            'address'  => ['nullable', 'string'],
            'city'     => ['nullable', 'string', 'max:100'],
            'status'   => ['required', Rule::in(['active', 'inactive'])],
            'notes'    => ['nullable', 'string'],
        ]);

        Mitra::create($data);

        return redirect()
            ->route('mitras.index')
            ->with('success', 'Mitra berhasil ditambahkan.');
    }

    public function edit(Mitra $mitra)
    {
        return view('mitras.edit', compact('mitra'));
    }

    public function update(Request $request, Mitra $mitra)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'type'     => ['required', Rule::in(['mitra', 'non_franchise'])],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'email'    => ['nullable', 'email', 'max:255'],
            'address'  => ['nullable', 'string'],
            'city'     => ['nullable', 'string', 'max:100'],
            'status'   => ['required', Rule::in(['active', 'inactive'])],
            'notes'    => ['nullable', 'string'],
        ]);

        $mitra->update($data);

        return redirect()
            ->route('mitras.index')
            ->with('success', 'Mitra berhasil diperbarui.');
    }

    public function destroy(Mitra $mitra)
    {
        if ($mitra->invoices()->exists()) {
            return back()->with('error',
                'Mitra tidak bisa dihapus karena masih memiliki invoice terkait.'
            );
        }

        $mitra->delete();

        return redirect()
            ->route('mitras.index')
            ->with('success', 'Mitra berhasil dihapus.');
    }
}
