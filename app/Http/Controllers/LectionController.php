<?php

namespace App\Http\Controllers;

use App\Models\Lection;
use Illuminate\Http\Request;

class LectionController extends Controller
{
    public function show($subject_id)
    {
        $lection = Lection::where('subject_id', $subject_id)->first();

        if (!$lection) {
            return response()->json(['message' => 'Лекция не найдена'], 404);
        }

        return response()->json(['lection' => $lection]);
    }

    public function find($subject_id, $topic_id)
    {
        if (!is_numeric($subject_id) || !is_numeric($topic_id)) {
            return response()->json(['error' => 'Invalid subject or topic ID'], 400);
        }

        $lection = Lection::where('subject_id', $subject_id)
            ->where('topic_id', $topic_id)
            ->first();

        if (!$lection) {
            return response()->json(['message' => 'Лекция не найдена'], 404);
        }

        return response()->json([
            'markdown' => $lection->content,
            'title' => $lection->title ?? 'Lection',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'topic_id' => 'required|exists:topics,id',
            'content' => 'required|string',
        ]);

        // ✅ Учитываем subject + topic + user, чтобы не затереть другие лекции
        $existingLection = Lection::where('subject_id', $validated['subject_id'])
            ->where('topic_id', $validated['topic_id'])
            ->where('user_id', auth()->id() ?? 1)
            ->first();

        if ($existingLection) {
            return response()->json([
                'message' => 'Лекция для этой темы уже существует',
                'existing_lection' => $existingLection,
                'action_required' => 'delete_first'
            ], 409);
        }

        $lection = Lection::create([
            'subject_id' => $validated['subject_id'],
            'topic_id' => $validated['topic_id'],
            'user_id' => auth()->id() ?? 1,
            'content' => $validated['content'],
        ]);

        return response()->json([
            'message' => 'Лекция успешно создана',
            'data' => $lection
        ], 201);
    }

    public function destroy($subject_id, $topic_id)
    {
        $lection = Lection::where('subject_id', $subject_id)
            ->where('topic_id', $topic_id)
            ->where('user_id', auth()->id() ?? 1)
            ->first();

        if (!$lection) {
            return response()->json(['message' => 'Лекция не найдена'], 404);
        }

        $lection->delete();

        return response()->json([
            'message' => 'Лекция успешно удалена',
            'subject_id' => $subject_id,
            'topic_id' => $topic_id
        ]);
    }
}
