@extends('layouts.site')
@section('title', 'Conditions générales d\'utilisation')
@section('description', 'Conditions générales d\'utilisation du site pratik-ci.com et de l\'application mobile Pratik-CI.')

@section('content')
    @include('site.pages._legal', [
        'titre' => 'Conditions d\'utilisation',
        'intro' => 'Bienvenue sur Pratik (site pratik-ci.com et application mobile « Pratik-CI »). Veuillez lire attentivement les présentes conditions avant d\'utiliser nos services.',
        'sections' => [
            'Acceptation des conditions' => '<p>En utilisant Pratik, vous acceptez de vous conformer aux présentes conditions. Si vous ne les acceptez pas, veuillez cesser d\'utiliser le service.</p>',
            'Utilisation autorisée' => '<ul><li>Vous devez avoir au moins 13 ans pour utiliser Pratik.</li><li>Vous ne pouvez pas utiliser le service à des fins illégales, abusives ou frauduleuses.</li><li>Vous ne pouvez pas copier, modifier, distribuer ou reproduire le contenu de Pratik sans autorisation.</li></ul>',
            'Propriété intellectuelle' => '<p>Tous les droits de propriété intellectuelle relatifs à Pratik (notamment droits d\'auteur, marques et bases de données) sont la propriété exclusive de SIEC. Vous ne pouvez pas les utiliser sans autorisation écrite.</p>',
            'Confidentialité des données' => '<p>Nous collectons des données personnelles conformément à notre <a href="'.route('confidentialite').'">politique de confidentialité</a>. En utilisant Pratik, vous consentez à la collecte et à l\'utilisation de vos données conformément à cette politique.</p>',
            'Responsabilités de l\'utilisateur' => '<ul><li>Vous ne devez pas harceler, menacer ou nuire à d\'autres utilisateurs.</li><li>Vous ne devez pas tenter de pirater le service ni d\'accéder à des comptes sans autorisation.</li><li>Vous êtes responsable de la confidentialité de votre compte et de votre mot de passe.</li></ul>',
            'Résiliation du compte' => '<p>Nous nous réservons le droit de suspendre ou de résilier votre compte en cas de violation des présentes conditions ou pour tout autre motif légitime.</p>',
            'Modification des conditions' => '<p>Nous pouvons modifier ces conditions à tout moment. Les modifications entrent en vigueur dès leur publication. Il vous appartient de les consulter régulièrement.</p>',
            'Limitation de responsabilité' => '<p>Les informations de l\'annuaire et des pharmacies de garde sont fournies à titre indicatif. Nous ne sommes pas responsables des dommages ou pertes liés à l\'utilisation du service, que vous utilisez à vos propres risques.</p>',
            'Droit applicable et juridiction' => '<p>Les présentes conditions sont régies par le droit ivoirien. Tout litige relève de la compétence exclusive des tribunaux d\'Abidjan.</p><p>Pour toute question, écrivez-nous à <a href="mailto:'.config('pratik.contact.email').'">'.config('pratik.contact.email').'</a>.</p>',
        ],
    ])
@endsection
