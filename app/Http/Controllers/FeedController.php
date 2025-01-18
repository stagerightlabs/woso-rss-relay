<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Relay\Article;
use Relay\Atom;
use Relay\Feed;

class FeedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Feed $feed): Response
    {
        $articles = Article::where('feed', $feed)
            ->orderBy('published_at')
            ->get();

        return response((new Atom($feed, $articles)), 200, [
            'Content-Type' => 'text/xml',
        ]);
    }
}
