<?php

namespace App\Http\Controllers\Api;
use App\Models\Habit;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreHabitRequest;
use App\Http\Requests\UpdateHabitRequest;

class HabitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $habits = Habit::where('user_id', $request->user()->id)->get();
        return response()->json([
                'success'=>true,
                'data' => $habits,
                'message' => 'Habits récupérées avec succes',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHabitRequest $request): JsonResponse
    {
        // $validated = $request->validate([
        //     'title' => 'required|string|max:100',
        //     'description' => 'nullable|string',
        //     'frequency' => 'required|in:daily,weekly,monthly',
        //     'target_days' => 'required|integer|min:1',
        //     'color' => 'nullable|string',
        //     'is_active' => 'boolean'
        // ]);
        $validated= $request->validated();
        $habit = Habit::create([
            ...$validated,
            'user_id' => $request->user()->id
        ]);
        return response()->json([
            'success' => true,
            'data' => $habit,
            'message' => 'Habit créée avec succés'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,string $id): JsonResponse
    {
        $habit = Habit::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $habit,
            'message' => 'Habit récupérée'
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHabitRequest $request, string $id): JsonResponse
    {
       $habit = Habit::where('user_id', $request->user()->id)->findOrFail($id);
       $validated = $request->validated();
    //    $validated = $request->validate([
    //     'title' => 'string|max:100',
    //     'description' => 'nullable|string',
    //     'frequency' => 'in:daily,weekly,monthly',
    //     'target_days' => 'nullable|integer|min:1',
    //     'color' => 'nullable|string',
    //     'is_active' => 'boolean'
    //    ]);
       $habit->update($validated);
       return response()->json([
            'success' => true,
            'data' => $habit,
            'message' => 'Habit mis à jour'
       ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id): JsonResponse
    {
        $habit = Habit::where('user_id', $request->user()->id)->findOrFail($id);
        $habit->delete();
        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Habit supprimée'
        ]);
    }
}
