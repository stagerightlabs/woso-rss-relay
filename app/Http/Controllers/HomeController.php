<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Relay\Sites\Catalog;

class HomeController extends Controller
{
    /**
     * Display the home page.
     */
    public function __invoke(Catalog $catalog): View
    {
        $sites = $catalog->sorted();

        return view('home', [
            'sites' => $sites,
        ]);
    }
}
