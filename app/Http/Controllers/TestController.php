<?php

namespace App\Http\Controllers;

use App\Models\Command;
use App\Models\Test;
use App\Models\Question;
use App\Models\Answer;
use App\Models\TestResult;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function getTestByTopic($topic)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('Authenticated user', ['user_id' => Auth::id(), 'user' => Auth::user()]);

        $test = Test::where('topic_id', $topic)
            ->with(['questions' => function ($query) {
                $query->with(['answers:id,question_id,title,is_correct'])->orderBy('id');
            }])
            ->first();

        if (!$test) {
            return response()->json(['error' => 'Test not found for this topic'], 404);
        }

        $previousResult = TestResult::where('test_id', $test->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->first();

        $formattedPreviousResult = null;
        if ($previousResult) {
            $userAnswers = json_decode($previousResult->answers, true) ?? [];
            Log::debug('User Answers from test_results', ['answers' => $userAnswers]);

            $results = [];
            foreach ($test->questions as $index => $question) {
                $correctAnswer = $question->answers->where('is_correct', true)->first();
                $userAnswerId = isset($userAnswers[$index]) ? (int)$userAnswers[$index] : null;
                $userAnswer = $userAnswerId ? $question->answers->where('id', $userAnswerId)->first() : null;

                $isCorrect = $userAnswer && $userAnswer->id === $correctAnswer->id;

                $results[] = [
                    'question_id' => $question->id,
                    'question' => $question->title,
                    'correct_answer' => $correctAnswer ? $correctAnswer->title : 'N/A',
                    'user_answer' => $userAnswer ? $userAnswer->title : 'Нет ответа',
                    'is_correct' => $isCorrect,
                ];
            }

            $formattedPreviousResult = [
                'score' => $previousResult->score,
                'correct' => $previousResult->correct,
                'total' => $previousResult->total,
                'details' => $results,
            ];
        }

        return response()->json([
            'test' => $test,
            'has_previous_attempt' => !is_null($previousResult),
            'previous_result' => $formattedPreviousResult,
        ]);
    }

    public function submitTest(Request $request, Test $test)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('Authenticated user', ['user_id' => Auth::id(), 'user' => Auth::user()]);

        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'exists:answers,id',
        ]);

        $questions = $test->questions()
            ->with(['answers' => function ($query) {
                $query->where('is_correct', true);
            }])
            ->orderBy('id')
            ->get();

        if ($questions->isEmpty()) {
            return response()->json(['error' => 'Test has no questions'], 400);
        }

        $userAnswers = $request->input('answers');
        $correctCount = 0;
        $results = [];

        foreach ($questions as $index => $question) {
            $correctAnswer = $question->answers->where('is_correct', true)->first();
            $userAnswerId = isset($userAnswers[$index]) ? (int)$userAnswers[$index] : null;
            $userAnswer = $userAnswerId ? $question->answers()->where('id', $userAnswerId)->first() : null;

            $isCorrect = $userAnswer && $userAnswer->id === $correctAnswer->id;

            $results[] = [
                'question_id' => $question->id,
                'question' => $question->title,
                'correct_answer' => $correctAnswer ? $correctAnswer->title : 'N/A',
                'user_answer' => $userAnswer ? $userAnswer->title : 'Нет ответа',
                'is_correct' => $isCorrect,
            ];

            if ($isCorrect) {
                $correctCount++;
            }
        }

        $totalQuestions = $questions->count();
        $score = $totalQuestions > 0 ? ceil(($correctCount / $totalQuestions) * 100) : 0;

        TestResult::create([
            'test_id' => $test->id,
            'user_id' => Auth::id(),
            'score' => $score,
            'correct' => $correctCount,
            'total' => $totalQuestions,
            'answers' => json_encode($userAnswers),
        ]);

        $topic = $test->topic;

        if ($topic) {
            $subjectId = $topic->subject_id;

            \Log::debug('Looking for command by subject_id', ['subject_id' => $subjectId]);

            $userId = $request->user()->id;

            $commands = Command::where('subject_id', $subjectId)->get();

            $command = $commands->first(function ($cmd) use ($userId) {
                $ids = json_decode($cmd->member_ids, true);
                return in_array($userId, $ids ?? []);
            });

            // return response()->json(['command' => $command]); Test log for checking decoding "json"

            if ($command) {
                $command->balls = ($command->balls ?? 0) + $score;
                $command->save();

                \Log::debug('Balls updated', [
                    'command_id' => $command->id,
                    'new_balls' => $command->balls,
                ]);
            } else {
                \Log::debug('No command found for subject_id', ['subject_id' => $subjectId]);
            }
        }

        return response()->json([
            'score' => $score,
            'correct' => $correctCount,
            'total' => $totalQuestions,
            'details' => $results,
        ]);
    }

    public function createTest(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('Authenticated user', ['user_id' => Auth::id(), 'user' => Auth::user()]);

        \Log::info('Request data:', $request->all());

        $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'title' => 'required|string',
            'questions' => 'required|array|min:1',
            'questions.*.title' => 'required|string',
            'questions.*.answers' => 'required|array|min:1',
            'questions.*.answers.*.title' => 'required|string',
            'questions.*.answers.*.is_correct' => 'required|boolean',
        ]);

        $existingTest = Test::where('topic_id', $request->topic_id)->first();
        if ($existingTest) {
            return response()->json(['error' => 'Test already exists for this topic'], 400);
        }

        $test = Test::create([
            'topic_id' => $request->topic_id,
            'title' => $request->title,
            'user_id' => Auth::id(),
        ]);

        foreach ($request->questions as $q) {
            $question = Question::create([
                'test_id' => $test->id,
                'title' => $q['title'],
            ]);

            foreach ($q['answers'] as $answer) {
                Answer::create([
                    'question_id' => $question->id,
                    'title' => $answer['title'],
                    'is_correct' => $answer['is_correct'],
                ]);
            }
        }

        $test->load('questions.answers');
        return response()->json(['test' => $test], 201);
    }

    public function deleteTest($test_id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Log::info('Authenticated user', ['user_id' => Auth::id(), 'user' => Auth::user()]);

        $test = Test::find($test_id);
        if (!$test) {
            return response()->json(['error' => 'Test not found'], 404);
        }

        foreach ($test->questions as $question) {
            $question->answers()->delete();
            $question->delete();
        }
        $test->results()->delete();
        $test->delete();

        return response()->json(['message' => 'Test deleted successfully']);
    }
}
