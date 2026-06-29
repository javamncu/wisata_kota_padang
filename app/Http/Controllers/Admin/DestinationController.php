<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CocokUntuk;
use App\Enums\Duration;
use App\Enums\IndoorOutdoor;
use App\Enums\PriceRange;
use App\Enums\Status;
use App\Enums\TagType;
use App\Enums\WaktuIdeal;
use App\Enums\Zone;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Tag;
use App\Support\Slug;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DestinationController extends Controller
{
    public function index(Request $request): View
    {
        $destinations = Destination::query()
            ->with('category')
            ->withCount('reviews')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->input('q').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('category'), fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->input('category'))))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.destinations.index', [
            'destinations' => $destinations,
            'categories' => Category::orderBy('name')->get(),
            'statuses' => Status::options(),
        ]);
    }

    public function create(): View
    {
        return view('admin.destinations.form', $this->formData(new Destination(['status' => Status::Draft])));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['slug'] = Slug::unique($data['name'], 'destinations');

        $destination = Destination::create($data);

        $this->syncTags($request, $destination);
        $this->storeImages($request, $destination);

        return redirect()->route('admin.destinations.index')->with('status', 'Destinasi dibuat.');
    }

    public function edit(Destination $destination): View
    {
        $destination->load(['images', 'tags']);

        return view('admin.destinations.form', $this->formData($destination));
    }

    public function update(Request $request, Destination $destination): RedirectResponse
    {
        $data = $this->validateData($request);
        $destination->update($data);

        $this->syncTags($request, $destination);
        $this->deleteImages($request, $destination);
        $this->storeImages($request, $destination);

        return redirect()->route('admin.destinations.index')->with('status', 'Destinasi diperbarui.');
    }

    public function destroy(Destination $destination): RedirectResponse
    {
        $destination->delete(); // soft delete

        return redirect()->route('admin.destinations.index')->with('status', 'Destinasi dihapus.');
    }

    public function toggleStatus(Destination $destination): RedirectResponse
    {
        $destination->update([
            'status' => $destination->status === Status::Aktif ? Status::Draft : Status::Aktif,
        ]);

        return back()->with('status', 'Status destinasi diperbarui.');
    }

    // -- helpers --------------------------------------------------------

    private function formData(Destination $destination): array
    {
        return [
            'destination' => $destination,
            'categories' => Category::orderBy('name')->get(),
            'tagsByType' => Tag::orderBy('name')->get()->groupBy(fn (Tag $t) => $t->type->value),
            'enums' => [
                'price' => PriceRange::options(),
                'zone' => Zone::options(),
                'io' => IndoorOutdoor::options(),
                'duration' => Duration::options(),
                'cocok' => CocokUntuk::options(),
                'waktu' => WaktuIdeal::options(),
                'status' => Status::options(),
            ],
            'selectedTagIds' => $destination->exists ? $destination->tags->pluck('id')->all() : [],
            'openingHoursText' => $this->openingHoursToText($destination->opening_hours),
        ];
    }

    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description_short' => ['required', 'string', 'max:500'],
            'description_long' => ['required', 'string'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'price_info' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_instagram' => ['nullable', 'string', 'max:100'],
            'contact_website' => ['nullable', 'url', 'max:255'],
            'price_range' => ['required', Rule::in(PriceRange::values())],
            'zone' => ['required', Rule::in(Zone::values())],
            'indoor_outdoor' => ['required', Rule::in(IndoorOutdoor::values())],
            'duration' => ['required', Rule::in(Duration::values())],
            'status' => ['required', Rule::in(Status::values())],
            'cocok' => ['array'],
            'cocok.*' => [Rule::in(CocokUntuk::values())],
            'waktu' => ['array'],
            'waktu.*' => [Rule::in(WaktuIdeal::values())],
            'opening_hours' => ['nullable', 'string', 'max:2000'],
            'images.*' => ['image', 'max:4096'],
        ]);

        // Map request keys to model columns.
        $validated['cocok_untuk'] = $request->input('cocok', []);
        $validated['waktu_ideal'] = $request->input('waktu', []);
        $validated['opening_hours'] = $this->parseOpeningHours($request->input('opening_hours'));
        unset($validated['cocok'], $validated['waktu']);

        return $validated;
    }

    private function syncTags(Request $request, Destination $destination): void
    {
        $ids = collect($request->input('tags', []))
            ->filter()
            ->map(fn ($id) => (int) $id);

        // On-the-fly tag creation, one comma-separated field per type.
        foreach (TagType::cases() as $type) {
            $ids = $ids->merge($this->createTags($request->input('new_'.$type->value), $type));
        }

        $destination->tags()->sync($ids->unique()->all());
    }

    /** @return int[] */
    private function createTags(?string $csv, TagType $type): array
    {
        if (! $csv) {
            return [];
        }

        return collect(explode(',', $csv))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->map(function (string $name) use ($type) {
                return Tag::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name, 'type' => $type],
                )->id;
            })
            ->all();
    }

    private function storeImages(Request $request, Destination $destination): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $dir = public_path('images/destinations');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $order = (int) $destination->images()->max('sort_order');

        foreach ($request->file('images') as $file) {
            $name = $destination->slug.'-'.uniqid().'.'.$file->getClientOriginalExtension();
            $file->move($dir, $name);
            $destination->images()->create([
                'path' => 'images/destinations/'.$name,
                'sort_order' => ++$order,
            ]);
        }
    }

    private function deleteImages(Request $request, Destination $destination): void
    {
        $ids = $request->input('delete_images', []);

        if (empty($ids)) {
            return;
        }

        foreach ($destination->images()->whereIn('id', $ids)->get() as $image) {
            @unlink(public_path($image->path));
            $image->delete();
        }
    }

    private function parseOpeningHours(?string $text): ?array
    {
        if (! $text) {
            return null;
        }

        $result = [];

        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $result[trim($k)] = trim($v);
            } else {
                $result['Info'] = $line;
            }
        }

        return $result ?: null;
    }

    private function openingHoursToText(?array $hours): string
    {
        if (! $hours) {
            return '';
        }

        return collect($hours)->map(fn ($v, $k) => "{$k}: {$v}")->implode("\n");
    }
}
