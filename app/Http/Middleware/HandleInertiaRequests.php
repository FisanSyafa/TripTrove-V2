<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, \Closure $next)
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }

        return parent::handle($request, $next);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = App::getLocale();
        
        $currency = Session::get('currency', 'IDR');
        
        $jsonFile = resource_path("lang/{$locale}.json");
        $translations = File::exists($jsonFile) 
            ? json_decode(File::get($jsonFile), true) 
            : [];

        $rates = Cache::remember('currency_rates', 360 * 60 * 6, function () {
            try {
                $response = Http::get("https://api.frankfurter.app/latest?base=IDR");
                
                if ($response->successful()) {
                    return $response->json()['rates'];
                }
                return null;
            } catch (\Exception $e) {
                return null;
            }
        });

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'ziggy' => fn () => [
                ...(new \Tighten\Ziggy\Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'locale' => $locale,
            'translations' => $translations,
            
            'currency' => $currency, 
            'currencyRates' => $rates ?? [],
            'csrf_token' => csrf_token(),
        ];
    }
}
