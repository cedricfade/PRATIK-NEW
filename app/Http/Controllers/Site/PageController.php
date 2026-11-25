<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PageController extends Controller
{
    public function cgu(): View
    {
        return view('site.pages.cgu');
    }

    public function confidentialite(): View
    {
        return view('site.pages.confidentialite');
    }
}
