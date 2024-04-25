<?php

namespace App\Http\Controllers;

use Imagick;
use App\Models\UserDetail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    public function generateQrCode(Request $request)
    {
        $data=$request->all();
        $validator=Validator::make($data,[
        'user_id'=>'required|exists:user_details,id'
        ]);
        if($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
        }

        // You can customize the data in the QR code (e.g., URL, text, etc.)
        $user=UserDetail::where('id',$data['user_id'])->first();
        $file= $user->id;

        // Generate the QR code
        $qrCode = QrCode::size(300)->generate($file);
        $code=$this->uploadSvgToStorage(   $qrCode  , $user->name);
        return response()->json(['code'=>$code],200);

    }

    private function uploadSvgToStorage($svg, $userName)
{
    // Create a folder for QRCODES if it doesn't exist
    $qrcodeFolder = 'public/QR-Codes';
    if (!Storage::exists($qrcodeFolder)) {
        Storage::makeDirectory($qrcodeFolder);
    }

    // Create a folder for the user's SVGs if it doesn't exist
    $userFolder = $qrcodeFolder . '/' . $userName;
    if (!Storage::exists($userFolder)) {
        Storage::makeDirectory($userFolder);
    }

    // Generate a unique image name based on the original name
    $imageName = time() . '_' . Str::random(10) . '.svg'; // You can adjust this as needed

    // Store the SVG data in the user's folder
    $imagePath = $userFolder . '/' . $imageName;
    Storage::put($imagePath, $svg);

    // Get the URL without the full domain
    $imageUrl = Storage::url($imagePath);
    // Return the relative path
    return $imageUrl;
}
}
