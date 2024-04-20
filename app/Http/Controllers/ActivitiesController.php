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

    public function listEtud()
    {
        // DB::connection()->enableQueryLog();
        if (auth()->user()->role == 'student') {
            $user = auth()->user();
            if ($user instanceof User) {
                $query = $user->activities()->where('class', $user->class)->where('group', $user->group);
                $activities = $query->get();
            return response()->json($activities);
            }
        } else {
            abort(404);
        }
    }

    public function create(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'intitule' => 'required|string',
            'matiere' => 'required|string',
            'class' => 'required|string',
            'group' => 'required|string',
            'type' => 'required|string',
            'description' => 'required|string',
            'filePaths' => 'string',
            'dateRemise' => 'string'
        ]);
        $validatedData['filePaths'] = json_encode($validatedData['filePaths']);
        $validatedData['user_id'] = auth()->user()->id;
        // Create a new activity instance with the validated data
        // return $validatedData;
        $activity = Activity::create($validatedData);
        // Return a response indicating the successful creation of the activity
        return response()->json([
            'message' => 'Activity created successfully',
            'activity' => $activity,
        ], 201);
    }
    
    public function createFile(Request $request)
    {
        // Check if a file was uploaded
        if ($request->hasFile('logo')) {
            // Get the uploaded file
            $file = $request->file('logo');

            // Get the file name
            $fileName = $file->getClientOriginalName();

            // Move the uploaded file to a directory
            $file->move(public_path('uploads'), $fileName);

            // Return a response
            return response()->json([
                'fileName' => $fileName
            ]);
        } else {
            // Return an error response
            return response()->json([
                'error' => 'No file was uploaded'
            ], 400);
        }
    }
}
