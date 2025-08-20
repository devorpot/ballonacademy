<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $user->load('profile');

        return Inertia::render('Frontend/Profiles/EditProfile', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $validated = $this->validateProfile($request, $user);



 
            if (isset($validated['locale']) && in_array($validated['locale'], ['es','en'])) {
                $user->locale = $validated['locale'];
                $user->save();
                // opcional: también puedes guardar en sesión
                $request->session()->put('locale', $validated['locale']);
            }

            // 2) Actualiza el perfil relacionado
            $profileData = collect($validated)->except(['locale'])->toArray();
            $profile = $user->profile; // asumiendo relación $user->profile()
            if ($profile) {
                $profile->fill($profileData);
                $profile->save();
            } else {
                $user->profile()->create($profileData);
            }

        // Eliminar imagen de perfil si se solicita
        if ($request->boolean('remove_profile_image')) {
            if ($user->profile?->profile_image) {
                Storage::disk('public')->delete($user->profile->profile_image);
            }
            $validated['profile_image'] = null;
        }

        // Eliminar imagen de portada si se solicita
        if ($request->boolean('remove_cover_image')) {
            if ($user->profile?->cover_image) {
                Storage::disk('public')->delete($user->profile->cover_image);
            }
            $validated['cover_image'] = null;
        }

        // Subir imagen de perfil si hay archivo
        if ($request->hasFile('profile_image')) {
            if ($user->profile?->profile_image) {
                Storage::disk('public')->delete($user->profile->profile_image);
            }
            $validated['profile_image'] = $request->file('profile_image')->store('profiles/profile_images', 'public');
        }

        // Subir imagen de portada si hay archivo
        if ($request->hasFile('cover_image')) {
            if ($user->profile?->cover_image) {
                Storage::disk('public')->delete($user->profile->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('profiles/cover_images', 'public');
        }

        if ($user->profile) {
            $user->profile->update($validated);
        } else {
            $user->profile()->create($validated);
        }

        return redirect()->route('dashboard.profile.edit')->with('success', 'Perfil actualizado correctamente.');
    }

    private function validateProfile(Request $request, User $user): array
    {
        return $request->validate([
            // Fiscales
            'locale' => ['required','in:es,en'],
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'business_name' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'external_number' => 'nullable|string|max:20',
            'internal_number' => 'nullable|string|max:20',
            'state' => 'nullable|string|max:100',
            'municipality' => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'billing_email' => 'nullable|email|max:255',
            'tax_regime' => 'nullable|string|max:100',
            'cfdi_use' => 'nullable|string|max:100',
            // Personales
            'personal_email' => [
                'required',
                'email',
                'max:255',
            ],
            'country' => 'required|string|max:100',
            'whatsapp' => 'nullable|string|max:20',
            'nickname' => [
                'required',
                'string',
                'max:50',
            ],
            // Archivos
            'profile_image' => 'sometimes|image|max:5120',
            'cover_image' => 'sometimes|image|max:5120',
            'remove_profile_image' => 'sometimes|boolean',
            'remove_cover_image' => 'sometimes|boolean',
            // Redes sociales
            'website' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'description' => 'nullable|string',
        ]);
    }
}
