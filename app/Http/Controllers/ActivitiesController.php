<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivitiesController extends Controller
{
    public function listProf()
    {
        // DB::connection()->enableQueryLog();
        if (auth()->user()->role == 'prof') {
            $user = auth()->user();
            if ($user instanceof User) {

                $query = $user->activities();
                $activities = $query->get();
                return response()->json($activities);
            }
        } else {
            abort(404);
        }
    }
    public function create(Request $request)
    {
        DB::connection()->enableQueryLog();
        // Validate the request data
        $validatedData = $request->validate([
            'intitule' => 'required|string',
            'matiere' => 'required|string',
            'class' => 'required|string',
            'group' => 'required|string',
            'type' => 'required|string',
            'filePaths' => 'string',
            'dateRemise' => 'string'
        ]);
        $validatedData['user_id'] = auth()->user()->id;
        // Create a new activity instance with the validated data
        $activity = Activity::create($validatedData);
        // Return a response indicating the successful creation of the activity
        return response()->json([
            'message' => 'Activity created successfully',
            'activity' => $activity,
        ], 201);
    }
}
