<?php

namespace App\Repositories;

use Aws\S3\S3Client;
use GuzzleHttp\Client;
use App\Models\Headshots;
use App\Models\ImageType;
use App\Traits\FileUpload;
use App\Models\InitialSurvey;
use App\Models\UserAttribute;
use App\Models\ProfileProgress;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\PointType;
use App\Interfaces\QuestionnaireRepositoryInterface;

class QuestionnaireRepository implements QuestionnaireRepositoryInterface
{
    use FileUpload;
    private $apiKey = "MBuWi0NaDY6tB3iL2pV_vrAZONEYwYB3", $apiSecret = "PIp1W3rw0VOeuIxNCVieeEh630nsqMne";

    public function load()
    {
        $randomHeadshots = UserAttribute::whereNotIn('user_attributes.id', function ($query) {
            $query->select('id')
                  ->from('user_attributes')
                  ->whereIn('attribute_type', [1]);
        })
        ->join('users', function($join) {
            $join->on('users.profileprogess_id', '=', 'user_attributes.profile')
                 ->whereNotNull('users.profileprogess_id');
        }) // Join with the users table
        ->inRandomOrder()
        ->distinct('user_attributes.attribute_type')
        ->take(20)
        ->get();
        $shots = [];
        Log::error('All randomHeadshots',  [$randomHeadshots] );
        foreach ($randomHeadshots as $shot) {

            $link = Headshots::where('id', $shot->headshot)->pluck('url')->first();
            $shots[] = [
                'headshot_id' =>$shot->headshot,
                'headshot' => $link,
                'answer' => $shot->answer
            ];

        }
        // You can further process the $randomHeadshots array or return it as-is
        return $shots;
    }
    public function store($data)
    {
        $filename = (time()+ random_int(100, 1000));
        $extension = $data['image']->getClientOriginalExtension();
        $filename = $filename . '.' . $extension;
        $filePath = 'question/' . $filename;
        $path = Storage::disk('spaces')->put($filePath, file_get_contents($data['image']));
        $path = Storage::disk('spaces')->url($filePath);

        $questionnaire = InitialSurvey::create([
            'option' => $data['option'],
            'answer' => $data['answer'],
            'image' => $url, // Store the file path, not the URL
            'points' => $data['points'],
        ]);
        return $questionnaire;
    }


    public function disagree(int $id, int $hid)
    {

        $headshot=UserAttribute::where('id',$hid)->first();
        $device = ProfileProgress::where('id',$id)->first();

        $headshot->disagree=$headshot->disagree+1;
        $headshot->save();

        if ($headshot->disagree >= $headshot->agree) {
            $pointsAdd=PointType::where('type','Swiper_add')->value('points');
            // dd($points);

            $device->types_points  = $device->types_points + $pointsAdd;
            if ($device->types_points < 50) {
                $device->account_level = 1;
            } else if ($device->types_points > 50  && $device->types_points < 120) {
                $device->account_level = 2;
            } else  if ($device->types_points > 300) {
                $device->account_level = 3;
            }
            $device->save();
        } else {
            $pointsDel=PointType::where('type','Swiper_del')->value('points');
            $device->types_points =  $device->types_points + $pointsDel;
                if ($device->types_points < 50) {
                    $device->account_level = 1;
                } else if ($device->types_points > 50  && $device->types_points < 120) {
                    $device->account_level = 2;
                } else  if ($device->types_points > 300) {
                    $device->account_level = 3;
                }
            $device->save();
        }
        $details = ['battery' => $device->types_points, 'account' => $device->account_level];
        return   $details;
    }

    public function agree(int $device_id, int $hi)
    {

        $headshot=UserAttribute::where('id',$hi)->first();
        $device = ProfileProgress::where('id',$device_id)->first();
        $headshot->agree=$headshot->agree+1;
        $headshot->save();

        if ($headshot->agree >= $headshot->disagree) {

            $pointsAdd=PointType::where('type','Swiper_add')->value('points');
            // dd($points);
            $device->types_points = $device->types_points +   $pointsAdd;
            if ($device->types_points < 50) {
                $device->account_level = 1;
            } else if ($device->types_points > 50  && $device->types_points < 120) {
                $device->account_level = 2;
            } else  if ($device->types_points > 300) {
                $device->account_level = 3;
            }
            $device->save();
        } else {

                $pointsDel=PointType::where('type','Swiper_del')->value('points');
                $device->types_points =  $device->types_points +  $pointsDel;
                if ($device->types_points < 50) {
                    $device->account_level = 1;
                } else if ($device->types_points > 50  && $device->types_points < 120) {
                    $device->account_level = 2;
                } else  if ($device->types_points > 300) {
                    $device->account_level = 3;
                }
            $device->save();
        }
        $details = ['battery' => $device->types_points, 'account' => $device->account_level];
        return   $details;
        }

    public function uploadHeadshots($data, $ids)
    {
        $type_id = ImageType::where("name", 'regular_image')->pluck('id')->first();
        $device_id = ProfileProgress::where("device_id", $ids["device_id"])->pluck('id')->first();
        $path = 'public/headshots/'; // Adjust the storage path
        $file = $data;
        $fileUrl = $this->file($file, $path);
        $pic = Headshots::create([
            'device_id' => $device_id,
            'type_id' => $type_id,
            'url' => $fileUrl, // Store the file path, not the URL
        ]);
        return $pic->id;
    }
    public function primaryHeadshot($data)
    {
        $id = ImageType::where("name", "primary_image")->pluck('id')->first();
        $image = Headshots::where("id", $data['headshot_id'])->first();
        $image->type_id = $id;
        $image->Save();
    }
    public function update()
    {
    }
    public function delete()
    {
    }
};
