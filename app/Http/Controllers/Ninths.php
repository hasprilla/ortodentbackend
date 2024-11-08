<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use function Pest\Laravel\json;
use Illuminate\Support\Facades\DB;
use App\Models\Ninth;
use App\Models\Day;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Ninths extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Ninth::with('days')->get();

        if ($data->isEmpty()) {
            return response()->json(['message' => 'No novenas found'], 404);  // Retorna un 404 si no se encuentran novenas
        }

        return response()->json(['data' => $data], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'prayer_every_day' => 'required|string',
            'days' => 'required|array',
            'days.*.title' => 'required|string|max:255',
        ]);

        // Crear el registro en la tabla `ninths`
        $ninth = Ninth::create([
            'title' => $validated['title'],
            'prayer_every_day' => $validated['prayer_every_day'],
        ]);

        // Crear los días asociados en la tabla `days`
        foreach ($validated['days'] as $dayData) {
            // Creamos cada día asociado al `ninth` recién creado
            Day::create([
                'title' => $dayData['title'],
                'ninth_id' => $ninth->id, // Relacionamos el día con el `ninth`
            ]);
        }

        // Enviar una respuesta JSON de éxito
        return response()->json([
            'message' => 'Ninth y días creados con éxito.',
            'data' =>  Ninth::with('days')->get()->last(),
            // 'days' => $ninth->days,
        ], 201); // 201 para indicar que la creación fue exitosa
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Intentamos obtener la novena con sus días asociados
            $ninth = Ninth::with('days')->findOrFail($id);

            // Si la novena existe, la devolvemos como respuesta JSON
            return response()->json(['data' => $ninth], 200);
        } catch (ModelNotFoundException $e) {
            // Si no se encuentra la novena, devolvemos un error personalizado
            return response()->json(['error' => 'Ninth not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // Validación de los datos
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'prayer_every_day' => 'required|string',
            'days' => 'required|array', // Un array de días
            'days.*.title' => 'required|string|max:255', // Títulos de cada día
        ]);

        // Buscar el `Ninth` por el ID
        $ninth = Ninth::findOrFail($id);

        // Actualizar el `Ninth` con los nuevos datos
        $ninth->update([
            'title' => $validated['title'],
            'prayer_every_day' => $validated['prayer_every_day'],
        ]);

        // Eliminar los días existentes antes de agregar los nuevos
        $ninth->days()->delete();

        // Crear los nuevos días asociados al `Ninth` actualizado
        foreach ($validated['days'] as $dayData) {
            Day::create([
                'title' => $dayData['title'],
                'ninth_id' => $ninth->id, // Relacionamos el día con el `ninth`
            ]);
        }

        // Responder con un mensaje de éxito y los datos actualizados
        return response()->json([
            'message' => 'Ninth y días actualizados con éxito.',
            'data' =>Ninth::with('days')->findOrFail($id)
        ], 200); // 200 OK
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
