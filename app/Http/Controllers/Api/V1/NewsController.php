<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NewsWebResource;
use App\Http\Resources\Api\UneResource;
use App\Http\Resources\Api\VideoResource;
use App\Models\News;
use App\Models\VideoInfo;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class NewsController extends Controller
{
    use Concerns;

    /** Unes des journaux d'une date (par défaut : la dernière date publiée), groupées par périodicité. */
    public function titrologie(Request $request): JsonResponse
    {
        $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);

        $date = $request->query('date')
            ?: Cache::remember('api:titrologie:derniere-date:'.now()->toDateString(), 600, fn () => News::titrologie()
                ->where('DATE_PARUTION', '<=', now()->toDateString())
                ->max('DATE_PARUTION'));

        $unes = $date ? Cache::remember('api:titrologie:'.$date, 600, fn () => News::titrologie()
            ->where('DATE_PARUTION', $date)
            ->with(['entreprise:ID,TITRE,LIEN', 'source:ID,DESIGNATION'])
            ->orderBy('RANG')
            ->orderBy('ID')
            ->get(['ID', 'ENTREPRISE_ID', 'SOURCE_ID', 'TYPE_NEWS_ID', 'IMAGE', 'DATE_PARUTION', 'NUMERO_PARUTION', 'RANG'])) : collect();

        $groupe = fn (int $type) => UneResource::collection($unes->where('TYPE_NEWS_ID', $type)->values());

        return response()->json(['data' => [
            'date' => $date ? Carbon::parse($date)->toDateString() : null,
            'quotidiens' => $groupe(1),
            'hebdomadaires' => $groupe(2),
            'mensuels' => $groupe(3),
            'total' => $unes->count(),
        ]]);
    }

    public function web(Request $request): AnonymousResourceCollection
    {
        $news = News::web()
            ->with(['entreprise:ID,TITRE,LIEN', 'sourceWeb:ID,DESIGNATION,LOGO'])
            ->orderByDesc('ID')
            ->paginate($this->parPage($request), ['ID', 'ENTREPRISE_ID', 'SOURCE_WEB_ID', 'IMAGE', 'RESUME', 'LIEN_WEB', 'DATE_PARUTION']);

        return NewsWebResource::collection($news);
    }

    public function videos(Request $request): AnonymousResourceCollection
    {
        return VideoResource::collection(VideoInfo::orderByDesc('id')->paginate($this->parPage($request)));
    }
}
