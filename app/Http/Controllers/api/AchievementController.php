<?php

namespace App\Http\Controllers\api;

use App\Models\UserPoint;

use App\Traits\FileUpload;
use App\Models\Achievement;
use App\Models\UserSpending;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ProfileProgress;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreAchievementRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\UpdateAchievementRequest;
use App\Models\PointType;

class AchievementController extends Controller
{
    public function trackPointsAndAwardAchievements()
    {
        try {
            $user = Auth::user();
            $profileProgress = ProfileProgress::where('id', $user->profileprogess_id)->first();

            if (!$profileProgress) {
                return response()->json(['error' => 'Profile Progress not found'], Response::HTTP_NOT_FOUND);
            }

            // Track invites_points logic
            // Assuming you have some logic to track invites_points here...

            // Get achievements that meet the criteria and haven't been achieved yet for types_points
            $achievementsToAttachTypes = Achievement::where('type', 'types')
                ->where('points_required', '<=', $profileProgress->types_points)
                ->whereDoesntHave('profileProgress', function ($query) use ($profileProgress) {
                    $query->where('profile_progress_id', $profileProgress->id);
                })
                ->get();
            // Get achievements that meet the criteria and haven't been achieved yet for invites_points
            $achievementsToAttachInvites = Achievement::where('type', 'invites')
                ->where('points_required', '<=', $profileProgress->invites_points)
                ->whereDoesntHave('profileProgress', function ($query) use ($profileProgress) {
                    $query->where('profile_progress_id', $profileProgress->id);
                })
                ->get();
            // Function to attach achievements
            function attachAchievements($achievements, $profileProgress)
            {
                foreach ($achievements as $achievement) {
                    $achievementPoints = $achievement->points_required;
                    $totalPointsRequired = $achievementPoints;

                    // Check if totalPointsRequired is greater than zero to avoid division by zero
                    $percentageAchieved = $totalPointsRequired > 0 ? ($profileProgress->types_points / $totalPointsRequired) * 100 : 0;

                    // Check if the achievement is already attached
                    if (!$profileProgress->achievementsWithPercentage->contains($achievement)) {
                        // Attach each eligible achievement with its own percentage achieved
                        $pivotData = ['percentage_achieved' => $percentageAchieved];
                        $profileProgress->achievementsWithPercentage()->attach($achievement->id, $pivotData);
                    }
                }
            }

            // Attach achievements for types_points
            attachAchievements($achievementsToAttachTypes, $profileProgress);

            // Attach achievements for invites_points
            attachAchievements($achievementsToAttachInvites, $profileProgress);

            return response()->json(['success' => 'Points tracked and achievements awarded successfully'], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
    public function getProfileProgressAchievements(ProfileProgress $profileProgress)
    {
        $user = Auth::user();

        $profileProgress = ProfileProgress::where('id', $user->profileprogess_id)->first();

        if (!$profileProgress) {
            return response()->json(['error' => 'Profile Progress not found'], Response::HTTP_NOT_FOUND);
        }

        $achievements = $profileProgress->achievementsWithPercentage->map(function ($achievement) {
            return [
                'id' => $achievement->id,
                'name' => $achievement->name,
                'description' => $achievement->description,
                'points_required' => $achievement->points_required,
                'type' => $achievement->type,
                'award_image' => $achievement->award_image,
                'percentage_achieved' => $achievement->pivot->percentage_achieved,
            ];
        });

        return response()->json(['data' => $achievements], Response::HTTP_OK);
    }
    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'name' => 'required|string',
                'description' => 'required|string',
                'points_required' => 'required|integer',
                'type' => ['required', Rule::in(['types', 'invites'])],
                'award_image' => 'required',
            ]);
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json(['error' => $e->errors()], Response::HTTP_BAD_REQUEST);
        }

        $filename = FileUpload::imageUpload($request->file('award_image'), 'public/achievements/');

