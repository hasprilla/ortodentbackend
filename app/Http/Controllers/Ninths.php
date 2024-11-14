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

    public function index()
    {
        $data = Ninth::with('days')->get();

        if ($data->isEmpty()) {
            return response()->json(['message' => 'No novenas found'], 404);
        }

        return response()->json(['data' => $data], 200);
    }


    public function store(Request $request)
    {

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'by_signal' => 'required|string',
            'contrition' => 'required|string',
            'prayer_every_day' => 'required|string',
            'days' => 'required|array',
            'days.*.title' => 'required|string|max:255',
            'days.*.f_sentence' => 'nullable|string',
            'days.*.s_sentence' => 'nullable|string',
        ]);


        $ninthExists = Ninth::where('title', $validated['title'])->exists();

        if ($ninthExists) {
            return response()->json([
                'message' => 'Ya existe un Ninth con ese título.',
            ], 409);
        }


        DB::beginTransaction();

        try {

            $ninth = Ninth::create([
                'title' => $validated['title'],
                'by_signal' => $validated['by_signal'],
                'contrition' => $validated['contrition'],
                'prayer_every_day' => $validated['prayer_every_day'],
            ]);


            $daysData = collect($validated['days'])->map(function ($dayData) use ($ninth) {
                return [
                    'title' => $dayData['title'],
                    'f_sentence' => $dayData['f_sentence'] ?? null,
                    's_sentence' => $dayData['s_sentence'] ?? null,
                    'ninth_id' => $ninth->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();


            Day::insert($daysData);
            DB::commit();
            return response()->json([
                'message' => 'Ninth y días creados con éxito.',
                'data' => $ninth->load('days'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Ocurrió un error al crear el Ninth y sus días.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function show(string $id)
    {
        try {
            $ninth = Ninth::with('days')->findOrFail($id);
            return response()->json(['data' => $ninth], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Ninth not found'], 404);
        }
    }


    public function update(Request $request, string $id)
    {

        // Validate input
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'by_signal' => 'required|string',
            'contrition' => 'required|string',
            'prayer_every_day' => 'required|string',
            'days' => 'required|array',
            'days.*.title' => 'required|string|max:255',
            'days.*.f_sentence' => 'nullable|string',
            'days.*.s_sentence' => 'nullable|string',
        ]);

        // Find the ninth record
        $ninth = Ninth::find($id);

        if (!$ninth) {
            return response()->json([
                'message' => 'El Ninth con el ID proporcionado no existe.',
            ], 404);
        }

        // Start transaction
        DB::beginTransaction();

        try {
            // Update the ninth record
            $ninth->update([
                'title' => $validated['title'],
                'by_signal' => $validated['by_signal'],
                'contrition' => $validated['contrition'],
                'prayer_every_day' => $validated['prayer_every_day'],
            ]);

            // Delete existing days related to the ninth
            $ninth->days()->delete();

            // Prepare days data for insertion
            $daysData = collect($validated['days'])->map(function ($dayData) use ($ninth) {
                return [
                    'title' => $dayData['title'],
                    'f_sentence' => $dayData['f_sentence'] ?? null,
                    's_sentence' => $dayData['s_sentence'] ?? null,
                    'ninth_id' => $ninth->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            // Insert new days
            Day::insert($daysData);

            // Commit transaction
            DB::commit();

            // Reload the ninth model with its associated days
            $ninth->load('days');

            return response()->json([
                'message' => 'Ninth y días actualizados con éxito.',
                'data' => $ninth,
            ], 200);
        } catch (\Exception $e) {
            // Rollback transaction in case of error
            DB::rollBack();

            return response()->json([
                'message' => 'Ocurrió un error al actualizar el Ninth y sus días.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {

        $ninth = Ninth::find($id);


        if (!$ninth) {
            return response()->json([
                'message' => 'El Ninth con el ID proporcionado no existe.',
            ], 404);
        }


        DB::beginTransaction();

        try {

            $ninth->days()->delete();


            $ninth->delete();


            DB::commit();


            return response()->json([
                'message' => 'Ninth y días eliminados con éxito.',
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();


            return response()->json([
                'message' => 'Ocurrió un error al eliminar el Ninth y sus días.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
