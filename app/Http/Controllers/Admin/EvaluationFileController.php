<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\EvaluationFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Inertia\Inertia;

class EvaluationFileController extends Controller
{
    /**
     * Lista los archivos de una evaluación, ordenados por "order".
     */
    public function index(Request $request, Evaluation $evaluation)
    {
        $files = $evaluation->files()
            ->ordered()
            ->paginate($request->integer('per_page', 20))
            ->appends($request->query());

        // Si ya tienes la vista Inertia, devuélvela.
        // De lo contrario, retorna JSON para integrarlo después en el front.
        if ($request->wantsJson()) {
            return response()->json([
                'evaluation' => $evaluation->only(['id','title']),
                'files'      => $files,
            ]);
        }

        return Inertia::render('Admin/Evaluations/Files/Index', [
            'evaluation' => $evaluation->only(['id','title']),
            'files'      => $files,
        ]);
    }

    /**
     * Guarda un nuevo archivo para la evaluación.
     */
    public function store(Request $request, Evaluation $evaluation)
    {
        $validated = $request->validate([
            'title'       => ['required','string','max:150'],
            'description' => ['nullable','string','max:2000'],
            'file'        => ['required','file','max:102400', // 100 MB
                // Ajusta a tus tipos reales:
                'mimes:pdf,jpg,jpeg,png,webp,mp4,avi,mov,doc,docx,xls,xlsx,zip,rar,txt'
            ],
            'order'       => ['nullable','integer','min:0'],
            'uploaded'    => ['nullable','date'],
        ], [
            'file.max'    => 'El archivo no debe exceder 100 MB.',
            'file.mimes'  => 'Tipo de archivo no permitido.',
        ]);

        // Calcular el siguiente order si no se envía
        $nextOrder = $evaluation->files()->max('order');
        $nextOrder = is_null($nextOrder) ? 1 : $nextOrder + 1;
        $order     = (int) ($validated['order'] ?? $nextOrder);

        // Ruta base: storage/app/public/evaluations/{evaluation_id}/files
        $disk = 'public';
        $dir  = "evaluations/{$evaluation->id}/files";

        $uploadedFile = $request->file('file');
        $storedPath   = $uploadedFile->store($dir, $disk);

        $mime    = $uploadedFile->getClientMimeType() ?: $uploadedFile->getMimeType();
        $ext     = Str::lower($uploadedFile->getClientOriginalExtension());
        $size    = $uploadedFile->getSize(); // bytes
        $date    = isset($validated['uploaded'])
            ? Carbon::parse($validated['uploaded'])->toDateString()
            : now()->toDateString();

        $record = $evaluation->files()->create([
            'order'        => $order,
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'file_uploaded'=> $storedPath,                   // guarda la ruta relativa en el disco
            'type'         => $ext ?: $mime,                 // puedes guardar ext o mime
            'size'         => (int) $size,
            'uploaded'     => $date,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'file' => $record], 201);
        }

        return back()->with('success', 'Archivo agregado correctamente.');
    }

    /**
     * Muestra un archivo puntual (útil si necesitas ver detalles).
     */
    public function show(Request $request, Evaluation $evaluation, EvaluationFile $file)
    {
        $this->ensureBelongsTo($evaluation, $file);

        if ($request->wantsJson()) {
            return response()->json(['file' => $file]);
        }

        return Inertia::render('Admin/Evaluations/Files/Show', [
            'evaluation' => $evaluation->only(['id','title']),
            'file'       => $file,
        ]);
    }

    /**
     * Actualiza metadatos y permite reemplazar el archivo físico.
     */
    public function update(Request $request, Evaluation $evaluation, EvaluationFile $file)
    {
        $this->ensureBelongsTo($evaluation, $file);

        $validated = $request->validate([
            'title'       => ['sometimes','required','string','max:150'],
            'description' => ['nullable','string','max:2000'],
            'order'       => ['nullable','integer','min:0'],
            'uploaded'    => ['nullable','date'],
            'file'        => ['nullable','file','max:102400',
                'mimes:pdf,jpg,jpeg,png,webp,mp4,avi,mov,doc,docx,xls,xlsx,zip,rar,txt'
            ],
        ]);

        $file->fill([
            'title'       => $validated['title'] ?? $file->title,
            'description' => array_key_exists('description', $validated) ? $validated['description'] : $file->description,
        ]);

        if (isset($validated['order'])) {
            $file->order = (int) $validated['order'];
        }
        if (isset($validated['uploaded'])) {
            $file->uploaded = Carbon::parse($validated['uploaded'])->toDateString();
        }

        if ($request->hasFile('file')) {
            // Eliminar archivo anterior si existe
            if ($file->file_uploaded && Storage::disk('public')->exists($file->file_uploaded)) {
                Storage::disk('public')->delete($file->file_uploaded);
            }

            $disk = 'public';
            $dir  = "evaluations/{$evaluation->id}/files";
            $uploadedFile = $request->file('file');
            $storedPath   = $uploadedFile->store($dir, $disk);

            $mime    = $uploadedFile->getClientMimeType() ?: $uploadedFile->getMimeType();
            $ext     = Str::lower($uploadedFile->getClientOriginalExtension());
            $size    = $uploadedFile->getSize();

            $file->file_uploaded = $storedPath;
            $file->type          = $ext ?: $mime;
            $file->size          = (int) $size;
        }

        $file->save();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'file' => $file]);
        }

        return back()->with('success', 'Archivo actualizado correctamente.');
    }

    /**
     * Elimina un archivo (físico y registro).
     */
    public function destroy(Request $request, Evaluation $evaluation, EvaluationFile $file)
    {
        $this->ensureBelongsTo($evaluation, $file);

        if ($file->file_uploaded && Storage::disk('public')->exists($file->file_uploaded)) {
            Storage::disk('public')->delete($file->file_uploaded);
        }

        $file->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Archivo eliminado correctamente.');
    }

    /**
     * Reordena múltiples archivos en una sola operación.
     * Recibe: { orders: [ {id: 10, order: 1}, {id: 12, order: 2}, ... ] }
     */
    public function reorder(Request $request, Evaluation $evaluation)
    {
        $data = $request->validate([
            'orders'               => ['required','array','min:1'],
            'orders.*.id'          => ['required','integer','exists:evaluations_files,id'],
            'orders.*.order'       => ['required','integer','min:0'],
        ]);

        DB::transaction(function () use ($data, $evaluation) {
            foreach ($data['orders'] as $row) {
                // Asegura pertenencia a la evaluación
                $file = EvaluationFile::where('evaluation_id', $evaluation->id)
                    ->where('id', $row['id'])
                    ->first();

                if ($file) {
                    $file->update(['order' => (int) $row['order']]);
                }
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Orden actualizado.');
    }

    /**
     * Descarga el archivo almacenado.
     */
    public function download(Evaluation $evaluation, EvaluationFile $file)
    {
        $this->ensureBelongsTo($evaluation, $file);

        if (!$file->file_uploaded || !Storage::disk('public')->exists($file->file_uploaded)) {
            abort(404);
        }

        // Nombre de descarga más legible
        $downloadName = $file->title
            ? Str::slug($file->title) . '.' . ($file->type ?: 'bin')
            : basename($file->file_uploaded);

        return Storage::disk('public')->download($file->file_uploaded, $downloadName);
    }

    /**
     * Verifica que el archivo pertenezca a la evaluación.
     */
    protected function ensureBelongsTo(Evaluation $evaluation, EvaluationFile $file): void
    {
        if ($file->evaluation_id !== $evaluation->id) {
            abort(404);
        }
    }
}
