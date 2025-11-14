<?php

namespace App\Http\Controllers;

use App\Models\Lection;
use Illuminate\Http\Request;

class LectionController extends Controller
{
    // Получить лекцию для subject + topic (общую)
    public function find($subject_id, $topic_id)
    {
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

    // Создать общую лекцию
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'topic_id' => 'required|exists:topics,id',
            'content' => 'required|string',
        ]);

        // Проверяем существующую общую лекцию
        $existing = Lection::where('subject_id', $validated['subject_id'])
            ->where('topic_id', $validated['topic_id'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Лекция уже существует'], 409);
        }

        $lection = Lection::create([
            'subject_id' => $validated['subject_id'],
            'topic_id' => $validated['topic_id'],
            'content' => $validated['content'],
        ]);

        return response()->json([
            'message' => 'Лекция успешно создана',
            'data' => $lection
        ], 201);
    }

    // Удалить общую лекцию
    public function destroy($subject_id, $topic_id)
    {
        $lection = Lection::where('subject_id', $subject_id)
            ->where('topic_id', $topic_id)
            ->first();

        if (!$lection) {
            return response()->json(['message' => 'Лекция не найдена'], 404);
        }

        $lection->delete();

        return response()->json([
            'message' => 'Лекция успешно удалена',
        ]);
    }
}
