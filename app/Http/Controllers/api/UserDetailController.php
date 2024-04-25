<?php

namespace App\Http\Controllers\api;

use App\Models\FilmMaker;
use App\Models\Type;
use App\Models\User;
use App\Models\Essence;
use App\Models\Physique;
use App\Models\UserFlag;
use App\Models\UserLink;
use App\Models\UserType;
use App\Models\ActorReel;
use App\Models\Headshots;
use App\Models\SavedFeed;
use App\Models\UserDetail;
use App\Traits\FileUpload;
use App\Models\AiAttribute;
use App\Models\UserEssence;
use Illuminate\Support\Str;
use App\Models\UserSpending;
use Illuminate\Http\Request;
use App\Models\UserAttribute;
use App\Models\ProfileProgress;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use App\Models\PointType;



class UserDetailController extends Controller
{

    public function store(Request $request)
    {

        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'device_id' => 'required|exists:profile_progress,id',
            'name' => 'required|string',
            'weight_height' => 'required|string',
            'physique' => 'required|string',
            'location' => 'required|string',
            'essence' => 'required|array',
            'type' => 'required|array',
        ]);
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validator->errors()->first()
            ], 422);
        }
        $values = explode(',', $data['weight_height']);
        // Extract values for meters and pounds
        $metersValue = (float)str_replace('m', '', trim($values[0])); // Remove 'm' and convert to float
        $poundsValue = (int)str_replace('lbs', '', trim($values[1])); // Remove 'lbs' and convert to integer
        $headshot = Headshots::where('device_id', $data['device_id'])->where('type_id', 2)->first();

        if (!$headshot) {
            return response()->json([
                'code' => 404,
                'message' => 'Headshot not found',
            ]);
        }
        Log::error('Check headshot is find againt the device_id ',  [$headshot]);
        $age = UserAttribute::where('headshot', $headshot->id)->where('attribute_type', 1)->pluck('answer');
        $gender = UserAttribute::where('headshot', $headshot->id)->where('attribute_type', 2)->pluck('answer');
        $ethnicity = UserAttribute::where('headshot', $headshot->id)->where('attribute_type', 3)->pluck('answer');
        // If validation passes, create a new profile
        $profile = UserDetail::create([
            'device_id' => $data['device_id'],
            'name' => $data['name'],
            'physique' => $data['physique'],
            'location' => $data['location'],
            'IMDb' => $data['IMDb'],
            'age' =>  $age[0],
            'gender' => $gender[0],
            'ethnicity' => $ethnicity[0],
            'height' => $metersValue,
            'weight' => $poundsValue,
        ]);

        // Associate essences
        if ($request->has('essence')) {
            //dd($essences);
            $essences = $request->essence;
            //  dd($essences);
            foreach ($essences as $essence) {
                $essence1 = Essence::where('name', $essence)->first();
                //dd($essence);
                $essenceModel = UserEssence::create([
                    'essence_id' => $essence1->id,
                    'user_id' => $profile->id
                ]);

                $head_shoot_exist = Headshots::where('device_id', $data['device_id'])->where('type_id', 2)->pluck('id')->first();
                if (isset($head_shoot_exist)) {
                    UserAttribute::create([
                        'headshot' => Headshots::where('device_id', $data['device_id'])->where('type_id', 2)->pluck('id')->first(),
                        'attribute_type' => 5,
                        'attribute_name' =>  Essence::where('id', $essence1->id)->pluck('name')->first(),
                        'agree' => 15,
                        'disagree' => 0,
                        'answer' => Essence::where('id', $essenceModel->id)->pluck('name')->first(),
                        'profile' => $headshot->device_id
                    ]);
                } else {
                    return response()->json(['code' => 400, 'message' => 'Headshoot is missing']);
                }
            }
        }

        // Associate types
        if ($request->has('type')) {
            $types = $request->type;
            foreach ($types as $type) {
                $type1 = Type::where('name', $type)->first();
                $Model = UserType::create([
                    'type_id' =>  $type1->id,
                    'user_id' =>  $profile->id
                ]);
            }
        }
        return response()->json(['code' => 200, 'message' => 'Profile created successfully', 'profile' => $profile], 200);
    }
    public function load()
    {
        return UserDetail::All();
    }

    public function essence()
    {

        $essenceList = Essence::all();

        if ($essenceList->isNotEmpty()) {
            return response()->json(['code' => 200, 'message' => 'Essence fetched successfully', 'essence_list' => $essenceList], 200);
        } else {
            return response()->json(['code' => 404, 'message' => 'No essence records found']);
        }
    }

    public function physique()
    {
        $physiques = Physique::All();

        if ($physiques->isNotEmpty()) {
            return response()->json(['code' => 200, 'message' => 'physiques fetched successfully', 'physiques' => $physiques], 200);
        } else {
            return response()->json(['code' => 404, 'message' => 'No physiques records found']);
        }
    }

    public function type()
    {
        $typeyourself_list = Type::All();
        if ($typeyourself_list->isNotEmpty()) {
            return response()->json(['code' => 200, 'message' => 'Types fetched successfully', 'typeyourself_list' => $typeyourself_list], 200);
        } else {
            return response()->json(['code' => 404, 'message' => 'No Types records found'],);
        }
    }
    public function generateBarcodeId(Request $request)
    {
        $data = $request->all();

        $rules = [
            'user_id' => 'required'
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        // If validation fails, return with validation error messages
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $user = User::where('id', $data['user_id'])->first();
        $barcodeNumber = $this->generateUniqueBarcodeNumber();
        $barcodeId = $user->username . '.' . $barcodeNumber;

        return response()->json(['barcode_id' => $barcodeId]);
    }

    private function generateUniqueBarcodeNumber()
    {
        // Generate a random and unique barcode number
        $barcodeNumber = Str::random(8);

        // Check if the barcode number already exists in the database
        while (User::where('barcode_number', $barcodeNumber)->exists()) {
            $barcodeNumber = Str::random(8);
        }

        return $barcodeNumber;
    }

    public function AddFlag(Request $request)
    {

        $data = $request->All();
        $validator = validator::make($data, [
            'headshot_id' => 'required|exists:user_attributes,id',
            'flag_id' => 'required|exists:flag_types,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        } else {
            $flag = UserFlag::create([
                'headshot_id' => $data['headshot_id'],
                'flag_id' => $data['flag_id'],
                'user_id' => 1
            ]);
            return response()->json([
                'code' => 200,
                'message' => 'Flag Marked',
                'flag' => $flag
            ], 200);
        }
    }
    public function makeUser()
    {
        $newUser = new ProfileProgress;
        $newUser->device_id = $this->generateUniqueToken();
        $newUser->battery_level = 0;
        $newUser->account_level = 0;
        $newUser->available_contacts = '0';
        $newUser->save();
        return response()->json([
            'code' => 200,
            'message' => 'Account made',
            'user' => $newUser
        ], 200);
    }
    public function generateUniqueToken()
    {
        $token = Str::random(40); // Adjust the length as needed

        // Ensure the token is unique in your database
        while (ProfileProgress::where('device_id', $token)->exists()) {
            $token = Str::random(40);
        }

        return $token;
    }
    public function getAllProfile()
    {

        $user = auth()->user();
        $savedProfileIds = SavedFeed::where('user_id', $user->id)->pluck('profile_id')->toArray();
        $profiles = ProfileProgress::where('account_level', 3)->latest()->get();

        // Fetch additional data for each profile if needed
        $arr = [];
        foreach ($profiles as $profile) {
            $profileData = $profile->toArray();

            // Check if profile is saved
            $profileData['is_saved'] = in_array($profile->id, $savedProfileIds);

            // Fetch headshot for the profile
            $headshot = Headshots::where('device_id', $profile->id)->where('type_id', 2)->first();

            if ($headshot !== null) {
                // Add headshot data to profile data
                $profileData['headshot'] = $headshot;
            }

            $arr[] = $profileData;
        }

        return response()->json([
            'code' => 200,
            'message' => 'Data Fetched',
            'data' => $arr
        ], 200);
    }
    public function getProfileProgress(Request $request)
    {
        $data = $request->all();
        $validator = validator::make(
            $data,
            [
                'device_id' => 'required|exists:profile_progress,id'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(), 'code' => 422], 200);
        } else {
            $profile = ProfileProgress::with('user_details',  'user_attributes')
                ->where('id', $data['device_id'])
                ->first();

            if ($profile) {
                return response()->json([
                    'code' => 200, 'message' => 'Data Fetched',
                    'details' => $profile
                ], 200);
            }
        }
    }
    public function getAttributes(Request $request)
    {
        $data = $request->all();
        $validator = validator::make(
            $data,
            [
                'device_id' => 'required|exists:profile_progress,id'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        } else {

            $headshot = Headshots::where('device_id', $data['device_id'])->where('type_id', 2)->first();

            if (!$headshot) {
                return response()->json([
                    'code' => 404,
                    'message' => 'Headshot not found',
                ]);
            }
            Log::error('Check headshot is find againt the device_id ',  [$headshot]);
            $age = UserAttribute::where('headshot', $headshot->id)->where('attribute_type', 1)->first();
            $gender = UserAttribute::where('headshot', $headshot->id)->where('attribute_type', 2)->first();
            $race = UserAttribute::where('headshot', $headshot->id)->where('attribute_type', 3)->first();
            Log::error('Check age is find againt the device_id ',  [$age]);
            Log::error('Check gender is find againt the device_id ',  [$gender]);
            Log::error('Check race is find againt the device_id ',  [$race]);
            if (!$age || !$gender || !$race) {
                return response()->json([
                    'code' => 400,
                    'message' => 'User attributes not found',
                ], 400);
            }
            $genderArray =  json_decode($gender['attribute_name']);
            //dd(  $genderArray);

            $atts = [
                'age' =>  $age['attribute_name'],
                'gender' => $gender['attribute_name'],
                'ethnicity' =>  $race['attribute_name'],
            ];
            //dd($atts);

            $raceArray = json_decode($race['attribute_name']);

            $newArray3 = [];
            foreach ($raceArray as $key => $value) {
                $newKey = str_replace(' ', '/', $key);
                $newArray3[] = [
                    'name' => $newKey,
                    'percentage' => $value,
                ];
            }
            //dd( $newArray3 );
            $newArray4 = [];
            foreach ($genderArray as $key => $value) {
                $newKey = strtolower(str_replace(' ', '', $key));
                $newArray4[] = [
                    'name' => $newKey,
                    'percentage' => $value,
                ];
            }

            $atts['gender'] = $newArray4;
            $atts['ethnicity'] = $newArray3;
            $atts['profile_image'] = $headshot->url;
            return response()->json([
                'code' => 200,
                'message' => 'User attributes fetched successfully',
                'attributes' => $atts,
            ], 200);
        }
    }

    public function getEssence(int $id)
    {
        $essences = UserAttribute::where('profile', $id)->where('attribute_type', 5)->get()->toArray();
        //dd( $essences);
        //dd($professions[1]->agree);
        usort($essences, function ($a, $b) {
            return $b['agree'] <=> $a['agree'];
        });
        // Take the top two professions
        $essence = array_slice($essences, 0, 1);
        $final = [
            'name' => $essence[0]['attribute_name'],
            'likes' => $essence[0]['agree'] - 15
        ];
        return $final;
    }

    public function getAttributesbyid(int $id)
    {
        $headshot = Headshots::where('device_id', $id)->where('type_id', 2)->first();
        if (!$headshot) {
            return response()->json([
                'code' => 404,
                'message' => 'Headshot not found',
            ]);
        }
        $age = UserAttribute::where('headshot', $headshot->id)->where('attribute_type', 1)->first();
        $gender = UserAttribute::where('headshot', $headshot->id)->where('attribute_type', 2)->first();
        $race = UserAttribute::where('headshot', $headshot->id)->where('attribute_type', 3)->first();

        if (!$age || !$gender || !$race) {
            return response()->json([
                'code' => 404,
                'message' => 'User attributes not found',
            ]);
        }
        $genderArray =  json_decode($gender['attribute_name']);
        //dd(  $genderArray);

        $atts = [
            'age' =>  $age['attribute_name'],
            'gender' => $gender['answer'],
            'race' => $race['answer'],
        ];
        return $atts;
    }


    public function getPrimaryHeadshot(int $id)
    {
        $headshot = Headshots::where('device_id', $id)->where('type_id', 2)->value('url');
        return $headshot;
    }


    public function topProfessions(int $id)
    {
        $headshots = Headshots::where('device_id', $id)->get();
        if (!$headshots) {
            return response()->json([
                'code' => 404,
                'message' => 'User not found',
            ]);
        }
        // dd( $headshots ->pluck('id'));
        $professions = [];
        foreach ($headshots as $headshot) {
            $profession = UserAttribute::where('headshot', $headshot->id)->where('attribute_type', 4)->first();
            if ($profession) {
                $professions[] = $profession;
            }
        }
        //dd($professions[1]->agree);
        usort($professions, function ($a, $b) {
            return $b->agree <=> $a->agree;
        });
        // dd($professions);
        // Take the top two professions
        if (count($professions) == 1) {
            $topTwoProfessions[0] = [
                'name' => $professions[0]->answer,
                'likes' => $professions[0]->agree - 15
            ];
        } else {
            $topTwoProfessions = array_slice($professions, 0, 2);
            $topTwoProfessions[0] = [
                'name' => $topTwoProfessions[0]->answer,
                'likes' => $topTwoProfessions[0]->agree - 15
            ];
            $topTwoProfessions[1] = [
                'name' => $topTwoProfessions[1]->answer,
                'likes' => $topTwoProfessions[1]->agree - 15
            ];
        }
        return  $topTwoProfessions;
    }



    public function getActorProfile(Request $request)
    {
        $userId = User::where('profileprogess_id', $request->device_id)->first();
        $user =  $request->device_id;
        $device['user'] = User::where('profileprogess_id', $request->device_id)->first();
        $device['attributes'] = $this->getAttributesbyid($user);
        $device['profile_image'] = $this->getPrimaryHeadshot($user);
        $device['professions'] = $this->topProfessions($user);
        $device['essence'] = $this->getEssence($user);
        $device['user_details'] = UserDetail::where('device_id', $user)->first();
        $device['links'] =  $this->getLinks($userId->id);
        //  $details=$device->with('user_details_No')->first();
        // dd( $details);
        return response()->json([
            'code' => 200,
            'message' => 'User Fetched',
            'user' => $device
        ], 200);
    }
    public function getReel()
    {
        $reel = ActorReel::where('user_id', auth()->user()->id)
            ->latest('updated_at')
            ->first();


        return $reel;
    }

    public function extractThumbnail($videoPath, $outputPath = null, $time = '00:00:05')
    {
        // If output path is not provided, use a default location or generate a unique name
        if (!$outputPath) {
            $outputPath = 'thumbnails/' . uniqid() . '.jpg';
        }

        // Use FFmpeg to extract thumbnail
        $command = [
            'ffmpeg',
            '-i', $videoPath,
            '-ss', $time,
            '-vframes', '1',
            $outputPath,
        ];

        // Execute the command
        exec(implode(' ', $command));

        return $outputPath;
    }


    public function getProfile(Request $request)
    {
        $userId = Auth::user();
        $user = $userId->profileprogess_id;
        $device['attributes'] = $this->getAttributesbyid($user);
        $device['profile_image'] = $this->getPrimaryHeadshot($user);
        $device['professions'] = $this->topProfessions($user);
        $device['essence'] = $this->getEssence($user);
        $device['user_details'] = UserDetail::where('device_id', $user)->first();
        $device['media'] =  $this->getUserMedia();

        $device['links'] =  $this->getLinks(request()->user()->id);
        $device['reel'] =  $this->getReel();
        //  $details=$device->with('user_details_No')->first();
        // dd( $details);
        return response()->json([
            'code' => 200,
            'message' => 'User Fetched',
            'user' => $device
        ], 200);
    }



    public function getUserMedia()
    {

        $user = User::find(auth()->user()->id);

        // $device = $user->with('profileprogress')->first();
        $headshots = Headshots::where('device_id', $user->profileprogess_id)->get();

        if (!$headshots) {
            return response()->json([
                'message' => 'Headshots not found',
            ]);
        }
        // dd( $headshots ->pluck('id'));
        $professions = [];
        foreach ($headshots as $headshot) {
            $profession = UserAttribute::where('headshot', $headshot->id)->where('attribute_type', 4)->first();
            if ($profession) {
                $professions[] = $profession;
            }
        }
        //dd($professions[1]->agree);
        usort($professions, function ($a, $b) {
            return $b->agree <=> $a->agree;
        });
        //dd($professions);
        $resultProfessions = [];

        foreach ($professions as $profession) {
            $resultProfessions[] = [
                'url' => Headshots::where('id', $profession->headshot)->pluck('url')->first(),
                'name' => $profession->answer,
                'likes' => $profession->agree - 15
            ];
        }
        //$resultProfessions['reels'] =  $this->getReel();
        return $resultProfessions;
    }



    public function updateUser(Request $request)
    {
        log::error('all request data ', [$request->all()]);
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'physique' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'weight_height' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => 'Validation failed',
                'error' => $validator->errors()->first(),
            ], 200);
        }

        $user = User::with('profileprogress.user_details_No')->find(auth()->user()->id);

        if ($user->profileprogress) {
            $userDetails = $user->profileprogress->user_details_No;
            $values = explode(',',  $request->weight_height);
            Log::info('values', [$values]);
            // Extract values for meters and pounds
            $metersValue = (float)str_replace('m', '', trim($values[0])); // Remove 'm' and convert to float
            $poundsValue = (int)str_replace('lbs', '', trim($values[1])); // Remove 'lbs' and convert to integer
            Log::info('height', [$metersValue]);
            Log::info('weight', [$poundsValue]);
            $userDetails->name = $request->name;
            $userDetails->weight_height = $request->weight_height;
            $userDetails->physique = $request->physique;
            $userDetails->location = $request->location;
            $userDetails->weight = $poundsValue;
            $userDetails->height = $metersValue;
            $userDetails->save();
            $user->profileprogress->user_details_No->save();
        }
        // Save the changes
        $user->save();

        return response()->json([
            'code' => 200,
            'message' => 'User and details updated successfully',
            'user' => $user,
        ], 200);
    }


    public function updateLinks(Request $request)
    {
    }



    public function addReel(Request $request)
    {
        $data2 = $request->All();
        $validator = Validator::make($request->all(), [
            'reel' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 400);
        } else {

            $filename = FileUpload::handleVideofordigitaloceanstorage($request->file('reel'), 'actor/reels/');
            $data2['device_id'] = User::where('id', auth()->user()->id)->value('profileprogess_id');
            $dev = ProfileProgress::where('id', $data2['device_id'])->first();
            if ($dev->types_points < 100) {
                return response()->json(['Message' => 'You don,t have sufficient balance to upload reel', 422]);
            }

            $pointsDel = PointType::where('type', 'Reel')->value('points');
            $dev->types_points -= $pointsDel;
            if ($dev->types_points < 50) {
                $dev->account_level = 1;
            } else if ($dev->types_points > 50  && $dev->types_points < 120) {
                $dev->account_level = 2;
            } else  if ($dev->types_points > 300) {
                $dev->account_level = 3;
            }
            $dev->save();
            // Validation passed, so add the data to the database
            UserSpending::create([
                'user' => auth()->user()->id,
                'amount' => 100,
                'spending_type' => 'Headshots Uploaded',
                'transaction_type' => 'Spent'
            ]);
            return response()->json([
                'message' => 'Reel Added successfully',
                'code' => 200,
                'data' => $filename,
            ], 200);
        }
    }

    public function addLinks(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'casting_networks' => 'nullable|url',
            'instagram' => 'nullable|url',
            'tiktok' => 'nullable|url',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'message' => 'Validation failed',
                'error' => $validator->errors()->first(),
            ], 200);
        }
        $userId = Auth::user();
        if ($userId) {
            $link = UserLink::updateOrCreate(
                ['user_id' => $userId->id],
                [
                    'casting_networks' => $request->casting_networks ?? null,
                    'instagram' => $request->instagram ?? null,
                    'tiktok' => $request->tiktok ?? null,
                ]
            );
            return response()->json([
                'message' => 'Link Added successfully',
                'code' => 200,
                'social_links' => $link,
            ], 200);
        } else {
            return response()->json([
                'message' => 'User Is not Found',
                'code' => 401,
            ], 200);
        }
    }

    public function getLinks(int $id)
    {
        $user = User::find($id);
        $data = UserLink::where('user_id', $user->id)->first();
        return   $data;
    }


    public function pointsActivity(Request $request)
    {
        $lowerLimit = 0;
        $upperLimit = 10;
        $intervalSize = ($upperLimit - $lowerLimit) / 10;
        $user = User::find(auth()->user()->id);
        $device = ProfileProgress::where('id', $user->profileprogess_id)->first();
        $contactsAvailable = $device->battery_level / 500;
        $points['battery'] = $device->battery_level;
        $points['available_contacts'] = $contactsAvailable;
        $points['intervalSize'] = $intervalSize;
        return response()->json([
            'message' => 'Link Added successfully',
            'code' => 200,
            'data' => $points,
        ], 200);
    }

    public function  detailThroughHeadshot(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'headshot_id' => 'exists:headshots,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'message' => 'Validation failed',
                'error' => $validator->errors()->first(),
            ], 200);
        } else {
            $device = Headshots::where('id', $data['headshot_id'])->value('device_id');
            $userid = User::where('profileprogess_id', $device)->first();
            $user['details'] = UserDetail::where('device_id',  $device)->first();
            $user['social_links'] = UserLink::where('user_id', $userid->id)->first();
            $user['media'] = Headshots::where('device_id', $device)->get();
            return response()->json([
                'message' => 'Data Fetched successfully',
                'code' => 200,
                'data' => $user,
            ], 200);
        }
    }

    public function filmmakers()
    {
        $users = User::all();
        $performers = [];
        foreach ($users as $user) {
            if ($user->user_type === 'filmmaker') {
                $device['user'] = $user;
                $device['progress']=FilmMaker::where('user_id',$user->id)->get();
                $performers[] = $device;
            }
        };
        return response()->json(['message' => 'Performers fetched successfully', 'code' => 200, 'data' => $performers], 200);
    }

    public function performers()
    {
        $users = User::all();
        $performers = [];
        foreach ($users as $user) {
            if ($user->user_type === 'user') {
                $device['user'] = User::where('profileprogess_id', $user->profileprogess_id)->first();
                //  dd($user->profileprogess_id);
                $device['progress']=ProfileProgress::where('id',$user->profileprogess_id)->first();
                if(!isset($device['progress'])){
                    $device['progress']=null;
                    continue;
                }
                $device['attributes'] = $this->getAttributesbyid($user->profileprogess_id);
                $device['profile_image'] = $this->getPrimaryHeadshot($user->profileprogess_id);
                $device['professions'] = $this->topProfessions($user->profileprogess_id);
                $device['essence'] = $this->getEssence($user->profileprogess_id);
                $device['user_details'] = UserDetail::where('device_id', $user->profileprogess_id)->first();
                $device['links'] =  $this->getLinks($user->id);
                $performers[] = $device;
            }
        };
        return response()->json(['message' => 'Performers fetched successfully', 'code' => 200, 'data' => $performers], 200);
    }

    public function updateAiAttributes(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'device_id' => 'required|exists:profile_progress,id',
            'age' => 'required',
            'gender' => 'required',
            'race' => 'required',
        ]);
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'error' => $validator->errors()->first()
            ], 422);
        } else {
            $headshot = Headshots::where('device_id', $request->device_id)->where('type_id', 2)->value('id');
            $age = UserAttribute::where('headshot', $headshot)->where('attribute_type', 1)->first();
            $gender = UserAttribute::where('headshot', $headshot)->where('attribute_type', 2)->first();
            $race = UserAttribute::where('headshot', $headshot)->where('attribute_type', 3)->first();
            $age->answer = $request->age;
            $age->attribute_name = $request->age;

            $gender->answer = $request->gender;

            $race->answer = $request->race;

            $age->save();
            $gender->save();
            $race->save();
            return response()->json(['code' => 200, 'message' => 'Attributes updated successfully'], 200);
        }
    }

    public function userBattery(Request $request)
    {
        try {
            $user = ProfileProgress::where('id', $request->device_id)->first();
            if (isset($user)) {
                $data = $user->types_points;
                return response()->json(['battery_level' => $data, 'message' => 'battery level fetched', 'code' => 200], 200);
            } else {
                return response()->json(['error' => 'User Not found', 'code' => 400], 400);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getmessage(), 'code' => 400], 400);
        }
    }

    public function userEarnings()
    {
        $data2 = User::where('id', auth()->user()->id)->value('profileprogess_id');
        $dev = ProfileProgress::where('id', $data2)->first();
        $headshot = Headshots::where('device_id', $dev->id)->where('type_id', 2)->first();
        $earnings = UserSpending::where('user', auth()->user()->id)->get();
        if (count($earnings) === 0) {
            return response()->json(['message' => 'You have no transactions', 'code' => 200], 200);
        }
        return response()->json(['pic' => $headshot->url, 'data' => $earnings, 'message' => 'Earnings Fetched', 'code' => 200], 200);
    }
}
