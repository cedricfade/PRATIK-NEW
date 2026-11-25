<?php

namespace App\Http\Controllers\Immobilier;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PageController extends Controller
{
    /** /immobilier/financement : simulateur de prêt (partenariat BHCI de l'ancien site). */
    public function financement(): View
    {
        return view('immobilier.pages.financement');
    }
}
