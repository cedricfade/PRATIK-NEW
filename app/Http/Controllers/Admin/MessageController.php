<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactEntreprise;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Messages des formulaires (contacts) et messages envoyés aux annonceurs (contact_entreprises). */
class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $onglet = $request->query('onglet') === 'entreprises' ? 'entreprises' : 'contacts';
        $recherche = trim((string) $request->query('q'));

        if ($onglet === 'contacts') {
            $messages = Contact::query()
                ->when($request->filled('categorie'), fn ($q) => $q->where('categorie', $request->query('categorie')))
                ->when($recherche !== '', fn ($q) => $q->where(fn ($w) => $w->where('nom', 'like', "%{$recherche}%")->orWhere('email', 'like', "%{$recherche}%")->orWhere('objet', 'like', "%{$recherche}%")))
                ->orderByDesc('id')
                ->paginate(30)
                ->withQueryString();
            $categories = Contact::query()->whereNotNull('categorie')->distinct()->orderBy('categorie')->pluck('categorie');
        } else {
            $messages = ContactEntreprise::query()
                ->when($recherche !== '', fn ($q) => $q->where(fn ($w) => $w->where('nom', 'like', "%{$recherche}%")->orWhere('email', 'like', "%{$recherche}%")->orWhere('objet', 'like', "%{$recherche}%")))
                ->with('entreprise:ID,TITRE')
                ->orderByDesc('id')
                ->paginate(30)
                ->withQueryString();
            $categories = collect();
        }

        return view('admin.messages.index', compact('onglet', 'messages', 'categories', 'recherche'));
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        $this->modele($type)::whereKey($id)->firstOrFail()->delete();

        return back()->with('success', 'Message supprimé.');
    }

    public function destroyMany(Request $request, string $type): RedirectResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array', 'max:200'], 'ids.*' => ['integer']])['ids'];
        $total = $this->modele($type)::whereKey($ids)->delete();

        return back()->with('success', "{$total} message(s) supprimé(s).");
    }

    /** @return class-string<\Illuminate\Database\Eloquent\Model> */
    private function modele(string $type): string
    {
        return $type === 'contacts' ? Contact::class : ContactEntreprise::class;
    }
}
