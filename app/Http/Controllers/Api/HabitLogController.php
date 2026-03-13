<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Habit;
use App\Models\HabitLog;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreHabitLogRequest;

class HabitLogController extends Controller
{
    public function store(StoreHabitLogRequest $request, $id): JsonResponse{
        $habit = Habit::where('user_id', $request->user()->id)->findOrFail($id);
        $validated = $request->validated();
        $loggedAt = $validated['logged_at'] ?? now()->toDateString();

        $alreadyExists = HabitLog::where('habit_id', $habit->id)->where('logged_at', $loggedAt)->exists();

        if($alreadyExists){
            return response()->json([
                'success' => false,
                'errors'=>[
                    'logged_at'=>['Cette habitude est déjà marquée pour cetee date.']
                ],
                'message' => 'log déà existant'
            ],422);
        }
        $log = HabitLog::create([
            'habit_id' => $habit->id,
            'logged_at' => $loggedAt,
            'note' => $validated['note'] ?? null,
        ]);
        return response()->json([
            'success' => true,
            'data' => $log,
            'message' => 'Log créé avec succés'
        ],201);
    }

    public function index(Request $request, $id): JsonResponse{
        $habit = Habit::where('user_id', $request->user()->id)->findOrFail($id);
        $logs = HabitLog::where('habit_id', $habit->id)->orderByDesc('logged_at')->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
            'message' => 'Historique des logs récupéré avec succès'
        ]);
    }
    public function destroy(Request $request, $id, $logId): JsonResponse{
        $habit = Habit::where('user_id', $request->user()->id)->findOrFail($id);
        $log = HabitLog::where('habit_id', $habit->id)->findOrFail($logId);
        $log->delete();
        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Log supprimé avec succés'
        ]);
    }
}