        $achievement = Achievement::create([
            'name' => $request->name,
            'description' =>  $request->description,
            'points_required' =>  $request->points_required,
            'type' =>  $request->type,
            'award_image' =>  $filename
        ]);
        return response()->json($achievement, Response::HTTP_CREATED);
    }

    public function shareAchievement(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'achievement_id' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json(['error' => $e->errors()], Response::HTTP_BAD_REQUEST);
        }
        $device = ProfileProgress::where('id', Auth::user()->profileprogess_id)->first();



        $pointsDel = PointType::where('type', 'Achievement')->value('points');
        $device->types_points += $pointsDel;
        $device->save();
        UserSpending::create([
            'user' => auth()->user()->id,
            'amount' => 250,
            'spending_type' => 'Shared Achievemnt',
            'transaction_type' => 'Earned'
        ]);
        return response()->json([
            'message' => 'You achieved +250 points',
            'code' => 200,
            'points_added' => 250,
            'battery_level' => $device->types_points,
            'achievement_on' => Achievement::where('id', $validatedData['achievement_id'])->value('points_required'),
            'battery_limit' => 1000
        ], 200);
    }

    public function Achievement(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'achievement_id' => 'required|exists:achievements,id',
            ]);
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json(['error' => $e->errors()], Response::HTTP_BAD_REQUEST);
        }
        return response()->json([
            'message' => 'Achievement fetched',
            'code' => 200,
            'achievement' => Achievement::where('id', $validatedData['achievement_id'])->first()
        ], 200);
    }


    public function index()
    {

        $this->trackPointsAndAwardAchievements();
        $user = Auth::user();

        $profileProgress = ProfileProgress::where('id', $user->profileprogess_id)->first();

        if (!$profileProgress) {
            return response()->json(['error' => 'Profile Progress not found'], Response::HTTP_NOT_FOUND);
        }
        $achievements = Achievement::all();
        $unlocked = $profileProgress->achievementsWithPercentage->map(function ($achievement) {
            return [
                'id' => $achievement->id,
                'name' => $achievement->name,
                'description' => $achievement->description,
                'points_required' => $achievement->points_required,
                'type' => $achievement->type,
                'award_image' => $achievement->award_image,
                'percentage_achieved' => $achievement->pivot->percentage_achieved,
            ];
        });
        $locked = $achievements->diff($profileProgress->achievementsWithPercentage)->map(function ($achievement) {
            return [
                'id' => $achievement->id,
                'name' => $achievement->name,
                'description' => $achievement->description,
                'points_required' => $achievement->points_required,
                'type' => $achievement->type,
                'award_image' => $achievement->award_image,
                'percentage_achieved' => null, // Locked achievements won't have a percentage achieved
            ];
        });
        $data = [];
        $data['invites_points'] = (int)$profileProgress->invites_points;
        $data['types_points'] = (int) $profileProgress->types_points;
        $data['allAchievment'] = $achievements;
        $data['unlocked'] = $unlocked;
        $data['locked'] = $locked;
        // Retrieve and return all achievements


        return response()->json(['code' => 200, 'data' => $data], 200);
    }
    public function show($id)
    {
        // Retrieve and return a specific achievement by ID
        $achievement = Achievement::findOrFail($id);
        return response()->json($achievement);
    }
    public function update(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'points_required' => 'required|integer',
            'type' => ['required', Rule::in(['types', 'invites'])], // Use an array for multiple rules
        ]);

        // Update and return the achievement
        $achievement = Achievement::findOrFail($request->id);
        $achievement->update($validatedData);
        return response()->json(['data'=>$achievement]);
    }

    public function load()
    {
        return response()->json(['data' => Achievement::all()], 200);
    }

    public function destroy(Request $request)
    {
        // Delete the achievement
        Achievement::findOrFail($request->id)->delete();
        return response()->json(['message' => 'Achievement deleted successfully']);
    }
}
