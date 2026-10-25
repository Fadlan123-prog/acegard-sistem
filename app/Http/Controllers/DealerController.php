<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DealerController extends Controller
{
    public function index(Request $request)
    {
        $query = Dealer::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $dealers = $query->ordered()->paginate(20)->withQueryString();

        return view('dealers.index', compact('dealers'));
    }

    public function create()
    {
        return view('dealers.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('dealers', 'public');
        }

        Dealer::create($data);

        return redirect()
            ->route('dealers.index')
            ->with('success', 'Dealer berhasil ditambahkan.');
    }

    public function edit(Dealer $dealer)
    {
        return view('dealers.edit', compact('dealer'));
    }

    public function update(Request $request, Dealer $dealer)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($dealer->image) {
                Storage::disk('public')->delete($dealer->image);
            }
            $data['image'] = $request->file('image')->store('dealers', 'public');
        }

        $dealer->update($data);

        return redirect()
            ->route('dealers.index')
            ->with('success', 'Dealer berhasil diperbarui.');
    }

    public function destroy(Dealer $dealer)
    {
        if ($dealer->image) {
            Storage::disk('public')->delete($dealer->image);
        }

        $dealer->delete();

        return redirect()
            ->route('dealers.index')
            ->with('success', 'Dealer berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'type'            => ['required', 'in:cabang_utama,dealer'],
            'location'        => ['nullable', 'string', 'max:255'],
            'image'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'admin_whatsapp'  => ['nullable', 'string', 'max:50'],
            'maps_url'        => ['nullable', 'url', 'max:255'],
            'instagram_url'   => ['nullable', 'url', 'max:255'],
            'is_active'       => ['nullable', 'boolean'],
            'sort_order'      => ['nullable', 'integer'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
