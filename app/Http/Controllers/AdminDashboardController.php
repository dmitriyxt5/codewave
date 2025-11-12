<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Lection;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Command;
use App\Models\Test;
use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 1);
        $startDate = now()->subDays($period);

        $studentsCount = User::where('role', 'user')->count();
        $adminsCount = User::where('role', 'admin')->count();
        $topicsCount = Topic::count();
        $testsCount = \App\Models\Test::count();
        $commandsCount = Command::count();

        $lastActivity = DB::table('personal_access_tokens')
            ->select('last_used_at', 'tokenable_id')
            ->whereNotNull('last_used_at')
            ->where('last_used_at', '>=', $startDate)
            ->orderByDesc('last_used_at')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $user = User::find($item->tokenable_id);
                return [
                    'user_id' => $user?->id,
                    'username' => $user?->username,
                    'role' => $user?->role,
                    'last_used_at' => $item->last_used_at,
                ];
            });

        $activityByHour = DB::table('personal_access_tokens')
            ->selectRaw('HOUR(last_used_at) as hour, COUNT(*) as count')
            ->whereNotNull('last_used_at')
            ->where('last_used_at', '>=', $startDate)
            ->groupByRaw('HOUR(last_used_at)')
            ->orderBy('hour')
            ->get();

        // последние 10 лекций по дате создания
        $lections = Lection::with(['subject:id,name', 'user:id,username'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'subject_id', 'user_id', 'created_at']);

        $commandsBySubject = Command::select(
                'subject_id',
                DB::raw('COALESCE(SUM(balls),0) as total_balls'),
                DB::raw('COUNT(id) as teams_count')
            )
            ->groupBy('subject_id')
            ->get()
            ->map(function ($item) {
                $subject = Subject::find($item->subject_id);
                return [
                    'subject_id' => $item->subject_id,
                    'subject_name' => $subject?->name ?? 'Без предмета',
                    'total_balls' => (int) $item->total_balls,
                    'teams_count' => (int) $item->teams_count,
                ];
            });

        return response()->json([
            'students_count' => $studentsCount,
            'admins_count' => $adminsCount,
            'topics_count' => $topicsCount,
            'tests_count' => $testsCount,
            'commands_count' => $commandsCount,
            'last_activity' => $lastActivity,
            'activity_by_hour' => $activityByHour,
            'lections' => $lections,
            'commands_by_subject' => $commandsBySubject,
        ]);
    }

public function showSubjectTeams($subjectId)
{
    $commands = Command::where('subject_id', $subjectId)
        ->with(['leader:id,username', 'subject:id,name'])
        ->get(['id', 'subject_id', 'leader_id', 'balls', 'member_ids']);

    return response()->json($commands);
}


}
