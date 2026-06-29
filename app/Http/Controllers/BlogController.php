<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(): View
    {
        $articles = Article::query()
            ->published()
            ->with('author')
            ->latest('published_at')
            ->latest()
            ->paginate(9);

        return view('public.blog.index', compact('articles'));
    }

    public function show(Request $request, Article $article): View
    {
        // Drafts are hidden from the public (admins may preview).
        if (! $article->isPublished() && ! $request->user()?->isAdmin()) {
            abort(404);
        }

        $article->load(['author', 'destinations.category', 'destinations.images']);

        $others = Article::query()
            ->published()
            ->whereKeyNot($article->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('public.blog.show', compact('article', 'others'));
    }
}
