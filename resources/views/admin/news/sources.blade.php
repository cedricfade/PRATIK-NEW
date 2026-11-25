@extends('layouts.admin')
@section('title', 'Sources des news')

@section('content')
<div class="grid gap-6 lg:grid-cols-2">
    <section class="card p-5">
        <h2 class="font-sans text-base font-semibold">Journaux (titrologie)</h2>
        <form method="POST" action="{{ route('admin.sources.store', 'presse') }}" class="mt-3 flex gap-2">@csrf
            <input name="designation" class="input" placeholder="Nom du journal" required><button class="btn-ink"><x-icon name="plus" class="size-4" /></button>
        </form>
        <table class="table-admin mt-4">
            <tbody>
                @foreach ($presse as $s)
                    <tr x-data="{ edit: false }">
                        <td>
                            <span x-show="!edit" class="{{ $s->ACTIVATION === 0 ? 'text-muted line-through' : '' }}">{{ $s->DESIGNATION }}</span>
                            <form x-show="edit" x-cloak method="POST" action="{{ route('admin.sources.update', ['presse', $s->ID]) }}" class="flex flex-wrap items-center gap-2">@csrf @method('PUT')
                                <input name="designation" value="{{ $s->DESIGNATION }}" class="input w-48 py-1.5" required>
                                <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="activation" value="1" @checked($s->ACTIVATION !== 0) class="rounded border-line"> actif</label>
                                <button class="btn-ink btn-sm">OK</button>
                            </form>
                        </td>
                        <td class="text-right text-xs tabular-nums">{{ number_format($comptesPresse[$s->ID] ?? 0, 0, ',', ' ') }}</td>
                        <td class="text-right whitespace-nowrap"><button type="button" class="btn-line btn-sm" @click="edit = !edit"><x-icon name="pencil" class="size-3.5" /></button> <x-admin.delete-button :action="route('admin.sources.destroy', ['presse', $s->ID])" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section class="card p-5">
        <h2 class="font-sans text-base font-semibold">Sites d'information (news web)</h2>
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.sources.store', 'web') }}" class="mt-3 flex flex-wrap gap-2">@csrf
            <input name="designation" class="input flex-1" placeholder="Nom du site" required>
            <input type="file" name="logo" accept="image/*" class="text-sm">
            <button class="btn-ink"><x-icon name="plus" class="size-4" /></button>
        </form>
        <table class="table-admin mt-4">
            <tbody>
                @foreach ($web as $s)
                    <tr x-data="{ edit: false }">
                        <td class="w-12"><x-logo-tile :src="$s->logo_url" :name="$s->DESIGNATION" size="size-9" /></td>
                        <td>
                            <span x-show="!edit">{{ $s->DESIGNATION }}</span>
                            <form x-show="edit" x-cloak method="POST" enctype="multipart/form-data" action="{{ route('admin.sources.update', ['web', $s->ID]) }}" class="flex flex-wrap items-center gap-2">@csrf @method('PUT')
                                <input name="designation" value="{{ $s->DESIGNATION }}" class="input w-44 py-1.5" required>
                                <input type="file" name="logo" accept="image/*" class="w-44 text-xs">
                                <button class="btn-ink btn-sm">OK</button>
                            </form>
                        </td>
                        <td class="text-right text-xs tabular-nums">{{ number_format($comptesWeb[$s->ID] ?? 0, 0, ',', ' ') }}</td>
                        <td class="text-right whitespace-nowrap"><button type="button" class="btn-line btn-sm" @click="edit = !edit"><x-icon name="pencil" class="size-3.5" /></button> <x-admin.delete-button :action="route('admin.sources.destroy', ['web', $s->ID])" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</div>
@endsection
