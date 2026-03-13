<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Habit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function habitStats(Request $request, $id): JsonResponse
    {
        $habit = Habit::where('user_id', $request->user()->id)
            ->with('logs')
            ->findOrFail($id);

        $dates = $habit->logs
            ->pluck('logged_at')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $totalCompletions = count($dates);
        $currentStreak = $this->calculateCurrentStreak($dates);
        $longestStreak = $this->calculateLongestStreak($dates);
        $completionRate = $this->calculateCompletionRate($dates, 30);

        return response()->json([
            'success' => true,
            'data' => [
                'habit_id' => $habit->id,
                'title' => $habit->title,
                'current_streak' => $currentStreak,
                'longest_streak' => $longestStreak,
                'total_completions' => $totalCompletions,
                'completion_rate' => $completionRate,
            ],
            'message' => 'Statistiques de l’habitude récupérées avec succès',
        ], 200);
    }

    public function overview(Request $request): JsonResponse
    {
        $habits = Habit::where('user_id', $request->user()->id)
            ->where('is_active', true)
            ->with('logs')
            ->get();

        $activeHabitsCount = $habits->count();
        $today = Carbon::today()->toDateString();

        $completedTodayCount = $habits->filter(function ($habit) use ($today) {
            return $habit->logs->contains(function ($log) use ($today) {
                return Carbon::parse($log->logged_at)->toDateString() === $today;
            });
        })->count();

        $bestHabit = null;
        $bestStreak = 0;

        foreach ($habits as $habit) {
            $dates = $habit->logs
                ->pluck('logged_at')
                ->map(fn ($date) => Carbon::parse($date)->toDateString())
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            $currentStreak = $this->calculateCurrentStreak($dates);

            if ($currentStreak > $bestStreak) {
                $bestStreak = $currentStreak;
                $bestHabit = [
                    'id' => $habit->id,
                    'title' => $habit->title,
                    'current_streak' => $currentStreak,
                ];
            }
        }

        $globalCompletionRate = $this->calculateGlobalCompletionRate($habits, 7);

        return response()->json([
            'success' => true,
            'data' => [
                'active_habits_count' => $activeHabitsCount,
                'completed_today_count' => $completedTodayCount,
                'best_current_streak_habit' => $bestHabit,
                'global_completion_rate_7_days' => $globalCompletionRate,
            ],
            'message' => 'Vue globale récupérée avec succès',
        ], 200);
    }

    private function calculateCurrentStreak(array $dates): int
    {
        if (empty($dates)) {
            return 0;
        }

        $dates = array_reverse($dates);
        $streak = 0;
        $expectedDate = Carbon::parse($dates[0]);

        if (!$expectedDate->isToday() && !$expectedDate->isYesterday()) {
            return 0;
        }

        foreach ($dates as $date) {
            $currentDate = Carbon::parse($date);

            if ($currentDate->toDateString() === $expectedDate->toDateString()) {
                $streak++;
                $expectedDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    private function calculateLongestStreak(array $dates): int
    {
        if (empty($dates)) {
            return 0;
        }

        $longest = 1;
        $current = 1;

        for ($i = 1; $i < count($dates); $i++) {
            $previousDate = Carbon::parse($dates[$i - 1]);
            $currentDate = Carbon::parse($dates[$i]);

            if ($previousDate->copy()->addDay()->toDateString() === $currentDate->toDateString()) {
                $current++;
                $longest = max($longest, $current);
            } else {
                $current = 1;
            }
        }

        return $longest;
    }

    private function calculateCompletionRate(array $dates, int $days = 30): float
    {
        $startDate = Carbon::today()->subDays($days - 1);

        $count = collect($dates)->filter(function ($date) use ($startDate) {
            return Carbon::parse($date)->greaterThanOrEqualTo($startDate);
        })->count();

        return round(($count / $days) * 100, 2);
    }

    private function calculateGlobalCompletionRate($habits, int $days = 7): float
    {
        $activeHabitsCount = $habits->count();

        if ($activeHabitsCount === 0) {
            return 0;
        }

        $totalPossible = $activeHabitsCount * $days;
        $totalCompleted = 0;
        $startDate = Carbon::today()->subDays($days - 1);

        foreach ($habits as $habit) {
            $count = $habit->logs
                ->filter(function ($log) use ($startDate) {
                    return Carbon::parse($log->logged_at)->greaterThanOrEqualTo($startDate);
                })
                ->pluck('logged_at')
                ->map(fn ($date) => Carbon::parse($date)->toDateString())
                ->unique()
                ->count();

            $totalCompleted += $count;
        }

        return round(($totalCompleted / $totalPossible) * 100, 2);
    }
}