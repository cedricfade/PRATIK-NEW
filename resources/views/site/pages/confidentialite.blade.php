@extends('layouts.site')
@section('title', 'Politique de confidentialité')
@section('description', 'Comment Pratik collecte, utilise et protège vos données personnelles, y compris votre position pour les pharmacies de garde.')

@section('content')
    @include('site.pages._legal', [
        'titre' => 'Politique de confidentialité',
        'intro' => 'Nous nous engageons à protéger votre vie privée et la sécurité de vos données personnelles. Cette politique explique quelles informations nous collectons, comment nous les utilisons et comment nous les protégeons.',
        'sections' => [
            'Données collectées' => '<p>Nous pouvons collecter :</p><ul><li>des informations d\'identification (nom, adresse e-mail, téléphone) lorsque vous créez un compte ou remplissez un formulaire ;</li><li>votre position, uniquement si vous l\'autorisez ;</li><li>les données de profil que vous fournissez volontairement ;</li><li>des données de connexion (adresse IP, informations techniques) ;</li><li>les données de transaction si vous effectuez un achat.</li></ul>',
            'Utilisation des données' => '<ul><li>Fournir, maintenir et améliorer Pratik.</li><li>Afficher un contenu pertinent pour vous.</li><li>Traiter les transactions, le cas échéant.</li><li>Vous envoyer des informations liées au service.</li><li>Répondre à vos demandes.</li><li>Respecter nos obligations légales.</li></ul>',
            'Partage des données' => '<p>Nous ne partageons pas vos données personnelles avec des tiers, sauf :</p><ul><li>avec votre consentement ;</li><li>pour respecter une obligation légale ;</li><li>avec les prestataires qui nous aident à fournir le service (hébergement par exemple) ;</li><li>pour protéger nos droits et la sécurité des utilisateurs.</li></ul><p>Les messages envoyés à une entreprise via sa fiche lui sont transmis directement.</p>',
            'Sécurité des données' => '<p>Nous prenons des mesures raisonnables pour protéger vos données contre l\'accès non autorisé, la divulgation, la modification ou la destruction. Aucune méthode de transmission ou de stockage électronique n\'est toutefois totalement sûre.</p>',
            'Accès et contrôle de vos données' => '<p>Vous pouvez consulter, modifier ou supprimer vos données depuis votre compte, ou nous contacter pour exercer vos droits.</p>',
            'Cookies' => '<p>Nous utilisons des cookies et technologies similaires pour mesurer l\'audience et faire fonctionner le service. Vous pouvez les refuser dans votre navigateur, au prix de certaines fonctionnalités.</p>',
            'Données de localisation' => '<p>Votre position précise n\'est utilisée que pour afficher les pharmacies de garde les plus proches. Elle n\'est ni enregistrée, ni partagée, ni utilisée à d\'autres fins. Vous pouvez refuser l\'accès à la localisation dans les réglages de votre appareil.</p>',
            'Modifications de cette politique' => '<p>Nous pouvons mettre à jour cette politique. Les modifications sont publiées sur cette page.</p>',
            'Nous contacter' => '<p>Pour toute question sur vos données, écrivez à <a href="mailto:'.config('pratik.contact.email').'">'.config('pratik.contact.email').'</a>.</p>',
        ],
    ])
@endsection
