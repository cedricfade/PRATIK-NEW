<?php

namespace App\Http\Controllers\Immobilier;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Immobilier\Support\Annonceurs;
use App\Http\Controllers\Immobilier\Support\RechercheBiens;
use App\Models\Immobilier;
use App\Models\Statistique;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BienController extends Controller
{
    /** /immobilier/achats et /immobilier/locations */
    public function index(Request $request, string $transaction): View
    {
        $filtres = RechercheBiens::filtres($request);

        $biens = RechercheBiens::requete($filtres, $transaction)
            ->paginate(12)
            ->withQueryString();

        return view('immobilier.biens.index', [
            'transaction' => $transaction,
            'titre' => $transaction === 'location' ? 'Biens à louer' : 'Biens à vendre',
            'biens' => $biens,
            'filtres' => $filtres,
            'marqueurs' => RechercheBiens::marqueurs($biens),
            'categories' => HomeController::categories(),
            'communes' => HomeController::communes(),
            'route' => $transaction === 'location' ? 'immo.locations' : 'immo.achats',
        ]);
    }

    /** /immobilier/immobilier-de-luxe */
    public function luxe(Request $request): View
    {
        $filtres = ['luxe' => 1] + RechercheBiens::filtres($request);

        $biens = RechercheBiens::requete($filtres)->paginate(12)->withQueryString();

        return view('immobilier.biens.index', [
            'transaction' => 'luxe',
            'titre' => 'Immobilier de luxe',
            'biens' => $biens,
            'filtres' => $filtres,
            'marqueurs' => RechercheBiens::marqueurs($biens),
            'categories' => HomeController::categories(),
            'communes' => HomeController::communes(),
            'route' => 'immo.luxe',
        ]);
    }

    /** /immobilier/proprietes-{lien} */
    public function show(Request $request, string $lien): View
    {
        // Jointure sur immobilier : une fiche annuaire peut porter le même lien qu'un bien.
        $bien = Immobilier::query()
            ->select('immobilier.*')
            ->join('entreprises', 'entreprises.ID', '=', 'immobilier.ENTREPRISE_ID')
            ->where('entreprises.LIEN', $lien)
            ->with([
                'entreprise:ID,TITRE,LIEN,COMMUNE_ID,COMPTEUR,VUES_BONUS,UTILISATEUR',
                'entreprise.commune:ID,DESIGNATION,LIEN',
                'photos:ID,IMMOBILIER_ID,IMAGE',
                'categorie:ID,DESIGNATION',
                'type:ID,DESIGNATION',
                'auteur:id,name,email,entreprise_id,agent_tel,agent_cel,agent_facebook,agent_instagram,agent_youtube,agent_twitter',
                'auteur.entreprise:ID,TITRE,LIEN,COMMUNE_ID',
                'auteur.entreprise.annuaire:ID,ENTREPRISE_ID,LOGO,LOGO1,TEL_1,TEL_2,CEL_1,CEL_2,EMAIL_1,EMAIL_2,WHATSAPP,SITE,ADRESSE',
            ])
            ->orderByDesc('immobilier.ID')
            ->firstOrFail();
        $fiche = $bien->entreprise;

        $user = $request->user();
        $estProprietaire = $user && ((int) $bien->UTILISATEUR === (int) $user->id || $user->estAdmin());

        // Un bien non validé n'est visible que par son annonceur et les administrateurs.
        abort_unless($bien->VALIDATION === 'OUI' || $estProprietaire, 404);

        $societe = $bien->auteur?->entreprise;
        $annuaire = $societe?->annuaire;
        $entrepriseStats = $societe?->ID ?? $fiche->ID;

        if (! $estProprietaire) {
            $fiche->incrementerVues();
            Statistique::enregistrer($entrepriseStats, Statistique::VUE);
        }

        $similaires = Immobilier::publie()
            ->where('ID', '!=', $bien->ID)
            ->where('CATEGORIE_IMMOBILIER_ID', $bien->CATEGORIE_IMMOBILIER_ID)
            ->when($bien->est_location, fn ($q) => $q->location(), fn ($q) => $q->vente())
            ->with(['entreprise:ID,TITRE,LIEN,COMMUNE_ID', 'entreprise.commune:ID,DESIGNATION', 'categorie:ID,DESIGNATION'])
            ->orderByDesc('ID')
            ->limit(4)
            ->get();

        return view('immobilier.biens.show', [
            'bien' => $bien,
            'fiche' => $fiche,
            'societe' => $societe,
            'annuaire' => $annuaire,
            'logo' => Annonceurs::logoUrl($annuaire?->LOGO ?: $annuaire?->LOGO1),
            'contacts' => $this->contacts($bien),
            'entrepriseStats' => $entrepriseStats,
            'similaires' => $similaires,
            'video' => $this->video($bien->VIDEO),
            'visite3d' => $this->visite3d($bien->VIDEOS_3D),
            'googleMaps' => $this->googleMaps($bien->GOOGLE_MAPS),
            'estProprietaire' => $estProprietaire,
        ]);
    }

    /** @return array{telephones: list<string>, email: ?string, whatsapp: ?string, nom: ?string} */
    private function contacts(Immobilier $bien): array
    {
        $auteur = $bien->auteur;
        $annuaire = $auteur?->entreprise?->annuaire;

        $telephones = collect([$bien->V_TEL, $bien->V_CEL])
            ->merge($annuaire?->telephones ?? [])
            ->merge([$auteur?->agent_cel, $auteur?->agent_tel])
            ->map(fn ($t) => trim((string) $t))
            ->filter(fn ($t) => strlen(preg_replace('/\D/', '', $t)) >= 8)
            ->unique()
            ->values();

        $email = collect([$bien->V_EMAIL])
            ->merge($annuaire?->emails ?? [])
            ->push($auteur?->email)
            ->first(fn ($e) => filter_var(trim((string) $e), FILTER_VALIDATE_EMAIL));

        // WhatsApp : numéro dédié, sinon le premier mobile ivoirien (01, 05, 07) — jamais un fixe (21, 25, 27).
        $national = fn ($n) => substr(preg_replace('/^(00)?225/', '', preg_replace('/\D/', '', (string) $n)), -10);
        $whatsapp = collect([$bien->V_WHATSAPP, $annuaire?->WHATSAPP])
            ->map(fn ($n) => preg_replace('/\D/', '', (string) $n))
            ->first(fn ($n) => strlen($n) >= 8)
            ?? $telephones->map($national)->first(fn ($n) => preg_match('/^0[157]\d{8}$/', $n));

        if ($whatsapp && strlen($whatsapp) === 10) {
            $whatsapp = '225'.$whatsapp;
        }

        return [
            'telephones' => $telephones->take(3)->all(),
            'email' => $email ? trim($email) : null,
            'whatsapp' => $whatsapp,
            'nom' => $bien->V_NOM ?: $auteur?->entreprise?->TITRE ?: $auteur?->name,
        ];
    }

    private function video(?string $valeur): ?string
    {
        if (preg_match('~(?:youtube\.com/(?:embed/|watch\?v=|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~', (string) $valeur, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1];
        }

        return null;
    }

    /** Visite virtuelle : seules les sources connues sont intégrées en iframe. */
    private function visite3d(?string $valeur): ?array
    {
        $url = trim((string) $valeur);

        if (! filter_var($url, FILTER_VALIDATE_URL) || ! Str::startsWith($url, 'https://')) {
            return null;
        }

        $integrable = (bool) preg_match('~^https://(www\.)?(klapty\.com|my\.matterport\.com|kuula\.co)/~', $url);

        return ['url' => $url, 'integrable' => $integrable];
    }

    /** GOOGLE_MAPS peut contenir un lien ou un iframe collé : on n'en garde qu'une URL sûre. */
    private function googleMaps(?string $valeur): ?array
    {
        $valeur = trim((string) $valeur);

        if (preg_match('~src="(https://www\.google\.com/maps/embed\?[^"]+)"~', $valeur, $m)) {
            return ['embed' => html_entity_decode($m[1]), 'lien' => null];
        }

        if (preg_match('~^https://(goo\.gl/maps|maps\.app\.goo\.gl|www\.google\.[a-z.]+/maps)~', $valeur)) {
            return ['embed' => null, 'lien' => $valeur];
        }

        return null;
    }
}
