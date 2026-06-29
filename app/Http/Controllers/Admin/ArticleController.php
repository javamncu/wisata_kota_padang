<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Destination;
use App\Support\Slug;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Mews\Purifier\Facades\Purifier;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $articles = Article::query()
            ->with('author')
            ->withCount('destinations')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->input('q').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.articles.index', [
            'articles' => $articles,
            'statuses' => ArticleStatus::options(),
        ]);
    }

    public function create(): View
    {
        return view('admin.articles.form', $this->formData(new Article(['status' => ArticleStatus::Draft])));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['slug'] = Slug::unique($data['title'], 'articles');
        $data['author_id'] = $request->user()->id;
        $data['body'] = Purifier::clean($request->input('body'));
        $data['published_at'] = $this->resolvePublishedAt($data);
        $data['cover_image'] = $this->storeCover($request);
        $destinationIds = $data['destinations'] ?? [];
        unset($data['destinations']);

        $article = Article::create($data);
        $article->destinations()->sync($destinationIds);

        return redirect()->route('admin.articles.index')->with('status', 'Artikel dibuat.');
    }

    public function edit(Article $article): View
    {
        $article->load('destinations');

        return view('admin.articles.form', $this->formData($article));
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['body'] = Purifier::clean($request->input('body'));
        $data['published_at'] = $this->resolvePublishedAt($data, $article);
        if ($cover = $this->storeCover($request, $article)) {
            $data['cover_image'] = $cover;
        }
        $destinationIds = $data['destinations'] ?? [];
        unset($data['destinations']);

        $article->update($data);
        $article->destinations()->sync($destinationIds);

        return redirect()->route('admin.articles.index')->with('status', 'Artikel diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        if ($article->cover_image) {
            @unlink(public_path($article->cover_image));
        }
        $article->delete();

        return redirect()->route('admin.articles.index')->with('status', 'Artikel dihapus.');
    }

    public function togglePublish(Article $article): RedirectResponse
    {
        $publishing = $article->status !== ArticleStatus::Published;

        $article->update([
            'status' => $publishing ? ArticleStatus::Published : ArticleStatus::Draft,
            'published_at' => $publishing && $article->published_at === null ? now() : $article->published_at,
        ]);

        return back()->with('status', $publishing ? 'Artikel dipublikasikan.' : 'Artikel dijadikan draft.');
    }

    // -- helpers --------------------------------------------------------

    private function formData(Article $article): array
    {
        return [
            'article' => $article,
            'statuses' => ArticleStatus::options(),
            'destinations' => Destination::orderBy('name')->get(['id', 'name']),
            'selectedDestinations' => $article->exists ? $article->destinations->pluck('id')->all() : [],
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['required', 'string'],
            'cover' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', Rule::in(ArticleStatus::values())],
            'published_at' => ['nullable', 'date'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'destinations' => ['array'],
            'destinations.*' => ['integer', 'exists:destinations,id'],
        ]);
    }

    /** Default published_at to "now" the first time an article is published. */
    private function resolvePublishedAt(array $data, ?Article $article = null): ?string
    {
        if (! empty($data['published_at'])) {
            return $data['published_at'];
        }

        if ($data['status'] === ArticleStatus::Published->value) {
            return $article?->published_at?->toDateTimeString() ?? now()->toDateTimeString();
        }

        return $article?->published_at?->toDateTimeString();
    }

    private function storeCover(Request $request, ?Article $article = null): ?string
    {
        if (! $request->hasFile('cover')) {
            return null;
        }

        $dir = public_path('images/articles');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if ($article && $article->cover_image) {
            @unlink(public_path($article->cover_image));
        }

        $file = $request->file('cover');
        $name = 'article-'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move($dir, $name);

        return 'images/articles/'.$name;
    }
}
