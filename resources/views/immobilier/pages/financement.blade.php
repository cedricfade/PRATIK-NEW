@extends('layouts.immo')

@section('title', 'Simuler son prêt immobilier')
@section('description', 'Estimez vos mensualités de prêt immobilier en Côte d\'Ivoire et vérifiez votre taux d\'endettement, avec notre partenaire bancaire.')

@section('content')
    <x-page-header tone="immo" title="Financer votre projet" :breadcrumbs="['Immobilier' => route('immo.home'), 'Financement' => null]">
        Estimez vos mensualités avant de visiter. En partenariat avec la BHCI (Banque de l'Habitat de Côte d'Ivoire) :
        frais de dossier offerts et accompagnement gratuit pour les clients Pratik.
    </x-page-header>

    <div class="container-page grid gap-8 py-10 lg:grid-cols-[1fr_380px]"
         x-data="{
            prix: 30000000, apport: 0, duree: 20, taux: 4.49, revenus: 0, revenusConjoint: 0, charges: 0,
            get capital() { return Math.max(0, this.prix - this.apport) },
            get mensualite() {
                const t = this.taux / 100 / 12, n = this.duree * 12;
                if (!this.capital) return 0;
                return t ? this.capital * t / (1 - Math.pow(1 + t, -n)) : this.capital / n;
            },
            get cout() { return Math.max(0, this.mensualite * this.duree * 12 - this.capital) },
            get endettement() {
                const r = Number(this.revenus) + Number(this.revenusConjoint);
                return r > 0 ? (this.mensualite + Number(this.charges)) / r * 100 : null;
            },
            fcfa(v) { return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Math.round(v)) + ' FCFA' },
         }">
        <form class="card space-y-6 p-6 sm:p-8" @submit.prevent>
            <div>
                <label class="label" for="prix">Prix du bien</label>
                <input id="prix" type="number" min="0" step="500000" x-model.number="prix" class="input text-lg">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="apport">Apport personnel</label>
                    <input id="apport" type="number" min="0" step="500000" x-model.number="apport" class="input">
                </div>
                <div>
                    <label class="label" for="duree">Durée du prêt</label>
                    <select id="duree" x-model.number="duree" class="input">
                        @foreach ([10, 15, 20, 25, 30] as $d)<option value="{{ $d }}">{{ $d }} ans</option>@endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="label" for="taux">Taux d'intérêt annuel (%)</label>
                <input id="taux" type="number" min="0" max="20" step="0.01" x-model.number="taux" class="input">
                <p class="mt-1 text-xs text-muted">Taux indicatif de 4,49 % proposé par notre partenaire. Le taux réel dépend de votre dossier.</p>
            </div>
            <fieldset class="border-t border-line pt-5">
                <legend class="text-sm font-semibold">Votre budget <span class="font-normal text-muted">(facultatif)</span></legend>
                <div class="mt-3 grid gap-4 sm:grid-cols-3">
                    <div><label class="label" for="revenus">Revenus nets mensuels</label><input id="revenus" type="number" min="0" step="10000" x-model.number="revenus" class="input"></div>
                    <div><label class="label" for="revenusConjoint">Revenus du conjoint</label><input id="revenusConjoint" type="number" min="0" step="10000" x-model.number="revenusConjoint" class="input"></div>
                    <div><label class="label" for="charges">Autres crédits / charges</label><input id="charges" type="number" min="0" step="10000" x-model.number="charges" class="input"></div>
                </div>
            </fieldset>
        </form>

        <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
            <div class="rounded-xl bg-ink p-6 text-white">
                <p class="text-sm text-white/70">Mensualité estimée</p>
                <p class="mt-1 font-serif text-3xl font-semibold text-roof" x-text="fcfa(mensualite)"></p>
                <dl class="mt-5 space-y-2 border-t border-white/10 pt-4 text-sm">
                    <div class="flex justify-between"><dt class="text-white/70">Montant emprunté</dt><dd x-text="fcfa(capital)"></dd></div>
                    <div class="flex justify-between"><dt class="text-white/70">Coût des intérêts</dt><dd x-text="fcfa(cout)"></dd></div>
                    <div class="flex justify-between"><dt class="text-white/70">Durée</dt><dd><span x-text="duree"></span> ans</dd></div>
                </dl>
            </div>
            <template x-if="endettement !== null">
                <div class="rounded-xl border p-5 text-sm" :class="endettement > 30 ? 'border-danger/30 bg-red-50 text-danger' : 'border-garde/30 bg-garde-wash text-garde'">
                    <p class="font-semibold">Taux d'endettement : <span x-text="endettement.toFixed(1).replace('.', ',') + ' %'"></span></p>
                    <p class="mt-1" x-show="endettement > 30">Au-delà de 30 % (seuil retenu par notre partenaire), les banques refusent souvent le prêt. Allongez la durée ou augmentez l'apport.</p>
                    <p class="mt-1" x-show="endettement <= 30">Votre projet reste dans les limites habituellement acceptées par les banques.</p>
                </div>
            </template>
            <a href="{{ route('immo.contact') }}" class="btn-roof w-full">Être rappelé par un conseiller</a>
            <p class="text-xs text-muted">Simulation indicative, sans valeur d'offre de prêt.</p>
        </aside>
    </div>
@endsection
