<?php

namespace App\Providers;

use App\Filesystem\CloudinaryUrlAdapter;
use Cloudinary\Cloudinary as CloudinarySdk;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // cloudinary-labs/cloudinary-laravel (the "official" Laravel wrapper) doesn't
        // support Laravel 13 yet (^11|^12 only), so this disk is wired directly on
        // top of the framework-agnostic cloudinary/cloudinary_php SDK + a plain
        // Flysystem adapter instead — same end result (Storage::disk('cloudinary')),
        // no dependency on an unmaintained-for-our-version wrapper.
        Storage::extend('cloudinary', function ($app, array $config) {
            $client = new CloudinarySdk($config['url'] ?? env('CLOUDINARY_URL'));

            $adapter = new CloudinaryUrlAdapter($client);

            return new FilesystemAdapter(new Filesystem($adapter, $config), $adapter, $config);
        });
    }
}
