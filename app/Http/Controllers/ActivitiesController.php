<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Submission;
use App\Models\User;
use DateTime;
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
                    // $user = User::where('id', 3)->first();
                    // $userName = $user->name;
                    // Create a directory for the user if it doesn't exist
                    $userDirectory = public_path('uploads') . $userName . date("H-i-s");
                    if (!file_exists($userDirectory)) {
                        mkdir($userDirectory, 0777, true);
                    }

                    // Move the file to the user's directory
                    $file->move($userDirectory, $fileName);

                    // Store the file path in an array
                    $filePaths[] = "uploads" . $userName . date("H-i-s") . "/" . $fileName;
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
    // $user = User::where('id', 3)->first();
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
        $now = new DateTime();
        $formattedDateTime = $now->format('Y-m-d H:i:s');
        $activity = Activity::find($id);
        if ($activity->dateRemise < $formattedDateTime) {
            return response()->json([
                'message' => 'Submitting too late'
            ], 422);
        }
        // $user = User::find(auth()->user()->id);
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
                    $filePaths[] = "uploads" . $userName . date("H-i-s") . "/" . $fileName;
                } else {
                    return 'invalid';
                }
            }
        } else {
            return 'no file given';
        }
        DB::connection()->enableQueryLog();
        $res = $user->submissions()->where('activities.id', $id)->first();
        if ($res) {
            DB::table('submissions')
                ->where('student_id', $user->id)
                ->where('activity_id', $id)
                ->update(['status' => 'soumis', 'filePaths' => json_encode($filePaths)]);
        } else {
            $user->submissions()->attach($id, [
                'status' => 'soumis',
                'filePaths' => json_encode($filePaths)
            ]);
        }

        return response()->json([
            'message' => 'Submitted work successfully'
        ], 201);
    }
    public function submitStatus(int $id)
    {
        $user = auth()->user();
        $userM = User::find($user->id);
        // $user = User::where('id', 3)->first();
        $sub = $userM->submissions()->where('activities.id', $id)->withPivot('status', 'lecture')->first();
        if ($sub)
            return response()->json([
                'status' => $sub->pivot->status
            ], 201);
        return response()->json([
            'status' => 'not submitted'
        ], 201);
    }
    public function Seen($id)
    {
        $user = auth()->user();
        $userM = User::find(auth()->user()->id);
        $res = $userM->submissions()->where('activities.id', $id)->first();
        if ($res) {
            if ($res->pivot->lecture == 'lu') {
                DB::table('submissions')
                    ->where('student_id', $user->id)
                    ->where('activity_id', $id)
                    ->update(['lecture' => 'non lu']);
            } else {
                DB::table('submissions')
                    ->where('student_id', $user->id)
                    ->where('activity_id', $id)
                    ->update(['lecture' => 'lu']);
            }
            return response()->json([
                'message' => 'done'
            ], 201);
        }
        $userM->submissions()->attach($id, [
            'lecture' => 'lu'
        ]);
    }
    public function SeenOnce($id)
    {
        $user = auth()->user();
        $userM = User::find(auth()->user()->id);
        $res = $userM->submissions()->where('activities.id', $id)->first();
        if ($res) {
            DB::table('submissions')
                ->where('student_id', $user->id)
                ->where('activity_id', $id)
                ->update(['lecture' => 'lu']);
            return response()->json([
                'message' => 'done'
            ], 201);
        }
        $userM->submissions()->attach($id, [
            'lecture' => 'lu'
        ]);
    }
    public function SubmissionsList()
    {
        $user = auth()->user();
        $id = $user->id;
        // Get all submissions for the teacher's activities
        $submissions = DB::table('submissions')
            ->join('activities', 'submissions.activity_id', '=', 'activities.id')
            ->join('users', 'submissions.student_id', '=', 'users.id')
            ->select(
                'activities.id as activity_id',
                'activities.intitule as intitule',
                'activities.group as group',
                'activities.matiere as matiere',
                'activities.class as class',
                'submissions.filePaths',
                'users.name as student',
                'activities.user_id as prof'
            )
            ->where('submissions.status', '=', 'soumis')
            ->where('activities.user_id', '=', $user->id)
            ->get();
        return $submissions;
    }
}
