<footer class="mt-20 bg-ink text-white/75">
    <div class="container-page grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-12">
        <div class="lg:col-span-4">
            <p class="font-serif text-2xl text-white">Pratik<span class="text-amber">.</span></p>
            <p class="mt-3 max-w-sm text-sm leading-6">
                Depuis Abidjan, nous recensons les entreprises, les pharmacies de garde et les services utiles
                de Côte d'Ivoire pour vous faire gagner du temps au quotidien.
            </p>
            <div class="mt-5 flex gap-2">
                @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'x' => 'X', 'tiktok' => 'TikTok'] as $reseau => $label)
                    <a href="{{ config('pratik.contact.'.$reseau) }}" target="_blank" rel="noopener" aria-label="{{ $label }}" class="grid size-9 place-items-center rounded-full border border-white/15 hover:border-amber hover:text-amber">
                        <x-icon :name="$reseau === 'x' ? 'x-social' : $reseau" class="size-4" />
                    </a>
                @endforeach
            </div>
        </div>

        <div class="lg:col-span-2">
            <p class="text-sm font-semibold text-white">Services</p>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a href="{{ route('annuaire.index') }}" class="hover:text-white">Annuaire des entreprises</a></li>
                <li><a href="{{ route('pharmacies.index') }}" class="hover:text-white">Pharmacies de garde</a></li>
                <li><a href="{{ route('news.index') }}" class="hover:text-white">Revue de presse</a></li>
                <li><a href="{{ route('papiers.index') }}" class="hover:text-white">Papiers administratifs</a></li>
                <li><a href="{{ route('vols.index') }}" class="hover:text-white">Horaires des vols</a></li>
                <li><a href="{{ route('immo.home') }}" target="_blank" rel="noopener" class="hover:text-white">Pratik Immobilier</a></li>
            </ul>
        </div>

        <div class="lg:col-span-3">
            <p class="text-sm font-semibold text-white">Professionnels</p>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a href="{{ route('souscription') }}" class="hover:text-white">Inscrire mon entreprise</a></li>
                <li><a href="{{ route('immo.pro.dashboard') }}" class="hover:text-white">Espace annonceur immobilier</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-white">Nous contacter</a></li>
            </ul>
            <p class="mt-6 text-sm font-semibold text-white">Contact</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a href="{{ tel_link(config('pratik.contact.phone_raw')) }}" class="hover:text-white">{{ config('pratik.contact.phone') }}</a></li>
                <li><a href="mailto:{{ config('pratik.contact.email') }}" class="hover:text-white">{{ config('pratik.contact.email') }}</a></li>
            </ul>
        </div>

        <div class="lg:col-span-3">
            <p class="text-sm font-semibold text-white">L'application Pratik</p>
            <p class="mt-4 text-sm leading-6">Les pharmacies de garde et l'annuaire dans votre poche, même en déplacement.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ config('pratik.apps.android') }}" target="_blank" rel="noopener"><img src="{{ asset('brand/google-play.svg') }}" alt="Disponible sur Google Play" class="h-10 w-auto"></a>
                <a href="{{ config('pratik.apps.ios') }}" target="_blank" rel="noopener"><img src="{{ asset('brand/app-store.svg') }}" alt="Télécharger dans l'App Store" class="h-10 w-auto"></a>
            </div>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="container-page flex flex-col gap-2 py-5 text-xs text-white/50 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} Pratik Côte d'Ivoire. Tous droits réservés.</p>
            <p class="flex gap-4">
                <a href="{{ route('cgu') }}" class="hover:text-white">Conditions d'utilisation</a>
                <a href="{{ route('confidentialite') }}" class="hover:text-white">Confidentialité</a>
            </p>
        </div>
    </div>
</footer>
