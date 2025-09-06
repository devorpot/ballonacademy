<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Auth;

class HandleInertiaRequests extends Middleware
{

      public function version(Request $request): ?string
    {
        // Válido si usas Vite/Laravel:
        return parent::version($request);

        // O fija explícitamente con el manifest de Vite:
        // return md5_file(public_path('build/manifest.json'));
    }
    public function share(Request $request): array
{
    $user = $request->user();

    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                 'locale' => $user->locale,
                // Aquí cargamos el perfil relacionado
                'profile' => $user->profile ? [
                    'id' => $user->profile->id,
                    'user_id' => $user->profile->user_id,
                    'firstname' => $user->profile->firstname,
                    'lastname' => $user->profile->lastname,
                    'profile_image' => $user->profile->profile_image,
                    'cover_image' => $user->profile->cover_image,
                    'whatsapp' => $user->profile->whatsapp,
                    'nickname' => $user->profile->nickname,
                     'country' => $user->profile->country,
                   
                ] : null,
            ] : null, 
        ],
        'flash' => [
            'message' => fn () => $request->session()->get('message'),
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
        ],
        'app' => [
            'name' => config('app.name'),
            'env' => config('app.env'),
            'url' => config('app.url'),
        ],
    ]);
}

}