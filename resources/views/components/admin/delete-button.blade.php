@props(['action', 'message' => 'Confirmer la suppression ?', 'label' => null, 'small' => true])
<form method="POST" action="{{ $action }}" onsubmit="return confirm(@js($message))" {{ $attributes->merge(['class' => 'inline']) }}>
    @csrf
    @method('DELETE')
    <button type="submit" class="{{ $small ? 'btn-sm' : '' }} btn-line text-danger hover:border-danger/40" title="Supprimer">
        <x-icon name="trash" class="size-3.5" />@if ($label)<span>{{ $label }}</span>@endif
    </button>
</form>
