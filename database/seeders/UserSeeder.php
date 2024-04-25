<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Essence;
use App\Models\UserLink;
use App\Models\Headshots;
use App\Models\UserDetail;
use Illuminate\Support\Str;
use App\Models\UserAttribute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('profile_progress')->insert([
            [
                'battery_level' => 0,
                'device_id' => Str::random(40),
                'account_level' => 0,
                'types_points' => 0,
                'invites_points' => 0
            ],
            [
                'battery_level' => 0,
                'device_id' => Str::random(40),
                'account_level' => 0,
                'types_points' => 0,
                'invites_points' => 0
            ],
        ]);
        /*
        $imagePath1 = ['storage\fake-user\elijah.jpg', 'storage\fake-user\gabriel.jpg', 'storage\fake-user\grande.jpg', 'storage\fake-user\grande3.jpg'];
        $imagePath2 = ['storage\fake-user\joseph.jpg', 'storage\fake-user\kristen1.jpg', 'storage\fake-user\omid.jpg', 'storage\fake-user\rand.jpg'];
        */
        $imagePath1=['https://casttypes-v2-bucket.nyc3.digitaloceanspaces.com/fakeHeadshots/1709805276.jpg','https://casttypes-v2-bucket.nyc3.digitaloceanspaces.com/fakeHeadshots/1709805774.jpg','https://casttypes-v2-bucket.nyc3.digitaloceanspaces.com/fakeHeadshots/1709805695.jpg','https://casttypes-v2-bucket.nyc3.digitaloceanspaces.com/fakeHeadshots/1709805303.jpg'];
        $imagePath2=['https://casttypes-v2-bucket.nyc3.digitaloceanspaces.com/fakeHeadshots/1709805806.jpg','https://casttypes-v2-bucket.nyc3.digitaloceanspaces.com/fakeHeadshots/1709805880.jpg','https://casttypes-v2-bucket.nyc3.digitaloceanspaces.com/fakeHeadshots/1709805743.jpg','https://casttypes-v2-bucket.nyc3.digitaloceanspaces.com/fakeHeadshots/1709805886.jpg'];
        $path = 'fakeHeadshots/';
        for ($i = 0; $i < 2; $i++) {
            for ($j = 0; $j < 4; $j++) {
                if ($i == 0) {
                    $filename = pathinfo($imagePath1[$j], PATHINFO_FILENAME);
                    $extension = pathinfo($imagePath1[$j], PATHINFO_EXTENSION);

                    // Generate a new filename with timestamp and random number
                    $filename = (time() + random_int(100, 1000)) . '.' . $extension;

                    // Set the destination file path
                    $filePath = $path . $filename;

                    // Upload the image to the specified storage disk
                    Storage::disk('spaces')->put($filePath, file_get_contents($imagePath1[$j]));

                    $url = Storage::disk('spaces')->url($filePath);
                    if ($j == 0)
                        $type = 2;
                    else
                        $type = 3;
                    Headshots::create([
                        'device_id' => 1,
                        'type_id' => $type,
                        'url' => $url,
                    ]);
                } else if ($i == 1) {

                    $filename = pathinfo($imagePath2[$j], PATHINFO_FILENAME);
                    $extension = pathinfo($imagePath2[$j], PATHINFO_EXTENSION);

                    // Generate a new filename with timestamp and random number
                    $filename = (time() + random_int(100, 1000)) . '.' . $extension;

                    // Set the destination file path
                    $filePath = $path . $filename;

                    // Upload the image to the specified storage disk
                    Storage::disk('spaces')->put($filePath, file_get_contents($imagePath2[$j]));

                    // Get the URL of the uploaded image
                    $url = Storage::disk('spaces')->url($filePath);
                    if ($j == 0)
                        $type = 2;
                    else $type = 3;
                    Headshots::create([
                        'device_id' => 2,
                        'type_id' => $type,
                        'url' => $url,
                    ]);
                }
            }
        }
        $fileUrls = Headshots::where('status', NULL)->get();
        foreach ($fileUrls as $fileUrl) {
            $this->processImage($fileUrl);
        }
        $age = ['28-30', '25-33'];
        $gender = ['Man', 'Woman'];
        $race = ['latino hispanic', 'indian'];

        for ($i = 0; $i < 2; $i++) {
            if ($i == 0) {
                UserDetail::create([
                    'device_id' => 1,
                    'name' => 'Chohan',
                    'physique' => 'Slender',
                    'location' => 'Uganda',
                    'IMDb' => 'https://www.youtube.com',
                    'age' =>  $age[0],
                    'gender' => $gender[0],
                    'ethnicity' => $race[0],
                    'height' => 191,
                    'weight' => 56,
                ]);

                UserAttribute::create([
                    'headshot' => 1,
                    'attribute_type' => 5,
                    'attribute_name' => 'Adele',
                    'agree' => 15,
                    'disagree' => 0,
                    'answer' => 'Adele',
                    'profile'=>1,
                ]);
            } else if ($i == 1) {
                UserDetail::create([
                    'device_id' => 2,
                    'name' => 'Khanzada',
                    'physique' => 'Athletic',
                    'location' => 'Zimbabwe',
                    'IMDb' => 'https://www.youtube.com',
                    'age' =>  $age[1],
                    'gender' => $gender[1],
                    'ethnicity' => $race[1],
                    'height' => 161,
                    'weight' => 74,
                ]);

                UserAttribute::create([
                    'headshot' => 1,
                    'attribute_type' => 5,
                    'attribute_name' => 'Adele',
                    'agree' => 15,
                    'disagree' => 0,
                    'answer' => 'Adele',
                    'profile'=>2
                ]);
            }
        }

        for ($i = 0; $i < 2; $i++) {
            if ($i == 0) {
                $user =  User::create([
                    'name'        => 'Chohan',
                    'barcode'    => str::random(10),
                    'email'       => 'ham@4.com',
                    'referrer_id' =>  null,
                    'profileprogess_id' => 1,
                    'password'    => Hash::make('sam'),
                    'email_verified_at' => now(),
                    'user_type'=>'user'
                ]);
            } else if ($i == 1) {
                $user =  User::create([
                    'name'        => 'Khanzada',
                    'barcode'    => str::random(10),
                    'email'       => 'ga@45.com',
                    'referrer_id' =>  null,
                    'profileprogess_id' => 2,
                    'password'    => Hash::make('sam'),
                    'email_verified_at' => now(),
                    'user_type'=>'user'

                ]);
            }
        }

        for ($i = 0; $i < 2; $i++) {
            if ($i == 0) {
                UserLink::create(
                    [
                        'user_id' => 1,
                        'casting_networks' => 'https://www.daf.com',
                        'instagram' =>  'https://www.fag.com',
                        'tiktok' =>  'https://www.ga.com',
                    ]
                );
            } else if ($i == 1) {
                UserLink::create(
                    [
                        'user_id' => 2,
                        'casting_networks' => 'https://www.dsa.com',
                        'instagram' =>  'https://www.fa.com',
                        'tiktok' =>  'https://www.ga.com',
                    ]
                );
            }
        }
    }
    private function processImage($file)
    {

        // Your existing code for processing images goes here

        $data2 = [
            'image_path1' => $file->url,
        ];
        //  dd($data2);
        $response2 = Http::timeout(600)->post(config('app.Python_URL') . '/analyze_images', $data2);
        $response2 = $response2->json();
        //dd(  $response2 );
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
            'image' => $file->url,
            'roboflow_api_key' =>  'alAsFria0lZ0HUhj5pXO',
        ];
        //  dd($data3);
        $response3 = Http::timeout(600)->post(config('app.Python_URL') . '/get_profession', $data3);
        $profession = $response3->json();
        $age = "{$lowerL}-{$upperL}";
        $atts = [$age, is_array($genderData) ? json_encode($genderData) : $genderData, is_array($topTwoRaces) ? json_encode($topTwoRaces) : $topTwoRaces, json_encode($profession)];
        $answers = [$age, $greaterGender, $greaterRace, ($profession['most_similar_tag'])];
        $c_answers = [$age, $lowerGender, $lowerRace, ($profession['most_similar_tag'])];
        //dd( $answers);
        $file->status = true;
        $file->save();
        for ($i = 0; $i < count($atts); ++$i) {

            UserAttribute::create([
                'headshot' => $file->id,
                'attribute_type' => $i + 1,
                'attribute_name' => $atts[$i],
                'agree' => 15,
                'disagree' => 0,
                'answer' => $answers[$i],
                'profile'=>$file->device_id
            ]);
            //  dd('Attributes updated');
        }
    }
}
