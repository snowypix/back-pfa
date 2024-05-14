<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivitiesController extends Controller
{
    public function listActivities()
    {
        $user = auth()->user();
        if (!($user instanceof User)) {
            abort(404);
        }

        switch ($user->role) {
            case 'prof':
                $query = $user->activities();
                break;
            case 'student':
                return DB::table('activities')
                    ->leftJoin('submissions', function ($join) {
                        $user = auth()->user();
                        $join->on('activities.id', '=', 'submissions.activity_id')
                            ->where('submissions.student_id', '=', $user->id);
                    })
                    ->select('activities.*', 'submissions.status', 'submissions.lecture')
                    ->where('group', '=', $user->group)
                    ->where('class', '=', $user->class)
                    ->get();
                break;
            default:
                abort(404);
        }

        $activities = $query->get();
        return response()->json($activities);
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
            'dateRemise' => 'nullable|string', // Allow dateRemise to be nullable
        ]);

        // Initialize an array to hold file paths
        $filePaths = [];

        // Check if files were uploaded
        if ($request->hasFile('filePaths')) {
            foreach ($request->file('filePaths') as $file) {
                // Ensure the file is valid
                if ($file->isValid()) {
                    // Get the original file name
                    $fileName = $file->getClientOriginalName();
                    // Get the user's name
                    $userName = auth()->user()->name;

                    // Create a directory for the user if it doesn't exist
                    $userDirectory = public_path('uploads') . $userName;
                    if (!file_exists($userDirectory)) {
                        mkdir($userDirectory, 0777, true);
                    }

                    // Move the file to the user's directory
                    $file->move($userDirectory, $fileName);

                    // Store the file path in an array
                    $filePaths[] = "uploads" . $userName . "\/" . $fileName;
                }
            }
        }

        // Attach user ID to the validated data
        $validatedData['user_id'] = auth()->user()->id;

        // Include file paths in the data to be saved
        $validatedData['filePaths'] = json_encode($filePaths);

        // Create a new activity instance with the validated data
        $activity = Activity::create($validatedData);

        // Return a response indicating the successful creation of the activity
        return response()->json([
            'message' => 'Activity created successfully',
            'activity' => $activity,
        ], 201);
    }

    public function StatusCheck(int $id)
    {
        // $user = User::where('id', 3)->first();
        // Submission::where($id);
    }
    public function getActivity(int $id)
    {
        $activity = Activity::find($id);
        if (!$activity) {
            abort(404); // Use abort helper to return 404 status code
        }

        return $activity;
    }
    public function submitWorkFiles(Request $request, int $id)
    {

        // $user = auth()->user();
        $user = User::where('id', 3)->first();
        // Initialize an array to hold file paths
        $filePaths = [];
        // Check if files were uploaded
        if ($request->hasFile('filePaths')) {
            // abort(401);
            foreach ($request->file('filePaths') as $file) {
                // Ensure the file is valid
                if ($file->isValid()) {

                    // Get the original file name
                    $fileName = $file->getClientOriginalName();
                    // Get the user's name
                    $userName = $user->name;

                    // Create a directory for the user if it doesn't exist
                    $userDirectory = public_path('uploads') . $userName . date("H-i-s");
                    // dd($userDirectory);
                    if (!file_exists($userDirectory)) {
                        mkdir($userDirectory, 0777, true);
                    }

                    // Move the file to the user's directory
                    $file->move($userDirectory, $fileName);
                    // Store the file path in an array
                    $filePaths[] = "uploads" . $userName . "\/" . $fileName . date("H:i:s");
                } else {
                    return 'invalid';
                }
            }
        } else {
            return 'no file given';
        }
        $user->submissions()->attach($id, [
            'status' => 'submitted',
            'filePaths' => json_encode($filePaths)
        ]);
        // Include file paths in the data to be saved
        // $validatedData['filePaths'] = json_encode($filePaths);
        // Return a response indicating the successful creation of the activity
        return response()->json([
            'message' => 'Submitted work successfully'
        ], 201);
    }
}
