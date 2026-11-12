<?php

namespace App\Http\Controllers;

use App\Enums\AdoptionStatus;
use App\Models\AdoptionListing;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $productCount = Product::query()->where('is_active', true)->count();
        $vetCount = User::query()->where('role', 'vet')->count();
        $adoptableCount = AdoptionListing::query()->where('status', AdoptionStatus::Available)->count();

        return view('pages.home', compact(
            'productCount',
            'vetCount',
            'adoptableCount'
        ));
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }
}
