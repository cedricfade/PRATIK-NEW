@extends('layouts.admin')
@section('title', 'Catégories & types de biens')

@section('content')
<div class="grid gap-6 lg:grid-cols-2">
    @foreach (['categories' => ['Catégories de biens', $categories, $parCategorie, 'Appartement, villa, terrain…'], 'types' => ['Types d\'offre', $types, $parType, 'Agence location, promoteur…']] as $table => [$titre, $liste, $comptes, $aide])
        <section class="card p-5">
            <h2 class="font-sans text-base font-semibold">{{ $titre }}</h2>
            <p class="text-xs text-muted">{{ $aide }}</p>
            <form method="POST" action="{{ route('admin.immo-referentiels.store', $table) }}" class="mt-3 flex gap-2">
                @csrf
                <input name="designation" class="input" placeholder="Nouvel élément" required>
                <button class="btn-ink"><x-icon name="plus" class="size-4" /></button>
            </form>
            <table class="table-admin mt-4">
                <tbody>
                    @foreach ($liste as $el)
                        <tr x-data="{ edit: false }">
                            <td>
                                <span x-show="!edit" class="{{ $el->ACTIVATION ? '' : 'text-muted line-through' }}">{{ $el->DESIGNATION }}</span>
                                <form x-show="edit" x-cloak method="POST" action="{{ route('admin.immo-referentiels.update', [$table, $el->ID]) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf @method('PUT')
                                    <input name="designation" value="{{ $el->DESIGNATION }}" class="input w-48 py-1.5" required>
                                    <label class="flex items-center gap-1 text-xs"><input type="hidden" name="activation" value="0"><input type="checkbox" name="activation" value="1" @checked($el->ACTIVATION) class="rounded border-line"> actif</label>
                                    <button class="btn-ink btn-sm">OK</button>
                                </form>
                            </td>
                            <td class="text-right text-xs tabular-nums">{{ $comptes[$el->ID] ?? 0 }} biens</td>
                            <td class="text-right whitespace-nowrap">
                                <button type="button" class="btn-line btn-sm" @click="edit = !edit"><x-icon name="pencil" class="size-3.5" /></button>
                                <x-admin.delete-button :action="route('admin.immo-referentiels.destroy', [$table, $el->ID])" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endforeach
</div>
@endsection
