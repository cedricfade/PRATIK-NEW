{{-- Champ piège invisible + horodatage chiffré (voir ProtegeContreSpam). --}}
<div aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden">
    <label>Ne pas remplir ce champ <input type="text" name="site_internet" tabindex="-1" autocomplete="off"></label>
</div>
<input type="hidden" name="_horodatage" value="{{ \Illuminate\Support\Facades\Crypt::encryptString((string) time()) }}">
