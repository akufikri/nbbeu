<?php

namespace App\Filesystem;

use Cloudinary\Asset\AssetType;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Http;
use League\Flysystem\FileAttributes;
use League\Flysystem\UnableToCheckFileExistence;
use League\Flysystem\UnableToRetrieveMetadata;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use League\MimeTypeDetection\MimeTypeDetector;
use ThomasVantuycom\FlysystemCloudinary\CloudinaryAdapter;

/**
 * The vendor adapter's publicUrl() calls Cloudinary's Admin API
 * (adminApi()->asset(...)) to look up the secure_url — a live HTTP request
 * on every single image render. Cloudinary URLs are 100% deterministic from
 * cloud_name + resource_type + public_id, so there's no need to ask their
 * API for it; doing so was adding a network round-trip per image and made
 * every page with images (landing page, admin tables) painfully slow.
 *
 * This override builds the URL locally instead, matching the parent
 * adapter's own resourceType()/publicId() logic (dynamicFolders mode).
 */
class CloudinaryUrlAdapter extends CloudinaryAdapter
{
    private Cloudinary $urlClient;

    private MimeTypeDetector $localMimeTypeDetector;

    /** @var array<string, array{exists: bool, size: ?int, mimeType: ?string}> */
    private array $headCache = [];

    public function __construct(Cloudinary $client, ?MimeTypeDetector $mimeTypeDetector = null, bool $dynamicFolders = true)
    {
        parent::__construct($client, $mimeTypeDetector, $dynamicFolders);

        $this->urlClient = $client;
        $this->localMimeTypeDetector = $mimeTypeDetector ?? new FinfoMimeTypeDetector();
    }

    public function getUrl(string $path): string
    {
        $resourceType = $this->resourceTypeFor($path);
        $publicId = $this->publicIdFor($path, $resourceType);

        return match ($resourceType) {
            AssetType::VIDEO => (string) $this->urlClient->video($publicId)->toUrl(),
            AssetType::RAW => (string) $this->urlClient->raw($publicId)->toUrl(),
            default => (string) $this->urlClient->image($publicId)->toUrl(),
        };
    }

    /**
     * The parent adapter's fileExists()/fileSize()/mimeType() all call
     * Cloudinary's Admin API (~1s each, per call) — a HEAD request straight
     * to the CDN URL gets the same info from an edge server in well under
     * 100ms, and mime type doesn't need a network call at all.
     */
    public function fileExists(string $path): bool
    {
        try {
            return $this->head($path)['exists'];
        } catch (\Throwable $e) {
            throw UnableToCheckFileExistence::forLocation($path, $e);
        }
    }

    public function mimeType(string $path): FileAttributes
    {
        $mimeType = $this->localMimeTypeDetector->detectMimeTypeFromPath($path);

        if ($mimeType === null) {
            throw UnableToRetrieveMetadata::mimeType($path);
        }

        return new FileAttributes($path, mimeType: $mimeType);
    }

    public function fileSize(string $path): FileAttributes
    {
        try {
            $size = $this->head($path)['size'];
        } catch (\Throwable $e) {
            throw UnableToRetrieveMetadata::fileSize($path, $e->getMessage(), $e);
        }

        if ($size === null) {
            throw UnableToRetrieveMetadata::fileSize($path, 'File not found or no Content-Length header.');
        }

        return new FileAttributes($path, fileSize: $size);
    }

    /**
     * @return array{exists: bool, size: ?int, mimeType: ?string}
     */
    private function head(string $path): array
    {
        if (isset($this->headCache[$path])) {
            return $this->headCache[$path];
        }

        $response = Http::timeout(5)->connectTimeout(2)->head($this->getUrl($path));

        return $this->headCache[$path] = [
            'exists' => $response->successful(),
            'size' => $response->hasHeader('Content-Length') ? (int) $response->header('Content-Length') : null,
            'mimeType' => $response->header('Content-Type'),
        ];
    }

    private function resourceTypeFor(string $path): string
    {
        $imageExtensions = ['3ds', 'ai', 'arw', 'avif', 'bmp', 'bw', 'cr2', 'cr3', 'djvu', 'dng', 'eps', 'eps3', 'ept', 'fbx', 'flif', 'gif', 'glb', 'gltf', 'hdp', 'heic', 'heif', 'ico', 'indd', 'jp2', 'jpe', 'jpeg', 'jpg', 'jxl', 'jxr', 'obj', 'pdf', 'ply', 'png', 'ps', 'psd', 'svg', 'tga', 'tif', 'tiff', 'u3ma', 'usdz', 'wdp', 'webp'];
        $videoExtensions = ['3g2', '3gp', 'avi', 'flv', 'm2ts', 'mkv', 'mov', 'mp4', 'mpeg', 'mts', 'mxf', 'ogv', 'ts', 'webm', 'wmv', 'aac', 'aiff', 'amr', 'flac', 'm4a', 'mp3', 'ogg', 'opus', 'wav'];

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, $imageExtensions, true)) {
            return AssetType::IMAGE;
        }

        if (in_array($extension, $videoExtensions, true)) {
            return AssetType::VIDEO;
        }

        return AssetType::RAW;
    }

    private function publicIdFor(string $path, string $resourceType): string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $resourceType === AssetType::RAW ? "{$filename}.{$extension}" : $filename;
    }
}
