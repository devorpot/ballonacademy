<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DistributorController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'q'       => trim((string) $request->input('q')),
            'country' => trim((string) $request->input('country')),
            'state'   => trim((string) $request->input('state')),
        ];

        $query = Distributor::query()
            ->select([
                'id',
                'name',
                'logo',
                'description',
                'country',
                'state',
                'region',
                'zone',
                'address',
                'gmap_link',
                'lat',
                'lng',
                'email',
                'whatsapp',
                'phone',
                'website',
                'facebook',
                'instagram',
                'tiktok',
            ]);

        // Filtro por texto
        if ($filters['q'] !== '') {
            $q = $filters['q'];
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('address', 'like', "%{$q}%")
                   ->orWhere('state', 'like', "%{$q}%")
                   ->orWhere('region', 'like', "%{$q}%")
                   ->orWhere('zone', 'like', "%{$q}%");
            });
        }

        // Filtro por país
        if ($filters['country'] !== '') {
            $query->where('country', $filters['country']);
        }

        // Filtro por estado
        if ($filters['state'] !== '') {
            $query->where('state', $filters['state']);
        }

        $distributors = $query->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        // Listado de países (en base a resultados filtrados por q)
        $countriesBase = Distributor::query()
            ->when($filters['q'] !== '', function ($qb) use ($filters) {
                $q = $filters['q'];
                $qb->where(function ($x) use ($q) {
                    $x->where('name', 'like', "%{$q}%")
                      ->orWhere('address', 'like', "%{$q}%")
                      ->orWhere('state', 'like', "%{$q}%")
                      ->orWhere('region', 'like', "%{$q}%")
                      ->orWhere('zone', 'like', "%{$q}%");
                });
            });

        $countries = (clone $countriesBase)
            ->distinct()
            ->orderBy('country')
            ->pluck('country')
            ->filter()
            ->values();

        // Listado de estados (depende del país y de q)
        $states = Distributor::query()
            ->when($filters['country'] !== '', fn ($qb) => $qb->where('country', $filters['country']))
            ->when($filters['q'] !== '', function ($qb) use ($filters) {
                $q = $filters['q'];
                $qb->where(function ($x) use ($q) {
                    $x->where('name', 'like', "%{$q}%")
                      ->orWhere('address', 'like', "%{$q}%")
                      ->orWhere('state', 'like', "%{$q}%")
                      ->orWhere('region', 'like', "%{$q}%")
                      ->orWhere('zone', 'like', "%{$q}%");
                });
            })
            ->distinct()
            ->orderBy('state')
            ->pluck('state')
            ->filter()
            ->values();

        // Mapeo para el frontend
        $payload = [
            'data'  => $distributors->getCollection()->transform(function ($d) {
                // Logo
                $logoUrl = $d->logo ? asset('storage/' . ltrim($d->logo, '/')) : null;

                // Enlace de mapa: prioriza gmap_link; si no, usa lat/lng
                $mapUrl = $d->gmap_link ?: (
                    ($d->lat !== null && $d->lng !== null)
                        ? "https://www.google.com/maps?q={$d->lat},{$d->lng}"
                        : null
                );

                return [
                    'id'          => $d->id,
                    'name'        => $d->name,
                    'description' => $d->description,
                    'country'     => $d->country,
                    'state'       => $d->state,
                    'region'      => $d->region,
                    'zone'        => $d->zone,
                    'address'     => $d->address,
                    'lat'         => $d->lat,
                    'lng'         => $d->lng,
                    'email'       => $d->email,
                    'whatsapp'    => $d->whatsapp,
                    'phone'       => $d->phone,
                    'website'     => $d->website,
                    'facebook'    => $d->facebook,
                    'instagram'   => $d->instagram,
                    'tiktok'      => $d->tiktok,
                    'logo_url'    => $logoUrl,
                    'map_url'     => $mapUrl,
                ];
            }),
            'links' => $distributors->linkCollection(),
            'meta'  => [
                'current_page' => $distributors->currentPage(),
                'last_page'    => $distributors->lastPage(),
                'per_page'     => $distributors->perPage(),
                'total'        => $distributors->total(),
            ],
        ];

        return Inertia::render('Frontend/Distributors/Index', [
            'distributors' => $payload,
            'filters'      => $filters,
            'countries'    => $countries,
            'states'       => $states,
        ]);
    }
}
