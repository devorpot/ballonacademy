<?php 
 

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit(User $user)
    {
        $user->load('profile');

        return Inertia::render('Admin/Profiles/Edit', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $this->validateProfile($request, $user);

        // Manejo de archivos
        if ($request->hasFile('profile_image')) {
            if ($user->profile?->profile_image) {
                Storage::disk('public')->delete($user->profile->profile_image);
            }
            $validated['profile_image'] = $request->file('profile_image')->store('profiles/profile_images', 'public');
        }

        if ($request->hasFile('cover_image')) {
            if ($user->profile?->cover_image) {
                Storage::disk('public')->delete($user->profile->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('profiles/cover_images', 'public');
        }

        // Crear o actualizar perfil
        if ($user->profile) {
            $user->profile->update($validated);
        } else {
            $user->profile()->create($validated);
        }

        return redirect()->route('admin.students.index')->with('success', 'Perfil actualizado correctamente.');

    }

    public function destroy(User $user)
    {
        if ($user->profile) {
            // Eliminar imágenes si existen
            if ($user->profile->profile_image) {
                Storage::disk('public')->delete($user->profile->profile_image);
            }
            if ($user->profile->cover_image) {
                Storage::disk('public')->delete($user->profile->cover_image);
            }

            $user->profile->delete();
        }

        return redirect()->back()->with('success', 'Perfil eliminado.');
    }

    private function validateProfile(Request $request, User $user): array
    {
        return $request->validate([
            // Fiscales
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
             
            'personal_email' => 'required|email|max:255|unique:profiles,personal_email,' . optional($user->profile)->id,
            'country' => 'required|string|max:100',
            'whatsapp' => 'nullable|string|max:20',
            'nickname' => 'required|string|max:50|unique:profiles,nickname,' . optional($user->profile)->id,

            // Archivos
            'profile_image' => 'nullable|image|max:5120',
            'cover_image' => 'nullable|image|max:5120',

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
