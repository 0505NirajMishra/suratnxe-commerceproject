<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ProcessProductImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public array $backoff = [10, 30, 60];

    /** @param array<int, string> $paths */
    public function __construct(
        public int $productId,
        public array $paths,
    ) {
        $this->onQueue('product-images');
    }

    public function handle(): void
    {
        $product = Product::findOrFail($this->productId);
        $product->forceFill(['image_processing_status' => 'processing'])->save();

        $disk = Storage::disk('public');
        $validated = [];

        foreach ($this->paths as $path) {
            if (! $disk->exists($path)) {
                throw new RuntimeException("Uploaded product image is missing: {$path}");
            }

            // Read only image headers; do not decode/re-encode the full-resolution image.
            $absolutePath = $disk->path($path);
            $imageInfo = @getimagesize($absolutePath);
            if ($imageInfo === false) {
                throw new RuntimeException("Uploaded product image is not readable: {$path}");
            }

            $validated[] = $path;
        }

        DB::transaction(function () use ($product, $validated) {
            $sortOrder = (int) ($product->images()->max('sort_order') ?? -1);

            foreach ($validated as $path) {
                // Makes the job safe to retry without creating duplicate image rows.
                $exists = $product->images()->where('path', $path)->exists();
                if (! $exists) {
                    $product->images()->create([
                        'path' => $path,
                        'sort_order' => ++$sortOrder,
                    ]);
                }
            }

            $product->forceFill(['image_processing_status' => 'completed'])->save();
        });
    }

    public function failed(Throwable $exception): void
    {
        Product::whereKey($this->productId)
            ->update(['image_processing_status' => 'failed']);
    }
}
