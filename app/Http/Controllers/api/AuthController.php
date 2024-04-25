<?php

namespace App\Http\Controllers\api;

use Throwable;
use App\Models\User;
use App\Models\FilmMaker;
use App\Traits\FileUpload;
use Illuminate\Support\Str;
use App\Traits\Notification;
use Illuminate\Http\Request;
use App\Models\ProfileProgress;
use App\Services\ResponseService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\VerificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller

{
     use Notification;
    private $responseService;

    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }
    private function generateUniqueBarcodeNumber()
    {
        // Generate a random and unique barcode number
        $barcodeNumber = Str::random(8);

        // Check if the barcode number already exists in the database
        while (User::where('barcode', $barcodeNumber)->exists()) {
            $barcodeNumber = Str::random(8);
        }

        return $barcodeNumber;
    }
    public function register(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:App\Models\User,email',
                'password' => 'required|confirmed|min:6',
                'name' => 'required',
            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'error' => $validator->errors()->first()
                ], 422);
            }
            if ($request->user_type == 1 || !isset($request->user_type)) {
                $uniqueString = uniqid();

                $referrer = ProfileProgress::where('device_id', $request->ref)->first();
                $generateReferCode = ProfileProgress::where('id', $request->device_id)->value('device_id');
                $url = config('app.url') .'invite/'  .  $generateReferCode;
                if (isset($referrer)) {
                    $user =  User::create([
                        'name'        => $request->name,
                        'barcode'    => $uniqueString,
                        'email'       => $request->email,
                        'referrer_id' => $referrer ? $referrer->id : null,
                        'profileprogess_id'=> $request->device_id,
                        'password'    => Hash::make($request->password),
                        'email_verified_at' => now(),
                        'user_type'=>'User'
                    ]);
                    $referrerProfileProgressId = $referrer->id;
                    // Find the ProfileProgress record based on profileprogess_id
                    $profileProgress = ProfileProgress::where('id', $referrerProfileProgressId)->first();
                    // Check if the record exists
                    if ($profileProgress) {
                        // Increment the invites_points by 10
                        $points = 20;
                        $profileProgress->invites_points += $points;
                    }
                    $profileProgress->save();
                    $user->assignRole('user');
                    event(new Registered($user));
                    $credentials = $request->only('email', 'password');
                    if (Auth::attempt($credentials)) {
                        if ($request->is('api/*')) {
                            $device_name = ($request->device_name) ? $request->device_name : config("app.name");
                            $token =Auth::user()->createToken($device_name)->plainTextToken;
                            $this->token( $token);
                            return $this->responseService->jsonResponse(200, 'User logged in successfully', [
                                'user' => Auth::user(),
                                'access_token' => $token,
                                'token_type' => 'Bearer',
                                'link' =>  $url,
                                // 'notification' => $notification,
                            ]);
                        } else {
                            $request->session()->regenerate();
                            if ($request->expectsJson()) {

                                //$device_name = ($request->device_name) ? $request->device_name : config("app.name");
                                //$accessToken = Auth::user()->createToken($device_name)->plainTextToken;
                                $data = Auth::get();

                                return response()->json($data);
                            }
                            return redirect()->intended('/');
                        }
                    }
                    return response()->json(["email" => $request->email, "password" => $request->password], 422);
                } else {
                    $user =  User::create([
                        'name'        => $request->name,
                        'barcode'    => $uniqueString,
                        'email'       => $request->email,
                        'referrer_id' => null,
                        'profileprogess_id'=> $request->device_id,
                        'password'    => Hash::make($request->password),
                        'email_verified_at' => now(),
                    ]);
                    $user->user_type='user';
                    $user->save();
                    $user->assignRole('user');
                    event(new Registered($user));
                    $credentials = $request->only('email', 'password');
                    if (Auth::attempt($credentials)) {
                        if ($request->is('api/*')) {
                            $device_name = ($request->device_name) ? $request->device_name : config("app.name");
                            $token =Auth::user()->createToken($device_name)->plainTextToken;
                            $this->token( $token);

                            return $this->responseService->jsonResponse(200, 'User logged in successfully', [
                                'user' => Auth::user(),
                                'access_token' => $token,
                                'token_type' => 'Bearer',
                                'link' =>  $url,
                                // 'notification' => $notification,
                            ]);
                        } else {
                            $request->session()->regenerate();
                            if ($request->expectsJson()) {

                                //$device_name = ($request->device_name) ? $request->device_name : config("app.name");
                                //$accessToken = Auth::user()->createToken($device_name)->plainTextToken;
                                $data = Auth::get();

                                return response()->json($data);
                            }
                            return redirect()->intended('/');
                        }
                    }
                    return response()->json(["email" => $request->email, "password" => $request->password], 422);
                }
            } else if(($request->user_type == 2) ){
                $uniqueString = uniqid();
                $user = new User();
                $user->name = $request->name;
                $user->password = Hash::make($request->password);
                $user->email = $request->email;
                $user->email_verified_at = now();
                $user->barcode =  $uniqueString;
                $user->user_type='Filmmaker';
                $user->save();
                $user->assignRole('filmmaker');
                event(new Registered($user));
                $credentials = $request->only('email', 'password');
                if (Auth::attempt($credentials)) {
                    if ($request->is('api/*')) {

                        $device_name = ($request->device_name) ? $request->device_name : config("app.name");
                        $token =Auth::user()->createToken($device_name)->plainTextToken;
                        $this->token( $token);
                        return response()->json([
                            'code' => 200,
                            'message' => 'User logged in successfully',
                            'data' => [
                                'user' => Auth::user(),
                                'access_token' => $token,
                                'token_type' => 'Bearer',
                                // 'notification' =>  $notification,
                            ],
                        ]);
                    } else {
                        $request->session()->regenerate();
                        if ($request->expectsJson()) {

                            //$device_name = ($request->device_name) ? $request->device_name : config("app.name");
                            //$accessToken = Auth::user()->createToken($device_name)->plainTextToken;
                            $data = Auth::get();

                            return response()->json($data);
                        }
                        return redirect()->intended('/');
                    }
                }
                return response()->json(["email" => $request->email, "password" => $request->password], 422);
            }
            elseif(($request->user_type == 3)){

                $uniqueString = uniqid();
                $user = new User();
                $user->name = $request->name;
                $user->password = Hash::make($request->password);
                $user->email = $request->email;
                $user->email_verified_at = now();
                $user->user_type='Admin';
                $user->save();
                $user->assignRole('admin');
                event(new Registered($user));
                $credentials = $request->only('email', 'password');
                if (Auth::attempt($credentials)) {
                    if ($request->is('api/*')) {

                        $device_name = ($request->device_name) ? $request->device_name : config("app.name");
                        $token =Auth::user()->createToken($device_name)->plainTextToken;
                        $this->token( $token);
                        return response()->json([
                            'code' => 200,
                            'message' => 'User logged in successfully',
                            'data' => [
                                'user' => Auth::user(),
                                'access_token' => $token,
                                'token_type' => 'Bearer',
                                // 'notification' =>  $notification,
                            ],
                        ]);}}
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    public function Profile(Request $request)
    {
        try {

            $user = Auth::user();
            if ($user->hasRole('filmmaker')) {

                $validator = Validator::make($request->all(), [
                    'compnay_name' => 'required',
                    'full_name' => 'required',
                    'bio' => 'required',
                    'imdb_link' => 'url|nullable',
                    'actoraccess_link' => 'url|nullable',
                    'casting_link' => 'url|nullable'
                ]);
                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json(['code' => '422', 'error' => $validator->errors()->first()], 422);
                }

                $filmmaker = FilmMaker::updateOrCreate(
                    ['user_id' => $user->id], // Search condition
                    [   // Data to update or create
                        'compnay_name' => $request->compnay_name,
                        'full_name' => $request->full_name,
                        'bio' => $request->bio,
                        'union_id' => $request->union_id[0],
                        'imdb_link' => $request->imdb_link,
                        'actoraccess_link' => $request->actoraccess_link,
                        'casting_link' => $request->casting_link
                    ]
                );
                User::where('id', $user->id)->update([
                    'name' => $request->full_name,
                ]);
                return response()->json(['code' => 200, 'data' => $filmmaker, 'message' => 'profile has been updated successfully']);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user->hasRole('filmmaker')) {
                $validator = Validator::make($request->all(), [
                    'profile_image' => 'required',
                    'compnay_name' => 'required',
                    'full_name' => 'required',
                    'bio' => 'required',
                ]);
                // Check if validation fails
                if ($validator->fails()) {
                    return response()->json(['code' => '422', 'errors' => $validator->errors()], 422);
                }

                $filmmaker = FilmMaker::find($request->id);

                if ($filmmaker) {
                    // Only update the profile image if a new one is provided
                    if ($request->has('profile_image')) {
                        $filename = FileUpload::file($request->profile_image, 'Filmmaker/profile/');
                        $filmmaker->profile_image = $filename;
                    }

                    // Update other fields
                    $filmmaker->compnay_name = $request->compnay_name;
                    $filmmaker->full_name = $request->full_name;
                    $filmmaker->bio = $request->bio;
                    $filmmaker->union_id = $request->union_id;
                    $filmmaker->imdb_link = $request->imdb_link;
                    $filmmaker->actoraccess_link = $request->actoraccess_link;
                    $filmmaker->casting_link = $request->casting_link;

                    // Save the changes
                    $filmmaker->save();

                    return response()->json(['code' => 200, 'data' => $filmmaker, 'message' => 'Profile has been updated successfully']);
                } else {
                    // Handle the case where a FilmMaker with the given id does not exist
                    return response()->json(['message' => 'FilmMaker not found for the given id'], 404);
                }
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    public function changePassword(Request $request)
    {

        try {

            $validator = Validator::make($request['data'], [
                'current_password' => 'required',
                'new_password' => 'required|min:8',
            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'error' => $validator->errors()->first()
                ], 422);
            }
            $user = Auth::user();

            if (!Hash::check($request['data']['current_password'], $user->password)) {
                throw ValidationException::withMessages(['code' => 401, 'current_password' => 'Incorrect current password'], 401);
            }

            $user->password = Hash::make($request['data']['new_password']);
            $user->save();

            return response()->json(['code' => 200, 'message' => 'Password changed successfully'], 200);
        } catch (\Throwable $th) {
            return response()->json(['code' => 401, 'error' => $th->getMessage()],422);
        }
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validator->errors()->first()
            ], 422);
        }
        $remember_me = ($request->remember_me) ? true : false;
        $credentials = $request->only('email', 'password');
        // dd( Auth::attempt($credentials));
        if (Auth::attempt($credentials, $remember_me)) {
            if ($request->is('api/*')) {
                $device_name = ($request->device_name) ? $request->device_name : config("app.name");
                return response()->json([
                    'code' => 200,
                    'message' => 'User logged in successfully',
                    'data' => [
                        'user' => Auth::user(),
                        'access_token' => Auth::user()->createToken($device_name)->plainTextToken,
                        'token_type' => 'Bearer',
                        // 'notification' =>  $notification,
                    ],
                ]);
            }
        } else {
            return response()->json(['code' => 401, 'error' => 'The credentials are incorrect'], 401);
        }
    }
    public function deleteAccount(Request $request)
    {
        try {
            $user = Auth::user();
            $user = ProfileProgress::find($user->profileprogess_id);
            if ($user) {
                $data =  $user->delete();
                return $this->responseService->jsonResponse(200, 'User Deleted successfully', ['is_deleted'=>$data]);
            } else {
                return $this->responseService->jsonResponse(401, 'User not  found', []);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    public function restoreAccount(Request $request)
    {
        try {
            $user = ProfileProgress::withTrashed()->find($request->device_id);

            if ($user) {
                $data =  $user->restore();
                return $this->responseService->jsonResponse(200, 'User Recovered successfully', [$data]);
            } else {
                return $this->responseService->jsonResponse(401, 'User not  found', []);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    public function permanentDeleteAccount(Request $request)
    {
        try {
            $user = ProfileProgress::withTrashed()->find($request->device_id);
            if ($user) {
                $data =  $user->forceDelete();;
                return $this->responseService->jsonResponse(200, 'User is Deleted  successfully', [$data]);
            } else {
                return $this->responseService->jsonResponse(401, 'User not  found', []);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    public function DeleteUserAccount(Request $request)
    {
        try {
            $user = ProfileProgress::withTrashed()->find($request->device_id);
            User::where('profileprogess_id',$request->device_id)->forceDelete();
            if ($user) {
                $data =  $user->forceDelete();
                return $this->responseService->jsonResponse(200, 'User is Deleted  successfully', [$data]);
            } else {
                return $this->responseService->jsonResponse(401, 'User not  found', []);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    public function unions()
    {
        try {
            $data = DB::table('unions')->get();
            $array  = [];
            foreach ($data as $dat) {
                // Append a new associative array to $array for each item in $data
                $array[] = [
                    'label' => $dat->name,
                    'value' => $dat->id
                ];
            }

            return response()->json(['data'=>$array , 'message'=> 'data fetched successfully', 'code'=> 200 ]);
        } catch (Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    public function updaProfileImage(Request $request)
    {
        try {
            $user = Auth::user();
             // dd( $request->all() );
             $validator = Validator::make($request->all(), [
                'profileImage' => 'required',
            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['code' => '422', 'errors' => $validator->errors()->first()], 422);
            }
            $filename = (time()+ random_int(100, 1000));
            $extension = $request->file('profileImage')->getClientOriginalExtension();
            $filename = $filename . '.' . $extension;
            $filePath = '/profile/images' . $filename;
            $path = Storage::disk('spaces')->put($filePath, file_get_contents($request->file('profileImage')));
            $path = Storage::disk('spaces')->url($filePath);
            $data = FilmMaker::updateOrCreate(
                ['user_id' => $user->id], // Search condition
                ['profile_image' => $path] // Data to update or create
            );
            return response()->json(['data'=>$data , 'message'=> 'Profile Has been updated Successfully', 'code'=> 200 ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    public function updateCoverImage(Request $request)
    {
        try {
            $user = Auth::user();
             // dd( $request->all() );
             $validator = Validator::make($request->all(), [
                'coverImage' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['code' => '422', 'errors' => $validator->errors()->first()], 422);
            }
            $filename = (time()+ random_int(100, 1000));
            $extension = $request->file('coverImage')->getClientOriginalExtension();
            $filename = $filename . '.' . $extension;
            $filePath = '/cover/images' . $filename;
            $path = Storage::disk('spaces')->put($filePath, file_get_contents($request->file('coverImage')));
            $path = Storage::disk('spaces')->url($filePath);
            $data = FilmMaker::updateOrCreate(
                ['user_id' => $user->id], // Search condition
                ['cover_image' => $path] // Data to update or create
            );
            return response()->json(['data'=>$data , 'message'=> 'Profile Has been updated Successfully', 'code'=> 200 ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    public  function deleteCoverImage()
    {
        try {
            $user = Auth::user();
            $data = FilmMaker::where('user_id',$user->id)->update([
                'cover_image' => null
            ]);
            return response()->json(['data'=>$data , 'message'=> 'Profile Has been updated Successfully', 'code'=> 200 ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    public  function deleteProfileImage()
    {
        try {
            $user = Auth::user();
            $data = FilmMaker::where('user_id',$user->id)->update([
                'profile_image' => null
            ]);
            return response()->json(['data'=>$data , 'message'=> 'Profile Has been updated Successfully', 'code'=> 200 ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'error' => $validator->errors()->first()
                ], 422);
            }
            $data = $request->all();
            // Determine the username field (email or phone)
            $usernameField = filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            if ($usernameField === 'email') {
                $user = User::findByEmail($data['email']);

                if (! $user) {
                    return response()->json(['message'=>'Please enter a valid email','code'=> 400],400);
                }

                VerificationService::sendEmailVerificationCode($user);
            }else {
                return response()->json(['message'=>'Please enter a valid email']);
            }
            return response()->json(['messae' =>"Verification code sent to your $usernameField." ]);

        } catch (Throwable $th) {
            return response()->json(['error'=>$th->getMessage()],400);

        }
    }
    public function verifyEmail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'code' => 'required|min:6|max:6'
            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'error' => $validator->errors()->first()
                ], 422);
            }
            return VerificationService::verifyEmail($request->email, $request->code);
        } catch (Throwable $th) {
            return response()->json(['error'=>$th->getMessage(), 'code'=> 400]);
        }
    }
    protected function updatePassword($user, $password)
    {
        $user->update([
            'password' => Hash::make($password)
        ]);
    }
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:8|confirmed',
                'email' => 'required|exists:users,email',
                'code' => 'required|exists:users,email_verification_code',
            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'error' => $validator->errors()->first()
                ], 422);
            }
            $usernameField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            if ($usernameField === 'email') {
                $user = User::findByEmail($request->email);
                if (!$user) {
                    return response()->json(['message'=>'No record found.','code'=> 400],400);
                }
                $verfied =   VerificationService::verifyEmail($request->email, $request->code);


                if ($verfied) {
                    $this->updatePassword($user, $request->password);
                } else {
                    return response()->json(['message'=>'Verification code is either incorrect or expired.','code'=> 400],400);
                }

            }

            return response()->json(['message'=>'Password updated successfully.','code'=> 200],200);

        } catch (Throwable $th) {
            return response()->json(['error'=>$th->getMessage(), 'code'=> 400]);
        }
    }
}
