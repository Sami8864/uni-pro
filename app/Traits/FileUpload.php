<?php

namespace App\Traits;

use App\Models\ActorReel;
use App\Models\Headshots;
use Illuminate\Support\Str;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

trait FileUpload
{
    public static $imageMimes = [
        'image/png',
        'image/jpeg',
        'image/jpg',
        'image/gif',
        'image/bmp',
        'image/webp',
        'image/PNG',
        'image/JPEG',
        'image/JPG',
        'image/GIF',
        'image/BMP',
        'image/WEBP'
    ];
    public static $videoMimes = [
        'video/mp4',
        'video/avi',
        'video/mov',
        'video/mkv',
        'video/MP4',
        'video/AVI',
        'video/MOV',
        'video/MKV'
    ];


    public static function file($file, $path)
    {
        // Handle image or video based on MIME type
        $mimeType = $file->getMimeType();
        if (in_array($mimeType, self::$imageMimes)) {
            return self::handleImage($file, $path);
        } elseif (in_array($mimeType, self::$videoMimes)) {
            return self::handleVideo($file, $path);
        }

        // Handle other file types or throw an exception as needed
    }
    protected static function handleImage($file, $path)
    {
        $uploadedImage = $file;
        // Generate a unique image name based on the original name
        $imageName = time() . '_' . $uploadedImage->getClientOriginalName();
        // Read the contents of the image file
        $imageData = file_get_contents($uploadedImage->getRealPath());
        // Determine the file extension dynamically
        $fileExtension = $uploadedImage->getClientOriginalExtension();
        // Store binary data in storage

        $imagePath = $path . uniqid() . '.' . $fileExtension;
        Storage::put($imagePath, $imageData);
        // Build the URL for the stored image
        $imageUrl = asset(str_replace('public', 'storage',  $imagePath));

        return $imageUrl;
    }

    protected static function handleVideo($file)
    {
        // Generate a unique video name based on the current timestamp and original name
        $videoName = time() . '_' . $file->getClientOriginalName();

        // Store the video file in storage/app/public/advertisements
        // Use the putFileAs method to specify the file name
        $videoPath = $file->storeAs($path, $videoName, 'public');

        // Build the URL for the stored video
        $videoUrl = asset('storage/' . $videoPath);

        $path = Storage::disk('spaces')->put($videoUrl, file_get_contents($file));
        $path = Storage::disk('spaces')->url($videoUrl);
        // Return the path or URL, depending on your needs
        return  $videoPath;
    }
    public static function deleteAndUpload($upload, $path, $delete)
    {
        $filename = self::file($upload, $path);
        self::delete($path, $delete);

        return $filename;
    }

    public static function handleImage2($file, $path, $device_id, $type_id)
    {

        // Generate a unique image name based on the original name
        $imageName = time() . '_' . $file->getClientOriginalName();
        // Read the contents of the image file
        $imageData = file_get_contents($file->getRealPath());
        // Determine the file extension dynamically
        $fileExtension = $file->getClientOriginalExtension();
        // Store binary data in storage
        $imagePath = $path . uniqid() . '.' . $fileExtension;
        Storage::put($imagePath, $imageData);

        // Build the URL for the stored image
        $imageUrl = asset(str_replace('public', 'storage', $imagePath));

        // Save image details to the database
        $uid_id = json_decode($device_id);

        $head = Headshots::create([
            'device_id' => $uid_id,
            'type_id' => $type_id,
            'url' => $imageUrl,
        ]);

        return $head;
    }

    public static function handleVideoForDigitalOceanStorage($file, $path)

    {
        $filename = (time() + random_int(100, 1000));
        $extension = $file->getClientOriginalExtension();
        $filename = $filename . '.' . $extension;
        $filePath = $path . $filename;
        $path = Storage::disk('spaces')->put($filePath, file_get_contents($file));
        $path = Storage::disk('spaces')->url($filePath);
        $thumbnailPath = 'thumbnails/' . pathinfo($filePath, PATHINFO_FILENAME) . '.png';
        $thumbnailPathUrl = Storage::disk('spaces')->url($thumbnailPath);
        FFMpeg::fromDisk('spaces')
            ->open($filePath)
            ->getFrameFromSeconds(2)
            ->export()
            ->toDisk('spaces')
            ->save($thumbnailPath);
        $newRecord = ActorReel::create([
            'user_id' => auth()->user()->id,
            'reel' => $path,
            'thumbnail' =>  $thumbnailPathUrl,
        ]);
        return $newRecord;
    }

    public static function handleImagefordigitaloceanstorage($file, $path, $device_id, $type_id)
    {
        $filename = (time() + random_int(100, 1000));
        $extension = $file->getClientOriginalExtension();
        $filename = $filename . '.' . $extension;
        $filePath = $path . $filename;
        $path = Storage::disk('spaces')->put($filePath, file_get_contents($file));
        $path = Storage::disk('spaces')->url($filePath);
        // // Generate a unique image name based on the original name
        // $imageName = time() . '_' . $file->getClientOriginalName();
        // // Read the contents of the image file
        // $imageData = file_get_contents($file->getRealPath());
        // // Determine the file extension dynamically
        // $fileExtension = $file->getClientOriginalExtension();
        // // Store binary data in storage
        // $imagePath = $path . uniqid() . '.' . $fileExtension;
        // Storage::put($imagePath, $imageData);

        // // Build the URL for the stored image
        // $imageUrl = asset(str_replace('public', 'storage', $imagePath));

        // Save image details to the database
        $uid_id = json_decode($device_id);

        $head = Headshots::create([
            'device_id' => $uid_id,
            'type_id' => $type_id,
            'url' => $path,
        ]);

        return $head;
    }
    public static function imageUpload($file, $path)
    {
        $filename = (time() + random_int(100, 1000));
        $extension = $file->getClientOriginalExtension();
        $filename = $filename . '.' . $extension;
        $filePath = $path . $filename;
        $path = Storage::disk('spaces')->put($filePath, file_get_contents($file));
        $path = Storage::disk('spaces')->url($filePath);
        return $path;
    }
}
