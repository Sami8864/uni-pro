<?php

namespace App\Console\Commands;

use App\Models\FileUrl;
use App\Models\Headshots;
use App\Models\UserAttribute;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProcessImages extends Command
{
    protected $signature = 'process:images';
    protected $description = 'Process images using Python APIs';

    public function handle()
    {
        $fileUrls = Headshots::where('status', NULL)->get();
        foreach ($fileUrls as $fileUrl) {
                $this->processImage($fileUrl);
        }
        $this->info('Image processing completed.');
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
        //  dd($profession);
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
                'profile'=>$file->device_id,
            ]);
        }
      //  dd('Attributes updated');
    }

}
