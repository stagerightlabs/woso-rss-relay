<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Relay\Article;
use Relay\Sites\Catalog;
use Relay\Transform\Atom;

class FeedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $site, Catalog $catalog): Response
    {
        $site = $catalog->find(strtolower($site));
        if (!$site) {
            abort(404);
        }

        $articles = Article::where('site', $site->slug())
            ->orderBy('published_at')
            ->get();

        return response((new Atom($site, $articles)), 200, [
            'Content-Type' => 'text/xml',
        ]);
    }
}
