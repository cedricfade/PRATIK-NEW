<?php

namespace App\Providers;

use App\Models\PeriodeGarde;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('fr');
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');

        Paginator::defaultView('partials.pagination');
        Paginator::defaultSimpleView('partials.pagination');

        // En développement : signale les requêtes N+1 (cause n°1 de lenteur).
        Model::preventLazyLoading(! $this->app->isProduction());

        // Période de garde en cours, affichée dans l'en-tête de toutes les pages.
        View::composer(['partials.site.header', 'site.*'], function ($view) {
            $view->with('periodeGarde', Cache::remember('periode-garde-courante:'.now()->toDateString(), 600, fn () => PeriodeGarde::courante()));
        });

        // API mobile (Flutter) : jetons Passport.
        Passport::tokensExpireIn(now()->addDays(60));
        Passport::refreshTokensExpireIn(now()->addDays(120));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(10)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('formulaires', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
    }
}
