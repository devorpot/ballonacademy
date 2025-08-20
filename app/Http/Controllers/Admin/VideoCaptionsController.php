<?php
// app/Http/Controllers/Admin/VideoCaptionsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoCaption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VideoCaptionsController extends Controller
{
    // Lista de captions por video (JSON o Inertia)
    public function index(Video $video)
    {
        $captions = $video->captions()->latest()->get();
        return response()->json($captions);
        // return Inertia::render('Admin/Videos/Captions/Index', compact('video', 'captions'));
    }

    public function store(Request $request, Video $video)
    {
        $data = $this->validateData($request);

        // Manejo de archivo/cuerpo
        $this->handleUploadOrBody($request, $data);

        $data['video_id'] = $video->id;

        $caption = VideoCaption::create($data);

        return response()->json($caption, 201);
        // return redirect()->back()->with('success', 'Subtítulo creado');
    }

    public function show(VideoCaption $caption)
    {
        return response()->json($caption);
    }

  public function update(Request $request, Video $video, $caption)
{
    // Buscar el caption perteneciente a este video
    $caption = $video->captions()->findOrFail($caption);

    $data = $this->validateData($request, updating: true);

    // Si sube nuevo archivo o envía nuevo cuerpo, manejamos actualización
    $this->handleUploadOrBody($request, $data, $caption);

    $caption->fill($data)->save();

    return response()->json($caption);
}


    public function destroy(Video $video, VideoCaption $caption)
    {
        if ($caption->video_id !== $video->id) {
            return response()->json(['message' => 'Subtítulo no encontrado'], 404);
        }

        try {
            $this->deleteStoredFile($caption);
            $caption->delete();
            return response()->json(['deleted' => true]);
        } catch (\Throwable $e) {
            \Log::error('Error al eliminar subtítulo', [
                'video_id'    => $video->id,
                'caption_id'  => $caption->id,
                'error'       => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Error al eliminar'], 500);
        }
    }

    /* ---------- Helpers ---------- */

    protected function validateData(Request $request, bool $updating = false): array
    {
        // En store: requiere al menos uno (file o captions).
        // En update: ambos son opcionales, pero si vienen se validan.
        $presence = $updating ? 'sometimes' : 'required';

        // Nota: puedes adaptar la lista de idiomas válidos si quieres restringir
        return $request->validate([
            'lang'     => [$presence, 'string', 'max:10'],
            'file'     => ['nullable', 'file', 'mimes:vtt,srt', 'max:10240'],
            'captions' => ['nullable', 'string'], // contenido WebVTT o SRT
        ], [
            'file.mimes' => 'El archivo debe ser .vtt o .srt',
        ], [
            'lang' => 'idioma',
        ]);
    }

    protected function handleUploadOrBody(Request $request, array &$data, ?VideoCaption $existing = null): void
    {
        // Reglas:
        // - Si viene archivo, lo guardamos (si es .srt lo convertimos a .vtt para playback).
        // - Si viene "captions" (texto), lo guardamos en DB y además generamos un .vtt para servir por <track>.
        // - Si actualiza archivo, borramos el anterior.
        // - Si actualiza solo "captions", generamos/reemplazamos archivo .vtt (de ser posible).

        $baseDir = 'videos/captions';

        $newFilePath = null;
        $newBody     = $data['captions'] ?? null;

        // 1) Archivo subido
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $ext  = strtolower($file->getClientOriginalExtension());

            // Eliminamos archivo anterior si existía
            if ($existing?->file) {
                Storage::disk('public')->delete($existing->file);
            }

            if ($ext === 'vtt') {
                $newFilePath = $file->store($baseDir, 'public');
                // Si es vtt subido, podemos leer contenido para DB si quieres
                $newBody = $newBody ?? @file_get_contents($file->getRealPath());
            } else {
                // srt -> convertimos a vtt y guardamos .vtt
                $srtContent = @file_get_contents($file->getRealPath());
                $vttContent = $this->convertSrtToVtt($srtContent);

                $filename = pathinfo($file->hashName(), PATHINFO_FILENAME) . '.vtt';
                $newFilePath = $baseDir . '/' . $filename;
                Storage::disk('public')->put($newFilePath, $vttContent);

                $newBody = $newBody ?? $vttContent; // guardamos VTT en DB para edición rápida
            }
        }

        // 2) Si NO hay archivo pero hay "captions" (texto plano), generamos/actualizamos .vtt
        if (!$newFilePath && $newBody) {
            // Si viene en SRT, convertimos; si ya es VTT lo dejamos
            $content = $this->looksLikeSrt($newBody) ? $this->convertSrtToVtt($newBody) : $this->ensureVttHeader($newBody);

            // Si existía archivo antes, lo reemplazamos con mismo nombre .vtt
            $target = $existing?->file ?: ($baseDir.'/'.uniqid('cap_', true).'.vtt');
            Storage::disk('public')->put($target, $content);
            $newFilePath = $target;
            $newBody     = $content;
        }

        // 3) Si ni archivo ni captions, en store valid lo habrá impedido.
        // En update, si no vino nada, mantenemos lo existente.

        if ($newFilePath) {
            $data['file'] = $newFilePath;
        }

        if (array_key_exists('captions', $data)) {
            $data['captions'] = $newBody;
        }
    }

    protected function deleteStoredFile(VideoCaption $caption): void
    {
        if ($caption->file) {
            Storage::disk('public')->delete($caption->file);
        }
    }

    /* --- Conversores simples SRT -> VTT --- */

    protected function convertSrtToVtt(?string $srt): string
    {
        $srt = (string) $srt;
        // Reemplaza coma por punto en timecodes y agrega cabecera WEBVTT
        $vtt = preg_replace('/(\d{2}:\d{2}:\d{2}),(\d{3})/m', '$1.$2', $srt);
        $vtt = "WEBVTT\n\n" . trim($vtt) . "\n";
        return $vtt;
    }

    protected function looksLikeSrt(string $content): bool
    {
        // heurística básica: timecodes con coma
        return (bool) preg_match('/\d{2}:\d{2}:\d{2},\d{3}\s-->\s\d{2}:\d{2}:\d{2},\d{3}/', $content);
    }

    protected function ensureVttHeader(string $content): string
    {
        $trim = ltrim($content);
        if (strncasecmp($trim, 'WEBVTT', 6) !== 0) {
            return "WEBVTT\n\n" . $content;
        }
        return $content;
        }
}
