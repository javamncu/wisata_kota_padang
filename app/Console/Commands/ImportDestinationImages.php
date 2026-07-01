<?php

namespace App\Console\Commands;

use App\Models\Destination;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ImportDestinationImages extends Command
{
    protected $signature = 'destinations:import-images
        {--source=public/images/destinasi : Folder berisi gambar (relatif ke base path)}';

    protected $description = 'Impor gambar destinasi dari folder, cocokkan ke destinasi, lalu attach.';

    private const EXT_BY_TYPE = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_WEBP => 'webp',
    ];

    public function handle(): int
    {
        $sourceDir = base_path($this->option('source'));

        if (! is_dir($sourceDir)) {
            $this->error("Folder tidak ditemukan: {$sourceDir}");

            return self::FAILURE;
        }

        $targetDir = public_path('images/destinations');
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $destinations = Destination::all();
        $bySlug = $destinations->keyBy('slug');

        $files = collect(scandir($sourceDir))
            ->reject(fn ($f) => in_array($f, ['.', '..'], true))
            ->reject(fn ($f) => Str::lower(pathinfo($f, PATHINFO_FILENAME)) === 'hero')
            ->values();

        $matched = 0;
        $unmatched = [];

        foreach ($files as $file) {
            $fullPath = $sourceDir.DIRECTORY_SEPARATOR.$file;

            $info = @getimagesize($fullPath);
            if ($info === false || ! isset(self::EXT_BY_TYPE[$info[2]])) {
                $this->warn("Lewati (bukan gambar valid): {$file}");
                continue;
            }
            $ext = self::EXT_BY_TYPE[$info[2]];

            $fileSlug = Str::slug(pathinfo($file, PATHINFO_FILENAME));
            $destination = $this->matchDestination($fileSlug, $destinations, $bySlug);

            if (! $destination) {
                $unmatched[] = $file;
                continue;
            }

            // Idempotent: drop any existing gallery images for this destination.
            // Query fresh each time so repeated files for the same destination
            // don't leave stale duplicates.
            foreach ($destination->images()->get() as $old) {
                @unlink(public_path($old->path));
                $old->delete();
            }

            $relative = "images/destinations/{$destination->slug}.{$ext}";
            copy($fullPath, public_path($relative));

            $destination->images()->create(['path' => $relative, 'sort_order' => 0]);

            $this->line("  ✓ {$file}  →  {$destination->name}");
            $matched++;
        }

        $this->newLine();
        $this->info("Selesai: {$matched} gambar ter-attach.");

        if ($unmatched) {
            $this->warn('File tidak cocok ke destinasi mana pun:');
            foreach ($unmatched as $f) {
                $this->line("  - {$f}");
            }
        }

        $without = $destinations->filter(fn (Destination $d) => $d->images()->count() === 0);
        if ($without->isNotEmpty()) {
            $this->warn("Destinasi tanpa gambar ({$without->count()}):");
            foreach ($without as $d) {
                $this->line("  - {$d->name} ({$d->slug})");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Match by slug: exact first, then a destination whose slug extends the
     * file slug (handles shortened filenames like "Kota Tua Padang").
     */
    private function matchDestination(string $fileSlug, Collection $destinations, Collection $bySlug): ?Destination
    {
        if ($bySlug->has($fileSlug)) {
            return $bySlug->get($fileSlug);
        }

        $candidates = $destinations->filter(
            fn (Destination $d) => str_starts_with($d->slug, $fileSlug.'-')
        );

        if ($candidates->count() === 1) {
            return $candidates->first();
        }

        // Reverse: filename has extra words beyond the destination slug.
        $reverse = $destinations
            ->filter(fn (Destination $d) => str_starts_with($fileSlug, $d->slug.'-'))
            ->sortByDesc(fn (Destination $d) => strlen($d->slug));

        return $reverse->count() >= 1 ? $reverse->first() : null;
    }
}
