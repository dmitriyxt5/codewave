<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradesController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'topic_id' => 'required|integer'
        ]);

        $rows = DB::table('test_results as tr')
            ->join('tests as t', 't.id', '=', 'tr.test_id')
            ->join('users as u', 'u.id', '=', 'tr.user_id')
            ->where('t.topic_id', $data['topic_id'])
            ->select(
                'tr.id',
                'u.username',
                DB::raw("TRIM(CONCAT(u.lastname,' ',u.firstname,COALESCE(CONCAT(' ',u.patronymic),''))) as fullname"),
                DB::raw('tr.score as grade'),
                DB::raw('DATE(tr.created_at) as date')
            )
            ->orderBy('u.lastname')
            ->get();

        return response()->json($rows);
    }
}
