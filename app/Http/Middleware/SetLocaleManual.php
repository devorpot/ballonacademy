<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class SetLocaleManual
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->user()->locale
            ?? $request->session()->get('locale', 'es');

        $dict = $this->loadTranslations($locale);

        Inertia::share([
            'locale' => $locale,
            'L'      => $dict,
        ]);

        return $next($request);
    }

    protected function loadTranslations(string $locale): array
    {
        $path = resource_path("translations/{$locale}.php");
        if (! file_exists($path)) {
            $path = resource_path('translations/es.php'); // fallback
        }

        $mtime = file_exists($path) ? filemtime($path) : 0;
        $cacheKey = "translations:{$locale}:{$mtime}";

        return Cache::rememberForever($cacheKey, fn () => require $path);
    }
}
