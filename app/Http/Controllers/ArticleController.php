<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $articles = $query->latestFirst()->paginate(20)->withQueryString();

        return view('articles.index', compact('articles'));
    }

    public function create()
    {
        return view('articles.create', ['categories' => Article::categories()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['title']);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('articles', 'public');
        }

        Article::create($data);

        return redirect()
            ->route('articles.index')
            ->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit(Article $article)
    {
        return view('articles.edit', ['article' => $article, 'categories' => Article::categories()]);
    }

    public function update(Request $request, Article $article)
    {
        $data = $this->validated($request);

        if ($data['title'] !== $article->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $article->id);
        }

        if ($request->hasFile('cover_image')) {
            if ($article->cover_image) {
                Storage::disk('public')->delete($article->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('articles', 'public');
        }

        $article->update($data);

        return redirect()
            ->route('articles.index')
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article)
    {
        if ($article->cover_image) {
            Storage::disk('public')->delete($article->cover_image);
        }

        $article->delete();

        return redirect()
            ->route('articles.index')
            ->with('success', 'Artikel berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'category'      => ['required', 'in:' . implode(',', array_keys(Article::categories()))],
            'excerpt'       => ['nullable', 'string', 'max:500'],
            'body'          => ['nullable', 'string'],
            'cover_image'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_published'  => ['nullable', 'boolean'],
            'published_at'  => ['nullable', 'date'],
        ]);

        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (Article::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
