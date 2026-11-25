@props(['annonceur'])
{{-- $annonceur : tableau issu de Support\Annonceurs::liste() --}}
<a href="{{ route('immo.annonceur', $annonceur['lien']) }}" {{ $attributes->merge(['class' => 'card card-hover flex items-center gap-4 p-4']) }}>
    <x-logo-tile :src="$annonceur['logo_url']" :name="$annonceur['titre']" size="size-16" />
    <div class="min-w-0">
        <p class="font-semibold leading-snug text-ink">{{ \Illuminate\Support\Str::title(\Illuminate\Support\Str::lower($annonceur['titre'])) }}</p>
        @if ($annonceur['commune'])
            <p class="mt-0.5 text-[13px] text-muted">{{ $annonceur['commune'] }}</p>
        @endif
        <p class="mt-1.5 text-[13px] font-medium text-roof-deep">{{ $annonceur['total'] }} {{ \Illuminate\Support\Str::plural('annonce', $annonceur['total']) }}</p>
    </div>
</a>
