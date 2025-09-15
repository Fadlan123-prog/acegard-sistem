<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gallery;
use App\Models\Branch;

class GalleryController extends Controller
{
    public function index(Request $request){
        $query = Gallery::query();

        // Filter berdasarkan keyword pencarian (nama)
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Ambil user & session cabang
        $user = auth()->user();
        $branchId = session('active_branch_id');

        // Filter cabang HANYA jika bukan superadmin
        if ($user->hasRole('superadmin')) {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        } else {
            // Admin biasa hanya bisa melihat cabang aktifnya
            $query->where('branch_id', $activeBranchId);
        }

        // Ambil semua cabang (hanya jika perlu tampil di filter UI)
        $branches = Branch::all();

        // Ambil data customer dengan paginasi
        $gallery = $query->latest()->paginate(10);
        return view('gallery.index',[
            'gallery' => $gallery,
            'branches' => $branches,
        ]);
    }
}
