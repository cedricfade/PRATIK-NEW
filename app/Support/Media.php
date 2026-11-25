<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Résout les URLs des images clients (logos, biens, unes de journaux…).
 *
 * Les fichiers gardent l'arborescence de l'ancien site : images/Logos,
 * images/ImagesImmobiliers, etc. Si un fichier n'est pas présent sur ce
 * serveur, l'URL pointe vers l'ancien site (PRATIK_MEDIA_FALLBACK_URL) :
 * aucune image client n'est perdue pendant la migration.
 */
class Media
{
    public static function url(string $folder, ?string $file): ?string
    {
        $file = trim((string) $file);

        if ($file === '' || $file === 'NULL') {
            return null;
        }

        if (Str::startsWith($file, ['http://', 'https://', '//'])) {
            return $file;
        }

        $dir = self::folder($folder);
        $file = ltrim(str_replace('\\', '/', $file), '/');

        if (is_file(self::path($folder, $file))) {
            return config('pratik.media.url').'/'.$dir.'/'.self::encode($file);
        }

        $fallback = config('pratik.media.fallback_url');

        return ($fallback ?: config('pratik.media.url')).'/'.$dir.'/'.self::encode($file);
    }

    public static function path(string $folder, string $file = ''): string
    {
        return rtrim(config('pratik.media.path'), '/').'/'.self::folder($folder).($file !== '' ? '/'.$file : '');
    }

    /**
     * Enregistre un fichier envoyé avec la même convention de nommage que
     * l'ancien site (ex. "ILL12345" + date + timestamp + extension).
     */
    public static function store(UploadedFile $file, string $folder, string $prefix = '', ?int $maxWidth = 1600): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $name = $prefix.random_int(11111, 99999).date('Y-m-d').time().Str::lower(Str::random(4)).'.'.$extension;

        File::ensureDirectoryExists(self::path($folder));
        $target = self::path($folder, $name);

        $isRaster = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);

        if ($isRaster && $maxWidth) {
            try {
                Image::decodePath($file->getRealPath())->scaleDown(width: $maxWidth)->save($target, quality: 82);

                return $name;
            } catch (\Throwable) {
                // on retombe sur une copie brute
            }
        }

        $file->move(self::path($folder), $name);

        return $name;
    }

    public static function delete(string $folder, ?string $file): void
    {
        if ($file && ! Str::contains($file, ['..', '/']) && is_file($path = self::path($folder, $file))) {
            @unlink($path);
        }
    }

    private static function folder(string $key): string
    {
        return config("pratik.media.folders.$key", $key);
    }

    private static function encode(string $file): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $file)));
    }
}
