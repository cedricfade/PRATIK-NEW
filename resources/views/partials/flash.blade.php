@if (session('success') || session('error') || session('status'))
    <div class="container-page pt-4" x-data="{ show: true }" x-show="show" x-transition>
        <div @class([
            'flex items-start gap-3 rounded-lg border px-4 py-3 text-sm',
            'border-garde/30 bg-garde-wash text-garde' => session('success') || session('status'),
            'border-danger/30 bg-red-50 text-danger' => session('error'),
        ])>
            <x-icon :name="session('error') ? 'alert' : 'check-circle'" class="mt-0.5 size-4 shrink-0" />
            <p class="flex-1">{{ session('success') ?? session('status') ?? session('error') }}</p>
            <button type="button" @click="show = false" class="opacity-60 hover:opacity-100" aria-label="Fermer">
                <x-icon name="x" class="size-4" />
            </button>
        </div>
    </div>
@endif
