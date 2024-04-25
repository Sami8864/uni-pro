<?php

namespace App\Http\Controllers\api;


use App\Mail\IniviteMail;
use App\Models\Invitation;
use App\Models\UserInvite;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use App\Http\Controllers\Controller;
use App\Models\ProfileProgress;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class InvitationController extends Controller
{
    private $responseService;

    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }
    public function linkGenerate(Request $request)
    {
        try {
            $device_id = $request->device_id;
            $uniquecode =  ProfileProgress::where('id', $device_id)->value('device_id');
            if (isset($uniquecode)) {

                $url = config('app.url')  . '?referral_link=' .  $uniquecode;

                return response()->json(['Link' => $url, 'code' => 200], 200);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage(), 'code' => 400], 400);
        }
    }
    public function create(Request $request)
    {
        $device_id = $request->device_id;
        $uniquecode = ProfileProgress::where('id', $device_id)->value('device_id');

        // Check if the user is authenticated
        if (isset($uniquecode)) {
            $recipients = $request->recipients; // Replace with actual recipients
            $url = config('app.url') . '?referral_link=' . $uniquecode;

            // Array to store recipients with deep links or error messages
            $results = [];

            // Loop through each recipient
            foreach ($recipients as $recipient) {
                // Check if the invitation already exists for this recipient and device_id
                $existingInvitation = Invitation::where('recipient', $recipient)
                ->where('inviter_id', $device_id)
                ->first();

                if ($existingInvitation) {
                    // If invitation already exists, add an error message to the results
                    $errorMessage = "You have already sent an invitation to $recipient.";
                    $results[] = ['recipient' => $recipient, 'error' => $errorMessage];
                } else {
                    // Create a new invitation for the recipient
                    $invitation = Invitation::create([
                        'inviter_id' => $device_id,
                        'recipient' => $recipient,
                    ]);

                    // Send email with invitation link to recipient
                    Mail::to($recipient)->send(new IniviteMail($url, false));

                    // Store recipient and deep link for successful invitations
                    $results[] = ['recipient' => $recipient, 'deep_link' => $url];
                }
            }

            // Check if any error messages were added to the results
            $errorMessages = array_column($results, 'error');
            $hasErrors = count(array_filter($errorMessages)) > 0;

            // Determine response status code and message
            $statusCode = $hasErrors ? 400 : 200;
            $message = $hasErrors ? 'Some invitations were not sent successfully' : 'Links Sent Successfully';

            // Respond with results and status code
            return response()->json(['results' => $results, 'message' => $message], $statusCode);
        }

        // User not authenticated
        return response()->json(['message' => 'User not Exist', 'code' => 401], 401);
    }





    public function invitedActor(Request $request)
    {
        try {
            $device_id = $request->device_id;
            $inviterId =  ProfileProgress::where('id', $device_id)->value('id');
            if (isset($inviterId)) {
                $data = Invitation::where('inviter_id', $inviterId)->get();
                if ($data->isNotEmpty()) {
                    return $this->responseService->jsonResponse(200, 'Invited Used Fetched  successfully', $data);
                } else {
                    return response()->json(['code' => 400, 'message' => 'No records found'], 400);
                }
            }
            return response()->json(['message' => 'User not authenticated']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    public function handleInvite($inviteCode)
    {
        $profileProgress = ProfileProgress::where('device_id', $inviteCode)->first();

        if ($profileProgress) {
            $user = User::find($profileProgress->profileprogess_id); // Assuming user_id is the foreign key linking ProfileProgress to User

            if ($user) {
                if ($user->hasAppInstalled()) {
                    // Redirect to open the app
                    return redirect()->away('casttype://open');
                } else {
                    // Redirect to app store
                    return redirect()->away('https://apps.apple.com/us/app/behance-creative-portfolios/id489667151');
                }
            } else {

                return redirect()->away('https://apps.apple.com/us/app/behance-creative-portfolios/id489667151');
            }
        } else {
            // Handle invalid invite code or missing ProfileProgress
            return response()->json(['message' => 'User not ProfileProgress']);
        }
    }
}
