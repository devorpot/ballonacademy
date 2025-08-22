<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DistributorController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = ['id', 'name', 'country', 'created_at'];
        $sortBy = in_array($request->get('sortBy'), $allowedSorts) ? $request->get('sortBy') : 'created_at';
        $sortDir = $request->get('sortDir') === 'asc' ? 'asc' : 'desc';

        $distributors = Distributor::orderBy($sortBy, $sortDir)->get();

        return Inertia::render('Admin/Distributors/Index', [
            'distributors' => $distributors,
            'filters' => [
                'sortBy' => $sortBy,
                'sortDir' => $sortDir,
            ],
            // Si quieres poblar selects en la UI:
            'countries' => $this->allowedCountries(), // opcional para <select>
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Distributors/Create', [
            'countries' => $this->allowedCountries(), // para selects en el formulario
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        // Carga inicial de archivo (cuando se sube en creación)
        $validated['logo'] = $this->handleUpload($request, 'logo', 'distributors/logos');

        Distributor::create($validated);

        return redirect()
            ->route('admin.distributors.index')
            ->with('success', 'Distribuidor creado exitosamente');
    }

    public function edit(Distributor $distributor)
    {
        return Inertia::render('Admin/Distributors/Edit', [
            'distributor' => $distributor,
            'countries'   => $this->allowedCountries(),
        ]);
    }

    public function update(Request $request, Distributor $distributor)
    {
        // En edición, los archivos son opcionales; permitimos flags de borrado
        $validated = $this->validateData($request, false);

        $distributor->fill($validated);

        // Logo: reemplazo o borrado con flag remove_logo
        if ($request->hasFile('logo')) {
            $this->deleteFile($distributor->logo);
            $distributor->logo = $request->file('logo')->store('distributors/logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteFile($distributor->logo);
            $distributor->logo = null;
        }

        $distributor->save();

        return redirect()
            ->route('admin.distributors.index')
            ->with('success', 'Distribuidor actualizado correctamente');
    }

    public function destroy(Distributor $distributor)
    {
        $this->deleteFile($distributor->logo);
        $distributor->delete();

        return redirect()
            ->route('admin.distributors.index')
            ->with('success', 'Distribuidor eliminado exitosamente');
    }

    public function show(Distributor $distributor)
    {
        return Inertia::render('Admin/Distributors/Show', [
            'distributor' => $distributor
        ]);
    }

    /**
     * $includeFiles=true: valida archivo en creación.
     * $includeFiles=false: no exige archivo en edición (permite flags remove_*).
     */
    private function validateData(Request $request, bool $includeFiles = true): array
    {
        // Nota: el usuario pidió "state" como enum con todos los países de América y Europa.
        // Implementamos Rule::in($this->allowedCountries()) para cumplir exactamente con ese requerimiento.
        $countryList = $this->allowedCountries();

        $rules = [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'country'     => ['required', Rule::in($countryList)], 
            'state'       => ['required', 'string', 'max:100'],
            'region'      => ['nullable', 'string', 'max:100'],
            'zone'        => ['nullable', 'string', 'max:100'],
            'address'     => ['required', 'string', 'max:255'],

            'gmap_link'   => ['nullable', 'url', 'max:255'],
            'lat'         => ['nullable', 'string', 'max:50'],
            'lng'         => ['nullable', 'string', 'max:50'],

            'email'       => ['nullable', 'email', 'max:255'],
            'whatsapp'    => ['nullable', 'string', 'max:30'],
            'phone'       => ['nullable', 'string', 'max:30'],

            'website'     => ['nullable', 'url', 'max:255'],
            'facebook'    => ['nullable', 'url', 'max:255'],
            'instagram'   => ['nullable', 'url', 'max:255'],
            'tiktok'      => ['nullable', 'url', 'max:255'],

            // Flags de borrado
            'remove_logo' => ['sometimes', 'boolean'],
        ];

        if ($includeFiles) {
            $rules['logo'] = ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'];
        }

        return $request->validate($rules);
    }

    private function handleUpload(Request $request, string $field, string $path): ?string
    {
        if ($request->hasFile($field)) {
            return $request->file($field)->store($path, 'public');
        }
        return null;
    }

    private function deleteFile(?string $filePath): void
    {
        if ($filePath) {
            Storage::disk('public')->delete($filePath);
        }
    }

    /**
     * Lista de países de América y Europa (en español).
     * Se usa para validar "state" como enum, según requerimiento.
     */
    private function allowedCountries(): array
    {
        return [
            // América del Norte
            'Canadá','Estados Unidos','México',

            // Centroamérica
            'Belice','Costa Rica','El Salvador','Guatemala','Honduras','Nicaragua','Panamá',

            // Caribe
            'Antigua y Barbuda','Bahamas','Barbados','Cuba','Dominica','Granada','Haití',
            'Jamaica','República Dominicana','San Cristóbal y Nieves','San Vicente y las Granadinas','Santa Lucía','Trinidad y Tobago',

            // Sudamérica
            'Argentina','Bolivia','Brasil','Chile','Colombia','Ecuador','Guyana','Paraguay','Perú','Surinam','Uruguay','Venezuela',

            // Europa (UE + no UE más comunes)
            'Alemania','Austria','Bélgica','Bulgaria','Chipre','Croacia','Dinamarca','Eslovaquia','Eslovenia',
            'España','Estonia','Finlandia','Francia','Grecia','Hungría','Irlanda','Italia',
            'Letonia','Lituania','Luxemburgo','Malta','Países Bajos','Polonia','Portugal','República Checa',
            'Rumanía','Suecia',
            // No UE
            'Albania','Andorra','Bosnia y Herzegovina','Islandia','Kosovo','Liechtenstein','Moldavia',
            'Mónaco','Montenegro','Noruega','Reino Unido','San Marino','Serbia','Suiza','Ucrania','Ciudad del Vaticano'
        ];
    }
}
