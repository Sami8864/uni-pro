<?php

namespace App\Http\Controllers\api;


use App\Models\User;
use App\Models\Device;
use GuzzleHttp\Client;
use App\Models\Headshots;
use App\Models\ImageType;
use App\Traits\FileUpload;
use App\Models\AiAttribute;
use App\Models\UserSpending;
use Illuminate\Http\Request;
use App\Models\InitialSurvey;
use App\Models\UserAttribute;
use App\Models\ProfileProgress;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Interfaces\QuestionnaireRepositoryInterface;
use App\Models\Notification as NotificationModel; // Alias the Notification model
use App\Traits\Notification as NotificationTrait; // Alias the Notification trait

use App\Models\PointType;

class InitialQuestionnaire extends Controller
{


    use NotificationTrait;
    private QuestionnaireRepositoryInterface $questionRepository;


    private $option, $answer, $points;
    private $roboflowApiKey = 'alAsFria0lZ0HUhj5pXO';

    public function __construct(QuestionnaireRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function firstQuestion()
    {

        $questionnaire = $this->questionRepository->load();

        return response()->json([
            'message' => 'Questions fetched successfully',
            'code' => 200,
            'questionnaire' =>  $questionnaire,

        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'option' => 'required',
            'answer' => 'required',
            'points' => 'required',
            'image' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $questionnaire = $this->questionRepository->store($data);

        return response()->json([
            'message' => 'Questionnaire Added',
            'code' => 200,
            'questionnaire' => $questionnaire
        ], 200);
    }
    public function Answer(Request $request)
    {

        $data = $request->all();
        // return response()->json($data);
        $validator = Validator::make($data, [
            'device_id' => 'required|exists:profile_progress,id',
            'selection_id' => 'required',
            'headshot_id' => 'required|exists:user_attributes,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first(), 'code' => 422], 422);
        }

        $progress = ProfileProgress::where('id', $data['device_id'])->first();

        if ($data['selection_id'] == 1) {
            $resp = $this->questionRepository->agree($data['device_id'], $data['headshot_id']);
            return response()->json([
                'message' => 'Account Updated',
                'battery_level' =>  $resp['battery'],
                'account_level' =>  $resp['account'],
            ], 200);
        } else if ($data['selection_id'] == 0) {
            $resp = $this->questionRepository->disagree($data['device_id'], $data['headshot_id']);
            return response()->json([
                'message' => 'Account Updated',
                'battery_level' =>  $resp['battery'],
                'account_level' =>  $resp['account'],
            ], 200);
        } else if ($data['selection_id'] == 2) {
            $pointsDel = PointType::where('type', 'Swiper_del')->value('points');
            $progress->types_points += $pointsDel;
            $progress->types_points = $progress->types_points + $pointsDel;
            if ($progress->types_points < 50) {
                $progress->account_level = 1;
            } else if ($progress->types_points > 50  && $progress->types_points < 120) {
                $progress->account_level = 2;
            } else  if ($progress->types_points > 300) {
                $progress->account_level = 3;
            }
            $progress->save();

            return response()->json(
                [
                    'message' => 'Account Updated',
                    'battery_level' =>  $progress->types_points,
                    'account_level' => $progress->account_level,
                ],
                200
            );
        }
    }


    public function analyzeFace(Request $request)
    {

        // Get the uploaded image from the request
        $uploadedImage = $request->file('image');
        //dd($uploadedImage);
        // Generate a unique filename based on the current timestamp
        $filename = $uploadedImage->getClientOriginalName();

        // Move the uploaded image to the temp_images directory
        $uploadedImage->move('temp_images', $filename);

        // Get the full path to the uploaded image
        $imagePath1 = config('app.url') . "/temp_images/$filename";

        // Assuming you have a second image or you can dynamically generate it
        $imagePath2 = config('app.url') . "temp_images/$filename";

        // Extract the path part from the URL
        $path = parse_url($imagePath1, PHP_URL_PATH);

        // Convert the URL-encoded characters to regular characters
        $path = urldecode($path);

        // Now $path contains something like "/temp_images/user_uploaded_image.jpg"

        // Assuming your Laravel project is in C:\Users\DELL\Desktop\Deepface
        $fullPath = base_path('public') . $path;

        // $fullPath should now contain the full local file path, like
        // "C:\Users\DELL\Desktop\Deepface\public\temp_images\user_uploaded_image.jpg"

        $data = [
            'image_path1' => $fullPath
            //  'image_path2' => 'C:\Users\DELL\Desktop\Deepface\storage\app\public\temp_images\user_uploaded_image.jpg',

        ];
        //dd($data);
        $result = Http::timeout(600)->post('http://127.0.0.1:5000/process_images', $data);

        // Process $result as needed
        $result =   $result->json();

        // For testing, you can return the result as a response
        return response()->json(['result' => $result]);
    }
    public function detectFace(Request $request)
    {

        $data2 = $request->All();
        $validator = validator::make($data2, [
            'device_id' => 'required',
            'image_set' => 'required|min:1|max:10'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $existing = Headshots::where('device_id', json_decode($data2['device_id']))->where('type_id', 2)->first();
        //dd($existing);
        if (isset($existing)) {
            $existing->type_id = 3;
            $existing->save();
        }
        $uploadedSet = $request->file('image_set');
        if (count($uploadedSet) == 1) {

            //$type = ImageType::where('name', 'primary_image')->pluck('id')->first();
            // $filename1 = $uploadedSet[0]->getClientOriginalName();
            $filename2 = 0;
            // first step
            $filename = (time() + random_int(100, 1000));
            $extension = $uploadedSet[0]->getClientOriginalExtension();
            $filename1 = $filename . '.' . $extension;
            $filePath = 'temp_images/' . $filename1;
            $path = Storage::disk('spaces')->put($filePath, file_get_contents($uploadedSet[0]));
            // dd($path);
            Log::error('images is uploaded or not.', [$path]);
            $fullPath = Storage::disk('spaces')->url($filePath);
            $uid_id = json_decode($data2['device_id']);
            $fileUrl =  Headshots::create([
                'device_id' => $uid_id,
                'type_id' =>  2,
                'url' => $fullPath,
            ]);
            $data2 = [
                'image_path1' => $fullPath
            ];
            Log::error('images.', $data2);
            $response2 = Http::timeout(600)->post(config('app.Python_URL') . '/analyze_images', $data2,);

            $response2 = $response2->json();

            $response2 = json_encode($response2);
            $responseArray = json_decode($response2, true);

            // Check if 'expression' key exists
            if (isset($responseArray['expression'][0])) {
                $result = $responseArray['expression'][0];
                $lowerL = $result['age'] - 2;

                $upperL = $result['age'] + 2;
                $genderData = $result['gender'];
                $dominantRace = $result['race'];
                $greaterGender = array_search(max($genderData), $genderData);
                $lowerGender = array_search(min($genderData), $genderData);

                //          dd($greater);
                arsort($dominantRace);
                $topTwoRaces = array_slice($dominantRace, 0, 2);
                $greaterRace = array_search(max($topTwoRaces), $topTwoRaces);
                $lowerRace = array_search(min($topTwoRaces), $topTwoRaces);

                //    dd($topTwoRaces);
            } else {
                return response()->json([
                    'message' =>  'image is not processable please try again or update images',
                    'code' => 200,
                    'unmatched_images' => [$fullPath,]
                ], 200);
            }

            $data3 = [
                'image' => $fullPath,
                'roboflow_api_key' => $this->roboflowApiKey,
            ];
            //  dd($data3);
            $response3 = Http::timeout(600)->post(config('app.Python_URL') . '/get_profession', $data3);
            $profession = $response3->json();
            //  dd($profession);
            $age = "{$lowerL}-{$upperL}";
            $atts = [$age, is_array($genderData) ? json_encode($genderData) : $genderData, is_array($topTwoRaces) ? json_encode($topTwoRaces) : $topTwoRaces, json_encode($profession)];
            $answers = [$age, $greaterGender, $greaterRace, ($profession['most_similar_tag'])];
            //$c_answers = [$age, $lowerGender, $lowerRace, ($profession['most_similar_tag'])];
            //dd( $answers);
            $fileUrl->status = true;
            $fileUrl->save();
            $response['matched_images'] =  $fileUrl->url;
            for ($i = 0; $i < count($atts); ++$i) {

                UserAttribute::create([
                    'headshot' => $fileUrl->id,
                    'attribute_type' => $i + 1,
                    'attribute_name' => $atts[$i],
                    'agree' => 15,
                    'disagree' => 0,
                    'answer' => $answers[$i],
                    'profile' => $uid_id,
                ]);
            }
            return response()->json([
                'message' =>  'Headshot Uploaded',
                'code' => 200,
            ], 200);
        }
        if (isset($data2["primary_id"])) {
            $var = $data2["primary_id"];
            $var = json_decode($var) - 1;
            $temp = $uploadedSet[$var];
            $uploadedSet[$var] = $uploadedSet[0];
            $uploadedSet[0] = $temp;
        }
        Log::error('Check primary id  ', [$data2["primary_id"]]);
        // dd(  $uploadedSet[0]);
        /*
        $uploadedImage = array_shift($uploadedSet);
        $filename1 = $uploadedImage->getClientOriginalName();
        $firstimage =  FileUpload::imageUpload($uploadedImage, 'temp_images/');
        // Log::info('First image for firstimage', $firstimage  );
        // Create a new subdirectory for the set of images
        */
        $setDirectoryName = 'temp_images/' . time() . '/'; // Use a unique identifier, like a timestamp
        // mkdir($setDirectoryName);
        // Move each image in the set to the new subdirectory
        $urlArray = [];
        foreach ($uploadedSet as $comp) {
            $urlArray[] = FileUpload::imageUpload($comp, $setDirectoryName);
        }
        // Log::info('all images for urlArray.', $urlArray  );
        $imagePath1 = $urlArray[0];
        $data = [
            'image_path1' =>   $imagePath1,
            'image_path2' => $urlArray
        ];
        foreach ($urlArray as $filePath) {
            if ($filePath == $imagePath1) {
                $type = 2;
            } else {
                $type = 3;
            }
            $head[] = Headshots::create([
                'device_id' => json_decode($request->device_id),
                'type_id' =>  $type,
                'url' =>  $filePath,
            ]);
        }
        $response2 = Http::timeout(600)->post(config('app.Python_URL') . '/analyze_images', $data);
        $response2 = $response2->json();
        $response2 = json_encode($response2);
        $responseArray = json_decode($response2, true);

        // Check if 'expression' key exists
        if (isset($responseArray['expression'][0])) {
            $result = $responseArray['expression'][0];
            $lowerL = $result['age'] - 2;

            $upperL = $result['age'] + 2;
            $genderData = $result['gender'];
            $dominantRace = $result['race'];
            $greaterGender = array_search(max($genderData), $genderData);
            $lowerGender = array_search(min($genderData), $genderData);

            //          dd($greater);
            arsort($dominantRace);
            $topTwoRaces = array_slice($dominantRace, 0, 2);
            $greaterRace = array_search(max($topTwoRaces), $topTwoRaces);
            $lowerRace = array_search(min($topTwoRaces), $topTwoRaces);

            //    dd($topTwoRaces);
        } else {
            return response()->json([
                'message' =>  'Image not Found',
                'code' => 400,
            ], 400);
        }

        $data3 = [
            'image' => $imagePath1,
            'roboflow_api_key' => $this->roboflowApiKey,
        ];
        //  dd($data3);
        $response3 = Http::timeout(600)->post(config('app.Python_URL') . '/get_profession', $data3);
        $profession = $response3->json();
        //  dd($profession);
        $age = "{$lowerL}-{$upperL}";
        $atts = [$age, is_array($genderData) ? json_encode($genderData) : $genderData, is_array($topTwoRaces) ? json_encode($topTwoRaces) : $topTwoRaces, json_encode($profession)];
        $answers = [$age, $greaterGender, $greaterRace, ($profession['most_similar_tag'])];
        $c_answers = [$age, $lowerGender, $lowerRace, ($profession['most_similar_tag'])];
        //dd( $answers);
        //dd($head[0]->url);
        // dd()
        $id = json_decode($data2['device_id']);
        $head1 = Headshots::where('device_id', $id)->where('type_id', 2)->first();
        $head1->status = true;
        $head1->save();
        for ($i = 0; $i < count($atts); ++$i) {

            UserAttribute::create([
                'headshot' => $head1->id,
                'attribute_type' => $i + 1,
                'attribute_name' => $atts[$i],
                'agree' => 15,
                'disagree' => 0,
                'answer' => $answers[$i],
                'profile' => json_decode($data2['device_id']),
            ]);
        }

        return response()->json([
            'code' => 200, 'message' => "Headshots uplaoded successfully"
        ], 200);
    }


    public function ensureBalance(Request $request)
    {
        $data2 = $request->All();
        $validator = validator::make($data2, [
            'image_count' => 'required|min:1|max:10'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        } else {
            $data2['device_id'] = User::where('id', auth()->user()->id)->value('profileprogess_id');
            $dev = ProfileProgress::where('id', $data2['device_id'])->first();
            $uploadedSet = $request->file('image_set');
            $points = json_decode($dev->types_points);
            $count=json_decode($request->image_count);
            if ($points < (50 * $count)) {
                return response()->json([
                    'code' => -4,
                    'message' => 'You don,t have sufficient balance to upload more headshots',
                    'required_points' => (50 * $count) - $points,
                ], 200);
            } else {
                return response()->json([
                    'code' => 200,
                    'message' => 'You can move towards transaction page',
                ], 200);
            }
        }
    }



    public function uploadHeadshot(Request $request)
    {

        $data2 = $request->All();
        $validator = validator::make($data2, [
            'image_set' => 'required|min:1|max:10'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $data2['device_id'] = User::where('id', auth()->user()->id)->value('profileprogess_id');
        $dev = ProfileProgress::where('id', $data2['device_id'])->first();
        $uploadedSet = $request->file('image_set');
        $existing = Headshots::where('device_id', json_decode($data2['device_id']))->where('type_id', 2)->first();
        //dd($existing);
        if (isset($existing)) {
            $existing->type_id = 3;
            $existing->save();
        }
        if (count($uploadedSet) == 1) {
            //$type = ImageType::where('name', 'primary_image')->pluck('id')->first();
            // $filename1 = $uploadedSet[0]->getClientOriginalName();
            $filename2 = 0;
            // first step
            $filename = (time() + random_int(100, 1000));
            $extension = $uploadedSet[0]->getClientOriginalExtension();
            $filename1 = $filename . '.' . $extension;
            $filePath = 'temp_images/' . $filename1;
            $path = Storage::disk('spaces')->put($filePath, file_get_contents($uploadedSet[0]));
            // dd($path);
            Log::error('images is uploaded or not.', [$path]);
            $fullPath = Storage::disk('spaces')->url($filePath);
            $uid_id = json_decode($data2['device_id']);
            $fileUrl =  Headshots::create([
                'device_id' => $uid_id,
                'type_id' =>  2,
                'url' => $fullPath,
            ]);
            $data2 = [
                'image_path1' => $fullPath
            ];
            Log::error('images.', $data2);
            $response2 = Http::timeout(600)->post(config('app.Python_URL') . '/analyze_images', $data2,);
            $response2 = $response2->json();

            $response2 = json_encode($response2);
            $responseArray = json_decode($response2, true);

            // Check if 'expression' key exists
            if (isset($responseArray['expression'][0])) {
                $result = $responseArray['expression'][0];
                $lowerL = $result['age'] - 2;

                $upperL = $result['age'] + 2;
                $genderData = $result['gender'];
                $dominantRace = $result['race'];
                $greaterGender = array_search(max($genderData), $genderData);
                $lowerGender = array_search(min($genderData), $genderData);

                //          dd($greater);
                arsort($dominantRace);
                $topTwoRaces = array_slice($dominantRace, 0, 2);
                $greaterRace = array_search(max($topTwoRaces), $topTwoRaces);
                $lowerRace = array_search(min($topTwoRaces), $topTwoRaces);

                //    dd($topTwoRaces);
            } else {
                return response()->json([
                    'message' =>  'image is not processable please try again or update images',
                    'code' => 200,
                    'unmatched_images' => [$fullPath,]
                ], 200);
            }

            $data3 = [
                'image' => $fullPath,
                'roboflow_api_key' => $this->roboflowApiKey,
            ];
            //  dd($data3);
            $response3 = Http::timeout(600)->post(config('app.Python_URL') . '/get_profession', $data3);
            $profession = $response3->json();
            //  dd($profession);
            $age = "{$lowerL}-{$upperL}";
            $atts = [$age, is_array($genderData) ? json_encode($genderData) : $genderData, is_array($topTwoRaces) ? json_encode($topTwoRaces) : $topTwoRaces, json_encode($profession)];
            $answers = [$age, $greaterGender, $greaterRace, ($profession['most_similar_tag'])];
            //$c_answers = [$age, $lowerGender, $lowerRace, ($profession['most_similar_tag'])];
            //dd( $answers);
            $fileUrl->status = true;
            $fileUrl->save();
            $response['matched_images'] =  $fileUrl->url;
            for ($i = 0; $i < count($atts); ++$i) {

                UserAttribute::create([
                    'headshot' => $fileUrl->id,
                    'attribute_type' => $i + 1,
                    'attribute_name' => $atts[$i],
                    'agree' => 15,
                    'disagree' => 0,
                    'answer' => $answers[$i],
                    'profile' => $uid_id,
                ]);
            }

            $pointsDel=PointType::where('type','Image')->value('points');
            foreach ($uploadedSet as $key) {
                $dev->types_points -= $pointsDel;
                if ($dev->types_points < 50) {
                    $dev->account_level = 1;
                } else if ($dev->types_points > 50  && $dev->types_points < 120) {
                    $dev->account_level = 2;
                } else  if ($dev->types_points > 300) {
                    $dev->account_level = 3;
                }
                $dev->save();
            }
            UserSpending::create([
                'user'=>auth()->user()->id,
                'amount'=>count($uploadedSet)*50,
                'spending_type'=>'Headshots Uploaded',
                'transaction_type'=>'Spent'
            ]);
            $id = User::where('id', request()->user()->id)->first();
            $token = Device::where('user_id', auth()->user()->id)->latest()->pluck('device_token')->first();
            $response = $this->send('User', 'You Uploaded Headshot', [$token], $id->getdata(), 'muted', 'headshoot');
            Log::info('User fcm Token', [$token]);
            Log::info('response of fcm ', [$response]);
            return response()->json([
                'message' =>  'Headshot Uploaded',
                'code' => 200,
                'balance_deducted' => count($uploadedSet) * 50,
                'balance_after_deduction' => $dev->types_points,
            ], 200);
        }
        if (isset($data2["primary_id"])) {
            $var = $data2["primary_id"];
            $var = json_decode($var) - 1;
            $temp = $uploadedSet[$var];
            $uploadedSet[$var] = $uploadedSet[0];
            $uploadedSet[0] = $temp;
        }
        Log::error('Check primary id  ', [$data2["primary_id"]]);
        $setDirectoryName = 'temp_images/' . time() . '/'; // Use a unique identifier, like a timestamp
        // mkdir($setDirectoryName);
        // Move each image in the set to the new subdirectory
        $urlArray = [];
        foreach ($uploadedSet as $comp) {
            $urlArray[] = FileUpload::imageUpload($comp, $setDirectoryName);
        }
        // Log::info('all images for urlArray.', $urlArray  );
        $imagePath1 = $urlArray[0];
        $data = [
            'image_path1' =>   $imagePath1,
            'image_path2' => $urlArray
        ];
        foreach ($urlArray as $filePath) {
            if ($filePath == $imagePath1) {
                $type = 2;
            } else {
                $type = 3;
            }
            $head[] = Headshots::create([
                'device_id' => json_decode($data2['device_id']),
                'type_id' =>  $type,
                'url' =>  $filePath,
            ]);
        }
        $response2 = Http::timeout(600)->post(config('app.Python_URL') . '/analyze_images', $data);
        $response2 = $response2->json();
        $response2 = json_encode($response2);
        $responseArray = json_decode($response2, true);

        // Check if 'expression' key exists
        if (isset($responseArray['expression'][0])) {
            $result = $responseArray['expression'][0];
            $lowerL = $result['age'] - 2;

            $upperL = $result['age'] + 2;
            $genderData = $result['gender'];
            $dominantRace = $result['race'];
            $greaterGender = array_search(max($genderData), $genderData);
            $lowerGender = array_search(min($genderData), $genderData);

            //          dd($greater);
            arsort($dominantRace);
            $topTwoRaces = array_slice($dominantRace, 0, 2);
            $greaterRace = array_search(max($topTwoRaces), $topTwoRaces);
            $lowerRace = array_search(min($topTwoRaces), $topTwoRaces);
            //    dd($topTwoRaces);
        } else {
            return response()->json([
                'message' =>  'Image not Found',
                'code' => 400,
            ], 400);
        }
        $data3 = [
            'image' => $imagePath1,
            'roboflow_api_key' => $this->roboflowApiKey,
        ];
        //  dd($data3);
        $response3 = Http::timeout(600)->post(config('app.Python_URL') . '/get_profession', $data3);
        $profession = $response3->json();
        //  dd($profession);
        $age = "{$lowerL}-{$upperL}";
        $atts = [$age, is_array($genderData) ? json_encode($genderData) : $genderData, is_array($topTwoRaces) ? json_encode($topTwoRaces) : $topTwoRaces, json_encode($profession)];
        $answers = [$age, $greaterGender, $greaterRace, ($profession['most_similar_tag'])];
        $c_answers = [$age, $lowerGender, $lowerRace, ($profession['most_similar_tag'])];
        $id = json_decode($data2['device_id']);
        $head1 = Headshots::where('device_id', $id)->where('type_id', 2)->first();
        $head1->status = true;
        $head1->save();
        for ($i = 0; $i < count($atts); ++$i) {

            UserAttribute::create([
                'headshot' => $head1->id,
                'attribute_type' => $i + 1,
                'attribute_name' => $atts[$i],
                'agree' => 15,
                'disagree' => 0,
                'answer' => $answers[$i],
                'profile' => json_decode($data2['device_id']),
            ]);
        }

        $pointsDel=PointType::where('type','Image')->value('points');
        foreach ($uploadedSet as $key) {
            $dev->types_points -= $pointsDel;
            if ($dev->types_points < 50) {
                $dev->account_level = 1;
            } else if ($dev->types_points > 50  && $dev->types_points < 120) {
                $dev->account_level = 2;
            } else  if ($dev->types_points > 300) {
                $dev->account_level = 3;
            }
            $dev->save();
        }
        UserSpending::create([
            'user'=>auth()->user()->id,
            'amount'=>count($uploadedSet)*50,
            'spending_type'=>'Headshots Uploaded',
            'transaction_type'=>'Spent'
        ]);
        $id = User::where('id', request()->user()->id)->first();
        $token = Device::where('user_id', auth()->user()->id)->latest()->pluck('device_token')->first();

        $response = $this->send('User', 'You Uploaded Headshot', [$token], $id->getdata(), '', 'headshot');
        Log::info('User fcm Token', [$token]);
        Log::info('response of fcm ', [$response]);

        return response()->json([
            'code' => 200, 'message' => "Headshots uplaoded successfully",
            'balance_deducted' => count($uploadedSet) * 50,
            'balance_after_deduction' => $dev->types_points,
        ], 200);
    }
    public function primaryHeadshot(Request $request)
    {

        $data = $request->all();

        $validator = Validator::make($data, [
            'headshot_id' => 'required|exists:headshots,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        } else {

            $this->questionRepository->primaryHeadshot($data);

            return response()->json(['message' => "Primary Headshot Selected"], 200);
        }
    }

    public function reUpload(Request $request)
    {
        $data = $request->All();
        $validator = validator::make($data, [
            'device_id' => 'required',
            'image_set' => 'required|min:1|max:10'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $uploadedSet = $request->file('image_set');
        if (count($uploadedSet) == 0) {
            return response()->json(['message' => 'You did not add any new headshot'], 200);
        }
        if (count($uploadedSet) == 1) {
            $image2 = Headshots::where('device_id', $data['device_id'])->pluck('url')->first();
            $type = ImageType::where('name', 'regular_image')->pluck('id')->first();
            $uploadedImage = $request->file('headshot');
            $filename1 = $uploadedImage->getClientOriginalName();
            $uploadedImage->move('temp_images', $filename1);

            $imagePath1 = config('app.url') . "/temp_images/$filename1";
            $imagePath2 = $image2;
            // Extract the path part from the URL
            $path = parse_url($imagePath1, PHP_URL_PATH);
            $path2 = parse_url($imagePath2, PHP_URL_PATH);
            $path = urldecode($path);
            $path2 = urldecode($path2);
            // Assuming your Laravel project is in C:\Users\DELL\Desktop\Deepface
            $fullPath = base_path('public') . $path;
            $fullPath2 = base_path('public') . $path2;

            $data2 = [
                'image_path1' => $fullPath,
                'image_path2' => $fullPath2
            ];
            $response = Http::timeout(600)->post('http://127.0.0.1:5000/compare_images', $data2);
            $response = $response->json();
            $comparison = $response["verified"];
            if ($comparison == true) {
                $path = 'public/headshots/';
                $actualFilename = pathinfo($fullPath, PATHINFO_BASENAME);
                $file = new UploadedFile($fullPath, $actualFilename);
                $fileUrl = FileUpload::handleImage2($file, $path, $request->device_id, $type);

                $baseImagePath = base_path('public/temp_images');
                // Convert faces_detected path to URL
                // dd($response['faces_detected']);
                if (isset($response['faces_detected']) && isset($response['similarity']) && is_array($response['similarity'][0])) {
                    $response2 = Http::timeout(600)->post('http://127.0.0.1:5000/analyze_images', $data);
                    $response2 = $response2->json();
                    $response2 = json_encode($response2);
                    $responseArray = json_decode($response2, true);

                    // Check if 'expression' key exists
                    if (isset($responseArray['expression'][0])) {
                        $result = $responseArray['expression'][0];
                        $lowerL = $result['age'] - 2;

                        $upperL = $result['age'] + 2;
                        $genderData = $result['gender'];
                        $dominantRace = $result['race'];
                        $greaterGender = array_search(max($genderData), $genderData);
                        $lowerGender = array_search(min($genderData), $genderData);

                        //          dd($greater);
                        arsort($dominantRace);
                        $topTwoRaces = array_slice($dominantRace, 0, 2);
                        $greaterRace = array_search(max($topTwoRaces), $topTwoRaces);
                        $lowerRace = array_search(min($topTwoRaces), $topTwoRaces);

                        //    dd($topTwoRaces);
                    } else {
                        dd("Expression key not found in the response");
                    }
                    $data3 = [
                        'image' => $fullPath,
                        'roboflow_api_key' => $this->roboflowApiKey,
                    ];
                    //  dd($data3);
                    $response3 = Http::timeout(600)->post('http://127.0.0.1:5000/get_profession', $data3);
                    $profession = $response3->json();
                    //  dd($profession);
                    $age = "{$lowerL}-{$upperL}";
                    $atts = [$age, is_array($genderData) ? json_encode($genderData) : $genderData, is_array($topTwoRaces) ? json_encode($topTwoRaces) : $topTwoRaces, json_encode($profession)];
                    $answers = [$age, $greaterGender, $greaterRace, ($profession['most_similar_tag'])];
                    $c_answers = [$age, $lowerGender, $lowerRace, ($profession['most_similar_tag'])];
                    //dd( $answers);
                    $fileUrl->status = true;
                    $fileUrl->save();
                    for ($i = 0; $i < count($atts); ++$i) {

                        UserAttribute::create([
                            'headshot' => $fileUrl->id,
                            'attribute_type' => $i + 1,
                            'attribute_name' => $atts[$i],
                            'agree' => 0,
                            'disagree' => 0,
                            'answer' => $answers[$i],
                            'counter_answer' => $c_answers[$i],
                            'population_answer' => $answers[$i]
                        ]);
                    }


                    return response()->json([
                        'code' => 200,
                        'message' => 'Image Uploaded'
                    ], 200);
                } else {
                    return response()->json([
                        'code' => 200,
                        'message' => 'Still a bad Headshot,Re-upload it !'
                    ], 200);
                }

                $setDirectoryName = 'temp_images/' . time(); // Use a unique identifier, like a timestamp
                mkdir($setDirectoryName);

                // Move each image in the set to the new subdirectory
                foreach ($uploadedSet as $comp) {
                    $filename2 = $comp->getClientOriginalName();
                    $comp->move($setDirectoryName, $filename2);
                }
                //$uploadedFilenames = [$filename1];
                $directory = base_path('public/') . urldecode(parse_url($setDirectoryName, PHP_URL_PATH));
                $filesInDirectory = glob($directory . '/*');
                //dd( $filesInDirectory );
                // Add the URLs of all the images in the directory to $uploadedFilenames
                $uploadedFilenames = $filesInDirectory;

                // Get the full path to the uploaded image
                $imagePath1 = Headshots::where('device_id', $data['device_id'])->pluck('url')->first();
                // Assuming you have a second image or you can dynamically generate it
                $imagePath2 = config('app.url') . "/$setDirectoryName";
                // Extract the path part from the URL
                $path = parse_url($imagePath1, PHP_URL_PATH);
                $path2 = parse_url($imagePath2, PHP_URL_PATH);

                // Convert the URL-encoded characters to regular characters
                $path = urldecode($path);
                $path2 = urldecode($path2);

                // Assuming your Laravel project is in C:\Users\DELL\Desktop\Deepface
                $fullPath = base_path('public') . $path;
                $fullPath2 = base_path('public') . $path2;

                $data = [
                    'image_path1' => $fullPath,
                    'image_path2' => $fullPath2
                ];
                //dd($data);
                $result = Http::timeout(600)->post('http://127.0.0.1:5000/process_images', $data);

                $response = $result->json();
                $unmatchedFilenames = array_diff($uploadedFilenames, array_column($response['similarity'][0], 'identity'));
                $matchedFilenames = array_diff($uploadedFilenames, $unmatchedFilenames);
                $matchedFilenames[] = $fullPath;
                //dd($matchedFilenames);
                $type = ImageType::where('name', 'regular_image')->pluck('id')->first();
                foreach ($matchedFilenames as $filePath) {
                    // Add the URL to the array
                    $path = 'public/headshots/'; // Adjust the storage path
                    // Assuming $filePath contains 'E:\CasType\CasType\public/temp_images/1705393054/gettyimages-510726876-612x612.jpg'
                    $actualFilename = pathinfo($filePath, PATHINFO_BASENAME);
                    $file = new UploadedFile($filePath, $actualFilename);
                    $fileUrl = FileUpload::handleImage2($file, $path, $request->device_id, $type);
                }

                $baseImagePath = base_path('public/temp_images');
                // Convert faces_detected path to URL
                // dd($response['faces_detected']);
                if (isset($response['faces_detected']) && isset($response['similarity']) && is_array($response['similarity'][0])) {
                    $response['creds'] = Http::timeout(600)->post('http://127.0.0.1:5000/analyze_images', $data);

                    $response['creds'] = $response['creds']->json();
                    $data2 = $response['creds'];
                    $dominantAge = $data2['expression'][0]['age'];
                    $upperL = $dominantAge + 2;
                    $lowerL = $dominantAge - 2;
                    $dominantGender = $data2['expression'][0]['dominant_gender'];
                    $dominantRace = $data2['expression'][0]['dominant_race'];

                    AiAttribute::create([
                        'device_id' => $request->device_id,
                        'age' => "{$lowerL}-{$upperL}",
                        'ethnicity' => $dominantRace,
                        'gender' => $dominantGender,
                    ]);
                }
            }
            /* Continue with the rest of your code
            if (count($unmatchedFilenames) == 0) {

                return response()->json([
                    'message' =>  'All shots are fine!',
                    'result' => $response
                ], 200);
            }


            $baseImagePath = base_path('public/temp_images');

            $response['faces_detected'] = url(str_replace('\\', '/', str_replace($baseImagePath, '/temp_images', $response['faces_detected'])));

            // Convert identity paths in the result array to URLs
            //dd($response['similarity'][0]);
            foreach ($response['similarity'][0] as &$entry) {
                $entry['identity'] = url(str_replace('\\', '/', str_replace($baseImagePath, '/temp_images', $entry['identity'])));
                $urlWithoutSpaces = str_replace(' ', '%20',  $entry['identity']);
                $entry['identity'] = $urlWithoutSpaces;
            }
            // Convert unmatched filenames to URLs
            $unmatchedImageUrls = [];
            foreach ($unmatchedFilenames as $filename) {
                $url = url(str_replace('\\', '/', str_replace($baseImagePath, '/temp_images', $filename)));
                $urlWithoutSpaces = str_replace(' ', '%20', $url);

                // Add the URL to the array
                $unmatchedImageUrls[] = $urlWithoutSpaces;
            }
            // Add the URLs to the response
            $response['unmatched_images'] = $unmatchedImageUrls;
            //dd( $response);
            return response()->json(['result' =>  $response]);*/
        }
    }

    public function sendRequest(Request $request)
    {
        $data = $request->all();
        $validator = validator::make($data, [
            'image' => 'required|min:1'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        // into 127.......
        $uploadedImage = $request->file('image');
        $filename = (time() + random_int(100, 1000));
        $extension = $uploadedImage->getClientOriginalExtension();
        $filename1 = $filename . '.' . $extension;
        $filePath = 'temp_images/' . $filename1;
        $path = Storage::disk('spaces')->put($filePath, file_get_contents($uploadedImage));
        // dd($path);
        //Log::error('images is uploaded or not.',[$path]);
        $fullPath = Storage::disk('spaces')->url($filePath);
        $tags = 'man,woman,dog,space,transgender';
        $data2 = [
            'image' => $fullPath,
            'roboflow_api_key' => $this->roboflowApiKey,
        ];
        //dd( $data2);
        $response = Http::timeout(600)->post('http://127.0.0.1:5000/get_profession', $data2);
        $result = $response->json();
        //dd( $result);
        // Do something with the result
        return response()->json($result);
    }
}
