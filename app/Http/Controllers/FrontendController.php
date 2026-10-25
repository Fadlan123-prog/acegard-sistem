<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Dealer;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function home(){
        return view('frontend.home.index');
    }

    public function serviceCar(){
        return view('frontend.service.car');
    }

    public function serviceBuilding(){
        return view('frontend.service.building');
    }

    public function serviceFranchise(){
        return view('frontend.service.franchise');
    }

    public function about(){
        return view('frontend.about.index');
    }

    public function contact(){
        return view('frontend.contact.index');
    }

    public function kacaFilmMobil(){
        return view('promotion.car');
    }

    public function ppfAds(){
        return view('frontend.landing.ppf-ads');
    }

    public function kacaFilmRumah(){
        return view('frontend.landing.kaca-film-rumah');
    }

    public function ppfCatalog(){
        return view('frontend.ppf.index');
    }

    public function dealers(){
        $cabangUtama = Dealer::active()->ordered()->where('type', 'cabang_utama')->get();
        $dealers = Dealer::active()->ordered()->where('type', 'dealer')->get();

        return view('frontend.dealers.index', compact('cabangUtama', 'dealers'));
    }

    public function faq(){
        return view('frontend.faq.index');
    }

    public function articles(Request $request){
        $category = $request->query('category');

        $featured = Article::published()->latestFirst()->first();

        $articles = Article::published()
            ->category($category)
            ->when($featured, fn ($q) => $q->where('id', '!=', $featured->id))
            ->latestFirst()
            ->paginate(9)
            ->withQueryString();

        return view('frontend.artikel.index', [
            'featured' => $featured,
            'articles' => $articles,
            'categories' => Article::categories(),
            'activeCategory' => $category,
        ]);
    }

    public function articleShow(Article $article){
        abort_unless($article->is_published, 404);

        return view('frontend.artikel.show', compact('article'));
    }

}
