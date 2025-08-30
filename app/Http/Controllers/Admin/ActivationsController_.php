<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ActivationInvitationMail;
use App\Models\Activation;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

use Illuminate\Validation\ValidationException;


class ActivationsController extends Controller
{

  public function index(Request $request)
    {
        $query = Activation::query()
            ->with(['course:id,title'])
            ->select('id', 'name', 'email', 'phone', 'course_id', 'code', 'hash', 'active', 'created');

        // ====== Filtros ======
        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                 $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($courseId = $request->input('course_id')) {
            $query->where('course_id', $courseId);
        }

        if (!is_null($request->input('active'))) {
            $query->where('active', (bool) $request->input('active'));
        }

        // ====== Ordenamiento ======
        $sortBy  = $request->input('sortBy', 'activations.id');
        $sortDir = $request->input('sortDir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // ====== Paginación ======
        $perPage     = (int) $request->input('perPage', 15);
        $activations = $query->paginate($perPage)->appends($request->query());

        // Agregar public_link calculado para usarlo directo en la vista
        $base = URL::to('/');
        $activations->getCollection()->transform(function ($a) use ($base) {
            $a->public_link = $a->hash ? "{$base}/register/student/{$a->hash}" : null;
            return $a;
        });

        // ====== Cursos para filtros ======
        $courses = Course::select('id', 'title')->orderBy('title')->get();

        return inertia('Admin/Activations/Index', [
            'activations' => $activations,
            'courses'     => $courses,
            'filters'     => [
                'q'         => $search,
                'course_id' => $request->input('course_id'),
                'active'    => $request->input('active'),
                'sortBy'    => $sortBy,
                'sortDir'   => $sortDir,
                'perPage'   => $perPage,
            ],
        ]);
    }


 public function store(Request $request)
    {
        // Validación base
        $validated = $request->validate([
            'name'      => ['required', 'string', 'min:3', 'max:120'],
            'email'     => ['required', 'email', 'max:190'],
            'phone'     => ['nullable', 'string', 'max:40'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
        ]);

        // Normalización
        $name  = trim($validated['name']);
        $email = strtolower(trim($validated['email']));
        $phone = isset($validated['phone']) && $validated['phone'] !== '' ? trim($validated['phone']) : null;

        // Verificación estricta de duplicados (case-insensitive)
       if (User::whereRaw('LOWER(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'El email ya está registrado en usuarios.',
            ]);
        }
        if (Activation::whereRaw('LOWER(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'El email ya está en activaciones.',
            ]);
        }

        // Código/Hash/Link
        $code  = $this->generateUniqueCode();
        $nonce = Str::random(16);
        $hash  = hash_hmac('sha256', "{$name}|{$email}|{$nonce}", config('app.key'));
        $link  = URL::to('/register/student/' . $hash);

        // Guardar
        $activation = Activation::create([
            'name'      => $name,
            'email'     => $email,
            'phone'     => $phone,
            'course_id' => (int) $validated['course_id'],
            'code'      => $code,
            'hash'      => $hash,
            'active'    => false,
            'created'   => now(),
        ]);

        // Enviar correo (atrapar errores de transporte sin romper UX)
        try {
            Mail::to($email)->send(new ActivationInvitationMail(
                name: $name,
                link: $link,
                code: $code
            ));
        } catch (\Throwable $e) {
            report($e);
            // Puedes decidir no fallar la creación si el correo no sale:
            // return back()->with('error', 'La activación se creó, pero no se pudo enviar el correo.');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'activation' => ['hash' => $hash, 'code' => $code, 'link' => $link],
            ], 201);
        }

        return to_route('admin.activations.index')
            ->with('success', 'Activación creada y enviada por correo.')
            ->with('activation_created', [
                'id'   => $activation->id,
                'hash' => $hash,
                'code' => $code,
                'link' => $link,
            ]);
    }

   private function generateUniqueCode(): string
    {
        do {
            $numbers = strval(random_int(100, 999));  // 3 dígitos
            $letters = '';
            for ($i=0; $i<3; $i++) {
                $letters .= chr(random_int(65, 90)); // A-Z
            }
            $chars = str_split($numbers.$letters);
            shuffle($chars);
            $code = implode('', $chars);
        } while (Activation::where('code', $code)->exists());

        return $code;
    }


    public function resend(Activation $activation, Request $request)
{
    try {
        // Asegura que tenga hash (por si tienes registros antiguos sin hash)
        if (!$activation->hash) {
            $nonce = \Illuminate\Support\Str::random(16);
            $activation->hash = hash_hmac('sha256', "{$activation->name}|{$activation->email}|{$nonce}", config('app.key'));
            $activation->save();
        }

        $link = \Illuminate\Support\Facades\URL::to('/register/student/'.$activation->hash);

        \Illuminate\Support\Facades\Mail::to($activation->email)->send(
            new \App\Mail\ActivationInvitationMail(
                name: $activation->name,
                link: $link,
                code: $activation->code
            )
        );

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Invitación reenviada'], 200);
        }

        return back()->with('success', 'La invitación se ha reenviado correctamente.');
    } catch (\Throwable $e) {
        \Log::error('Error al reenviar activación: '.$e->getMessage());

        if ($request->wantsJson()) {
            return response()->json(['ok' => false, 'message' => 'No se pudo reenviar la invitación'], 500);
        }
        return back()->with('error', 'No se pudo reenviar la invitación.');
    }
}


 public function destroy(Activation $activation, Request $request)
    {
        try {
            $activation->delete();

            if ($request->wantsJson()) {
                return response()->json(['ok' => true, 'message' => 'Activación eliminada'], 200);
            }

            return back()->with('success', 'La activación se eliminó correctamente.');
        } catch (\Throwable $e) {
            \Log::error('Error al eliminar activación: '.$e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'Ocurrió un error al eliminar'], 500);
            }
            return back()->with('error', 'Ocurrió un error al eliminar la activación.');
        }
    }



  public function resend(Activation $activation, Request $request)
    {
        try {
            $link = URL::to('/register/student/'.$activation->hash);

            Mail::to($activation->email)->send(
                new ActivationInvitationMail(
                    name: $activation->name,
                    link: $link,
                    code: $activation->code
                )
            );

            if ($request->wantsJson()) {
                return response()->json(['ok' => true, 'message' => 'Invitación reenviada'], 200);
            }

            return back()->with('success', 'La invitación se ha reenviado correctamente.');
        } catch (\Throwable $e) {
            \Log::error('Error al reenviar activación: '.$e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'No se pudo reenviar la invitación'], 500);
            }
            return back()->with('error', 'No se pudo reenviar la invitación.');
        }
    }


 public function toggle(Activation $activation, Request $request)
    {
        try {
            $activation->active = !$activation->active;
            $activation->save();

            if ($request->wantsJson()) {
                return response()->json(['ok' => true, 'active' => $activation->active], 200);
            }

            return back()->with('success', 'Estado de la activación actualizado.');
        } catch (\Throwable $e) {
            \Log::error('Error al actualizar estado de activación: '.$e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'No se pudo actualizar el estado'], 500);
            }
            return back()->with('error', 'No se pudo actualizar el estado.');
        }
    }


}
