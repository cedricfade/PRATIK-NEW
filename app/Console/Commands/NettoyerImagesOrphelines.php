<?php

namespace App\Console\Commands;

use App\Support\Media;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Repère les images qui ne sont plus référencées en base (entreprises supprimées,
 * biens retirés, anciennes galeries…) et libère de l'espace disque.
 *
 *   php artisan pratik:images-orphelines            → simulation, rien n'est touché
 *   php artisan pratik:images-orphelines --deplacer → déplace vers storage/app/corbeille-images/<date>
 *   php artisan pratik:images-orphelines --supprimer→ suppression définitive
 */
#[Signature('pratik:images-orphelines
    {--dossier=* : Limiter à certains dossiers (logos, immobilier, news, galerie, news_sources, papiers, pubs, heros)}
    {--deplacer : Déplacer les orphelines vers la corbeille (récupérable)}
    {--supprimer : Supprimer définitivement les orphelines}
    {--jours=2 : Ignorer les fichiers modifiés depuis moins de N jours (envois en cours)}')]
#[Description('Liste / nettoie les images clients qui ne sont plus utilisées en base')]
class NettoyerImagesOrphelines extends Command
{
    /** dossier => liste de [table, colonne] qui référencent ses fichiers */
    private const REFERENCES = [
        'logos' => [['annuaires', 'LOGO'], ['annuaires', 'LOGO1']],
        'immobilier' => [['immobilier', 'ILLUSTATION'], ['photo_immobilier', 'IMAGE']],
        'news' => [['news', 'IMAGE']],
        'galerie' => [['photo_annuaire', 'IMAGE']],
        'news_sources' => [['source_news_web', 'LOGO']],
        'papiers' => [['papier_administratif', 'IMAGE']],
        'pubs' => [['sous_categorie', 'PUBS']],
        'heros' => [['parametres_site', 'valeur'], ['pubs_app', 'image']],
    ];

    /** Fichiers partagés à ne jamais supprimer. */
    private const PROTEGES = ['imm_default.jpg', 'default.png', 'default.jpg', '.htaccess', 'index.html', 'index.php'];

    public function handle(): int
    {
        if ($this->option('deplacer') && $this->option('supprimer')) {
            $this->error('Choisissez --deplacer OU --supprimer.');

            return self::FAILURE;
        }

        $dossiers = $this->option('dossier') ?: array_keys(self::REFERENCES);
        $limite = now()->subDays((int) $this->option('jours'))->getTimestamp();
        $corbeille = storage_path('app/corbeille-images/'.now()->format('Y-m-d_His'));
        $totalFichiers = 0;
        $totalOctets = 0;

        foreach ($dossiers as $cle) {
            if (! isset(self::REFERENCES[$cle])) {
                $this->warn("Dossier inconnu : {$cle}");

                continue;
            }

            $chemin = Media::path($cle);
            if (! File::isDirectory($chemin)) {
                $this->line("<comment>{$cle}</comment> : dossier absent ({$chemin})");

                continue;
            }

            $utilises = $this->fichiersReferences(self::REFERENCES[$cle]);
            $orphelins = collect(File::files($chemin))
                ->reject(fn ($f) => in_array($f->getFilename(), self::PROTEGES, true) || \App\Models\Annuaire::estLogoParDefaut($f->getFilename()))
                ->reject(fn ($f) => isset($utilises[$f->getFilename()]))
                ->reject(fn ($f) => $f->getMTime() > $limite);

            $octets = $orphelins->sum(fn ($f) => $f->getSize());
            $totalFichiers += $orphelins->count();
            $totalOctets += $octets;

            $this->line(sprintf(
                '<info>%-13s</info> %6d fichiers, %6d référencés, <comment>%6d orphelins</comment> (%s)',
                $cle,
                count(File::files($chemin)),
                count($utilises),
                $orphelins->count(),
                $this->taille($octets),
            ));

            if ($this->option('deplacer')) {
                File::ensureDirectoryExists($corbeille.'/'.basename($chemin));
                $orphelins->each(fn ($f) => File::move($f->getPathname(), $corbeille.'/'.basename($chemin).'/'.$f->getFilename()));
            } elseif ($this->option('supprimer')) {
                $orphelins->each(fn ($f) => File::delete($f->getPathname()));
            }
        }

        $this->newLine();
        $this->line("Total : <comment>{$totalFichiers} images inutilisées</comment>, ".$this->taille($totalOctets));

        if ($this->option('deplacer')) {
            $this->info("Déplacées dans {$corbeille} (supprimez ce dossier une fois le site vérifié).");
        } elseif ($this->option('supprimer')) {
            $this->info('Supprimées définitivement.');
        } else {
            $this->line('Simulation uniquement. Relancez avec --deplacer (recommandé) ou --supprimer.');
        }

        return self::SUCCESS;
    }

    /** @return array<string, true> */
    private function fichiersReferences(array $references): array
    {
        $noms = [];

        foreach ($references as [$table, $colonne]) {
            DB::table($table)
                ->whereNotNull($colonne)
                ->where($colonne, '!=', '')
                ->select($colonne)
                ->distinct()
                ->orderBy($colonne)
                ->chunk(5000, function ($lignes) use (&$noms, $colonne) {
                    foreach ($lignes as $ligne) {
                        $noms[basename(str_replace('\\', '/', trim($ligne->{$colonne})))] = true;
                    }
                });
        }

        return $noms;
    }

    private function taille(int $octets): string
    {
        return $octets > 1048576 ? round($octets / 1048576, 1).' Mo' : round($octets / 1024).' Ko';
    }
}
