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
        // Validar que el archivo esté presente (puede ser uno o múltiples)
        $request->validate([
            'file' => 'required|array',  // Los archivos deben ser un array
            'file.*' => 'file',  // Cada archivo debe ser un archivo válido
        ]);

        // Crear un array para almacenar los nombres de los archivos subidos
        $uploadedFiles = [];

        // Si hay múltiples archivos, iterar y almacenarlos
        foreach ($request->file('file') as $file) {
            // Subir el archivo al almacenamiento 'public'
            $path = $file->store('uploads', 'public');

            // Guardar solo el nombre del archivo (no la ruta completa)
            $uploadedFiles[] = basename($path);
        }

        // Devolver los nombres de los archivos subidos
        return $uploadedFiles;
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
