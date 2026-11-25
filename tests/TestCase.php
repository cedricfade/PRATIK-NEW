<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    protected string $mediaTestPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Les images de test sont écrites dans un dossier temporaire, jamais dans public/images.
        $this->mediaTestPath = storage_path('framework/testing/images-'.getmypid());
        File::ensureDirectoryExists($this->mediaTestPath);
        config([
            'pratik.media.path' => $this->mediaTestPath,
            'pratik.media.fallback_url' => '',
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->mediaTestPath);

        parent::tearDown();
    }

    /** Crée un faux fichier image dans un dossier média et renvoie son nom. */
    protected function fauxFichier(string $dossier, string $nom): string
    {
        $chemin = \App\Support\Media::path($dossier);
        File::ensureDirectoryExists($chemin);
        File::put($chemin.'/'.$nom, 'x');

        return $nom;
    }
}
