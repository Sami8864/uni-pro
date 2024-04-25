<?php

namespace Database\Seeders;

use App\Models\InitialSurvey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SurveyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    use HasFactory;

    public function run(): void
    {
        $data = [
            [
                'path' => 'C:\Users\dell\Desktop\casttypesbackend-main\CastType-Backend\casttypesbackend\storage\app\public\media\alexis-chloe-TYDkKEgc0Fg-unsplash.jpg',
                'option' => 'White',
                'answer' => 'Black',
                'points' => 10,
            ],
            [
                'path' => 'C:\Users\dell\Desktop\casttypesbackend-main\CastType-Backend\casttypesbackend\storage\app\public\media\gabriel-silverio-u3WmDyKGsrY-unsplash.jpg',
                'option' => 'Age:40-45',
                'answer' => 'Age:20-25',
                'points' => 30,
            ], [

                'path' => 'C:\Users\dell\Desktop\casttypesbackend-main\CastType-Backend\casttypesbackend\storage\app\public\media\gettyimages-152071549-612x612.jpg',
                'option' => 'Adriana Grande',
                'answer' => 'Lella Joseph',
                'points' => 30,
            ], [
                'path' => 'C:\Users\dell\Desktop\casttypesbackend-main\CastType-Backend\casttypesbackend\storage\app\public\media\gettyimages-456759774-612x612.jpg',
                'option' => 'Black',
                'answer' => 'White',
                'points' => 50,
            ], [
                'path' => 'C:\Users\dell\Desktop\casttypesbackend-main\CastType-Backend\casttypesbackend\storage\app\public\media\gettyimages-627787380-612x612.jpg',
                'option' => 'White',
                'answer' => 'Brown',
                'points' => 10,
            ], [
                'path' => 'C:\Users\dell\Desktop\casttypesbackend-main\CastType-Backend\casttypesbackend\storage\app\public\media\joel-muniz-KodMXENNaas-unsplash.jpg',
                'option' => 'Single Photo',
                'answer' => 'Group Photo',
                'points' => 50,
            ], [
                'path' => 'storage\app\public\media\nora-hutton-tCJ44OIqceU-unsplash.jpg',
                'option' => 'Age:10-15',
                'answer' => 'Age:18-25',
                'points' => 10,
            ], [
                'path' => 'storage\app\public\media\tim-mossholder-hOF1bWoet_Q-unsplash.jpg',
                'option' => 'Not Smiling',
                'answer' => 'Smiling',
                'points' => 30,
            ]
            // Add more data entries as needed
        ];

        foreach ($data as $entry) {
            $this->createSurveyEntry($entry);
        }
    }

    private function createSurveyEntry($data)
    {
        $imageUrl = $this->uploadImage($data['path']);

        InitialSurvey::create([
            'option' => $data['option'],
            'answer' => $data['answer'],
            'image' => $imageUrl,
            'points' => $data['points'],
        ]);
    }

    private function uploadImage($path)
    {
        $uploadedImage = $path;
        // Generate a unique image name based on the original name
        $imageName = time() . '_' . basename($uploadedImage);
        // Read the contents of the image file
        $imageData = file_get_contents($uploadedImage);
        // Determine the file extension dynamically
        $fileExtension = pathinfo($uploadedImage, PATHINFO_EXTENSION);
        // Store binary data in storage
        $imagePath = 'public/survey/' . uniqid() . '.' . $fileExtension;
        Storage::put($imagePath, $imageData);
        // Get the URL without the full domain
        $imageUrl = Storage::url($imagePath);
        // Return the relative path
        return $imageUrl;
    }
}
