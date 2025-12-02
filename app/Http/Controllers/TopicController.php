<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $id)
    {
        try {
            $userId = $request->query('user_id');
    
            $topics = Topic::where('subject_id', $id)
                ->with(['test.results' => function ($query) use ($userId) {
                    if ($userId) {
                        $query->where('user_id', $userId)
                              ->select('id', 'test_id', 'user_id', 'score');
                    }
                }])
                ->orderBy('created_at', 'desc') // сортировка по дате
                ->get()
                ->map(function ($topic) {
                    $score = $topic->test && $topic->test->results->isNotEmpty()
                        ? $topic->test->results->first()->score
                        : null;
    
                    return [
                        'id' => $topic->id,
                        'subject_id' => $topic->subject_id,
                        'name' => $topic->name,
                        'date' => $topic->created_at->format('d.m.Y'),
                        'score' => $score,
                    ];
                });
    
            return response()->json($topics, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching topics.',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|numeric',
            'name' => 'required|string',
            'objective' => 'required|string',
            'tasks' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|in:lecture,practical',
            'date' => 'nullable|date',
        ]);
    
        $data = [
            'subject_id' => $request->subject_id,
            'name' => $request->name,
            'objective' => $request->objective,
            'tasks' => $request->tasks,
            'description' => $request->description,
            'type' => $request->type,
        ];
    
        if ($request->filled('date')) {
            $data['created_at'] = $request->date;
        }
    
        $topic = Topic::create($data);
    
        return response()->json($topic, 201);
    }
    

    /**
     * Display the specified resource.
     */
    public function show($subject_id, $topic_id)
    {
        $topic = Topic::where('subject_id', $subject_id)->where('id', $topic_id)->first();

        if (!$topic) {
            return response()->json(['message' => 'Тема не найдена'], 404);
        }

        return response()->json($topic, 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Topic $topic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $topic = Topic::find($id);

        if ($request->user()->role === 'admin' &&
            $topic->subject->user_id !== $request->user()->id
        ) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $item = Topic::find($id);
        $item->delete();

        return response()->json(['message' => 'Item deleted successfully']);
    }
}
