<footer class="mt-20 border-t-4 border-roof bg-ink text-white/75">
    <div class="container-page grid gap-10 py-14 md:grid-cols-4">
        <div class="md:col-span-2">
            <p class="font-serif text-2xl text-white">Pratik <span class="text-roof">Immobilier</span></p>
            <p class="mt-3 max-w-md text-sm leading-6">
                Appartements, villas, terrains et bureaux proposés par des agences, promoteurs et aménageurs
                fonciers vérifiés en Côte d'Ivoire. Contactez directement l'annonceur, sans intermédiaire.
            </p>
            <div class="mt-5 flex flex-wrap gap-2">
                <a href="{{ config('pratik.apps.android') }}" target="_blank" rel="noopener"><img src="{{ asset('brand/google-play.svg') }}" alt="Google Play" class="h-10 w-auto"></a>
                <a href="{{ config('pratik.apps.ios') }}" target="_blank" rel="noopener"><img src="{{ asset('brand/app-store.svg') }}" alt="App Store" class="h-10 w-auto"></a>
            </div>
        </div>
        <div>
            <p class="text-sm font-semibold text-white">Rechercher</p>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a href="{{ route('immo.achats') }}" class="hover:text-white">Biens à vendre</a></li>
                <li><a href="{{ route('immo.locations') }}" class="hover:text-white">Biens à louer</a></li>
                <li><a href="{{ route('immo.luxe') }}" class="hover:text-white">Immobilier de luxe</a></li>
                <li><a href="{{ route('immo.amenageurs') }}" class="hover:text-white">Terrains & lotissements</a></li>
                <li><a href="{{ route('immo.financement') }}" class="hover:text-white">Financer son projet</a></li>
            </ul>
        </div>
        <div>
            <p class="text-sm font-semibold text-white">Professionnels</p>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a href="{{ route('immo.agences') }}" class="hover:text-white">Agences immobilières</a></li>
                <li><a href="{{ route('immo.promoteurs') }}" class="hover:text-white">Promoteurs</a></li>
                <li><a href="{{ route('immo.pro.dashboard') }}" class="hover:text-white">Espace annonceur</a></li>
                <li><a href="{{ route('immo.contact') }}" class="hover:text-white">Devenir partenaire</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="container-page flex flex-col gap-2 py-5 text-xs text-white/50 sm:flex-row sm:justify-between">
            <p>© {{ date('Y') }} Pratik Immobilier — un service de <a href="{{ route('home') }}" class="underline hover:text-white">pratik-ci.com</a></p>
            <p class="flex gap-4"><a href="{{ route('cgu') }}" class="hover:text-white">Conditions d'utilisation</a><a href="{{ route('confidentialite') }}" class="hover:text-white">Confidentialité</a></p>
        </div>
    </div>
</footer>
