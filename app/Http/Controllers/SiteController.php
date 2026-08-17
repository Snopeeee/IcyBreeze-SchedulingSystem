<?php

namespace App\Http\Controllers;

use App\Models\AirconUnitType;
use App\Models\Service;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function home(): View
    {
        return view('home', [
            'service' => Service::bookable()->firstOrFail(),
            'unitTypes' => AirconUnitType::bookable()->get(),
        ]);
    }

    public function page(string $page): View
    {
        abort_unless(in_array($page, ['services', 'how-it-works', 'coverage', 'faq', 'contact'], true), 404);

        return view('pages.'.$page, [
            'service' => Service::bookable()->firstOrFail(),
            'unitTypes' => AirconUnitType::bookable()->get(),
        ]);
    }
}
