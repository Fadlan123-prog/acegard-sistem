<?php

namespace App\Http\Controllers;

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

}
