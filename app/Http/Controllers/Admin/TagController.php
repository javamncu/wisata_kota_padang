<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TagType;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Support\Slug;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class TagController extends Controller
{
    public function index(): View
    {
        $tags = Tag::query()
            ->withCount('destinations')
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(30);

        return view('admin.tags.index', compact('tags'));
    }

    public function create(): View
    {
        return view('admin.tags.form', ['tag' => new Tag()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['slug'] = Slug::unique($data['name'], 'tags');

        Tag::create($data);

        return redirect()->route('admin.tags.index')->with('status', 'Tag dibuat.');
    }

    public function edit(Tag $tag): View
    {
        return view('admin.tags.form', compact('tag'));
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $tag->update($this->validateData($request));

        return redirect()->route('admin.tags.index')->with('status', 'Tag diperbarui.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete(); // pivot rows cascade

        return redirect()->route('admin.tags.index')->with('status', 'Tag dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(TagType::class)],
        ]);
    }
}
