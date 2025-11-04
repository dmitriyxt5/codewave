<?php
namespace App\Http\Controllers;

use App\Models\Command;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommandController extends Controller
{
    public function getTeamImage(Request $request, $subject_id)
    {
        $command = Command::where('subject_id', $subject_id)->first();

        if (!$command) {
            return response()->json(['link' => '/images/animals/bars.jpg'], 200);
        }

        return response()->json(['link' => json_decode($command->items)], 200);
    }

    public function spendCoinsAndUpgrade(Request $request, $id)
    {
        $request->validate([
            'type' => 'required',
            'price' => 'required|integer|min:0'
        ]);

        $command = Command::findOrFail($id);

        if (Auth::user()->role !== 'admin' || 'superadmin' && Auth::user()->id !== $command->leader_id) {
            return response()->json(['error' => 'Только лидер команды может совершать покупки'], 403);
        }
        $items = json_decode($command->items) ?? []; 
        $items[] = $request->type; 
        $command->update(['items' => json_encode($items)]);


        if ($command->balls < $request->price) {
            return response()->json(['error' => 'Недостаточно баллов'], 400);
        }

        $command->balls -= $request->price;
        $command->link = "/images/animals/{$request->type}.jpg";
        $command->save();

        return response()->json($command);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'leader_id' => 'required|exists:users,id',
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:users,id'
        ]);

        // Command::where('subject_id', $request->subject_id)->delete(); We remove deletes for create multi commands

        $command = Command::create([
            'subject_id' => $request->subject_id,
            'leader_id' => $request->leader_id,
            'member_ids' => json_encode($request->member_ids),
            'link' => '/images/animals/bars.jpg',
            'items' => "[1]"
        ]);

        return response()->json($command, 201);
    }

    public function show(Request $request)
    {
        $subject_id = $request->route('subject_id');

        $commands = Command::where('subject_id', $subject_id)
            ->with('leader')
            ->get();

        foreach ($commands as $command) {
            $command->members = $command->members();
        }

        return response()->json($commands);
    }


    public function getStudents()
    {
        $students = User::where('role', 'user')->get(['id', 'username', 'firstname', 'lastname']);
        return response()->json($students);
    }

    public function addStudents(Request $request, $id)
    {
        $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:users,id'
        ]);

        $command = Command::findOrFail($id);
        $currentMembers = is_string($command->member_ids) ? json_decode($command->member_ids, true) : $command->member_ids;
        $newMembers = array_unique(array_merge($currentMembers ?? [], $request->member_ids));
        $command->update(['member_ids' => json_encode($newMembers)]);

        return response()->json($command);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'leader_id' => 'required|exists:users,id',
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:users,id'
        ]);

        $command = Command::findOrFail($id);
        $command->update([
            'leader_id' => $request->leader_id,
            'member_ids' => json_encode($request->member_ids),
        ]);

        return response()->json($command);
    }

    public function destroy($id)
    {
        $command = Command::findOrFail($id);
        $command->delete();

        return response()->json(['message' => 'Command deleted']);
    }

    public function upgradePhoto(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:standard_bars,golden_bars,silver_bars'
        ]);

        $command = Command::findOrFail($id);
        $command->update(['link' => "/images/animals/{$request->type}.jpg"]);

        return response()->json($command);
    }
}
