<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubjectController extends Controller
{

    public function index(Request $request)
    {
        $user_id = $request->user()->id;
        $role = $request->user()->role;
        $subjects = Subject::with('topics')->with('commands')->with('user')->get()->map(function ($subject) use ($user_id, $role) {
            if ($role == 'user') {
                
                $userCommands = $subject->commands->filter(function ($command) use ($user_id) {
                    $member_ids = json_decode($command->member_ids, true) ?? [];
                    return in_array($user_id, $member_ids);
                });

                if ($userCommands->isEmpty()) {
                    return null;
                }
            } else if ($role == 'admin') {
                // return $subject->user_id;

                if ($subject->user_id !== $user_id) {
                    return null;
                }
            }
            
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'description' => $subject->description,
                'image' => $subject->image,
                'lecture_count' => $subject->countLectureTopics(),
                'practice_count' => $subject->countPracticeTopics(),
                'user' => $subject->user,
                // 'members' => $subject->commands->members,
                // 'user_id' => $request->user(),
            ];
        })->filter();

        return response()->json($subjects, 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imagePath = $request->file('image')->store('images', 'public');

        $subject = Subject::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        return response()->json($subject, 201);
    }

    public function show(Subject $subject)
    {
        //
    }

    public function edit($id)
    {
        $subject = Subject::with('topics')->find($id);

        return response()->json($subject, 200);
    }

    public function update(Request $request, Subject $subject)
    {

        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($subject->image && Storage::disk('public')->exists($subject->image)) {
                Storage::disk('public')->delete($subject->image);
            }

            $validate['image'] = $request->file('image')->store('images', 'public');
        } else {
            unset($validate['image']);
        }

        $subject->update($validate);

        return response()->json($subject, 200);
    }

    public function destroy($id)
    {
        $subject = Subject::find($id);
        if ($subject->image && Storage::disk('public')->exists($subject->image)) {
            Storage::disk('public')->delete($subject->image);
        }

        $subject->delete();

        return response()->json(['message' => 'Subject deleted successfully'], 204);
    }
}