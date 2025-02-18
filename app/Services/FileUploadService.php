<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class FileUploadService
{
    /**
     * Maneja la subida de archivos y devuelve la ruta.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public function uploadFile(Request $request)
    {
        // Validar que el archivo esté presente
        $request->validate([
            'file' => 'required|file',
        ]);

        // Subir el archivo al almacenamiento 'public'
        $path = $request->file('file')->store('uploads', 'public');

        // Devolver el nombre del archivo (solo el nombre, no la ruta completa)
        return basename($path);
    }

    /**
     * Obtiene el archivo desde el almacenamiento público y lo devuelve.
     *
     * @param  string  $filename
     * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Http\JsonResponse
     */
    public function getFile($filename)
    {
        // Ruta completa dentro de 'storage/app/public/uploads/'
        $path = 'uploads/' . $filename;

        // Verificar si el archivo existe en el disco 'public'
        if (Storage::disk('public')->exists($path)) {
            // Si existe, devolver el archivo
            return response()->file(storage_path('app/public/' . $path));
        }

        // Si no existe, devolver un error 404
        return response()->json(['error' => 'Archivo no encontrado'], SymfonyResponse::HTTP_NOT_FOUND);
    }
}
