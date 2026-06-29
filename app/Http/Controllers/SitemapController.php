<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Destination;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('explore'), 'priority' => '0.8'],
            ['loc' => route('map.index'), 'priority' => '0.5'],
            ['loc' => route('quiz.index'), 'priority' => '0.5'],
            ['loc' => route('blog.index'), 'priority' => '0.7'],
            ['loc' => route('about'), 'priority' => '0.3'],
        ];

        foreach (Destination::active()->get() as $d) {
            $urls[] = ['loc' => route('destinations.show', $d), 'priority' => '0.7', 'lastmod' => $d->updated_at->toAtomString()];
        }

        foreach (Article::published()->get() as $a) {
            $urls[] = ['loc' => route('blog.show', $a), 'priority' => '0.6', 'lastmod' => $a->updated_at->toAtomString()];
        }

        return response()
            ->view('sitemap', compact('urls'))
            ->header('Content-Type', 'text/xml');
    }
}
