<?php

declare(strict_types=1);

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
            ->latest('published_at')
            ->take(20)
            ->get();

        return response((new Atom($site, $articles))->__toString(), 200, [
            'Content-Type' => 'application/rss+xml',
        ]);
    }
}
