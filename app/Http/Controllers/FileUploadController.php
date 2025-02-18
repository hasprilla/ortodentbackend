<?php

namespace App\Http\Controllers;

use App\Services\FileUploadService;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    public function upload(Request $request)
    {
        $filename = $this->fileUploadService->uploadFile($request);
        return response()->json([
            'message' => 'Archivo subido con éxito',
            'filename' => $filename
        ]);
    }

    public function getFile($filename)
    {
        return $this->fileUploadService->getFile($filename);
    }
}
