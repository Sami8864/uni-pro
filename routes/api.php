<?php



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\SearchController;
use App\Http\Controllers\api\FilmMakerController;
use App\Http\Controllers\api\HeadshotsController;
use App\Http\Controllers\api\SavedFeedController;
use App\Http\Controllers\api\InitialQuestionnaire;
use App\Http\Controllers\api\InvitationController;
use App\Http\Controllers\api\UserDetailController;
use App\Http\Controllers\api\AchievementController;
use App\Http\Controllers\api\ActivityPointController;
use App\Http\Controllers\api\ConversationController;
use App\Http\Controllers\api\NotificationController;
use App\Http\Controllers\api\AdvertisementController;
use App\Http\Controllers\api\ProfileProgressController;
use App\Http\Controllers\api\PointsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/




Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/actor/profile/details', [UserDetailController::class, 'getActorProfile']);
Route::post('/search', [SearchController::class, 'search']);
Route::get('/unions', [AuthController::class, 'unions']);
Route::get('/anonymous/user', [UserDetailController::class, 'makeUser']);
Route::post('/answer', [InitialQuestionnaire::class, 'Answer']);
Route::post('/similar-Face', [InitialQuestionnaire::class, 'detectFace']);
Route::post('/attributes', [UserDetailController::class, 'getAttributes']);
Route::post('/update-ai', [UserDetailController::class, 'updateAiAttributes']);
Route::post('/user-detail', [UserDetailController::class, 'store']);
Route::post('/invite/actor', [InvitationController::class, 'create']); // code is in correct
Route::post('/invited/actor', [InvitationController::class, 'invitedActor']); // code is in correct
Route::post('/invite/link', [InvitationController::class, 'linkGenerate']);
Route::get('/user-feed', [HeadshotsController::class, 'showFeed']);
Route::post('/share-feed', [HeadshotsController::class, 'shareFeed']);
Route::get('/get-users', [UserDetailController::class, 'load']);
Route::get('/get-essence', [UserDetailController::class, 'essence']);
Route::post('/headshot-data', [UserDetailController::class, 'detailThroughHeadshot']);
Route::get('/get-type', [UserDetailController::class, 'type']);
Route::get('/get-physique', [UserDetailController::class, 'physique']);
Route::get('/generate-barcode', [UserDetailController::class, 'generateBarcodeId']);
Route::get('/flags', [HeadshotsController::class, 'getFlags']);
Route::post('/add-flag', [UserDetailController::class, 'AddFlag']);
Route::post('/get-profile', [UserDetailController::class, 'getProfileProgress']);
Route::get('/generate-qr-code', [QrCodeController::class, 'generateQrCode']);
Route::post('/req', [InitialQuestionnaire::class, 'sendRequest']);
Route::get('/question', [InitialQuestionnaire::class, 'firstQuestion']);
Route::post('/user/battery', [UserDetailController::class, 'userBattery']);
Route::post('/add-question', [InitialQuestionnaire::class, 'store']);
Route::post('/reupload-image', [InitialQuestionnaire::class, 'reUpload']);
Route::post('/analyze-Face', [InitialQuestionnaire::class, 'analyzeFace']);
Route::post('/primary-headshots', [InitialQuestionnaire::class, 'primaryHeadshot']);
Route::post('/profession', [InitialQuestionnaire::class, 'sendRequest']);
Route::post('/detect-bad-image', [HeadshotsController::class, 'badImageDetect']);
Route::post('/battery-level', [ProfileProgressController::class, 'getBatteryLevel']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/getAchievements',[AchievementController::class,'load']);


// Route::post('/add-advertisement', [AdvertisementController::class, 'upload']);
// Route::get('/show-advertisement', [AdvertisementController::class, 'show']);
// Route::post('/delete-advertisement', [AdvertisementController::class, 'delete']);
// Route::post('/update-advertisement', [AdvertisementController::class, 'update']);

Route::middleware('auth:sanctum')->post('/change-password', [AuthController::class, 'changePassword']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('/actor/search-name', [SearchController::class, 'searchByName']);
    Route::post('/actor/saved/search-name', [SearchController::class, 'searchBySavedName']);
    // Chat moudule  Api's
    Route::post('getChannelId', [ConversationController::class, 'getChannelId']);
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/send/message/{id}', [ConversationController::class, 'sendMessageByChannelId']);
    Route::get('/conversation/{id}', [ConversationController::class, 'messages']);
    Route::get('/conversation/delete/{id}', [ConversationController::class, 'delete_conversation']);
    Route::get('/users', [ConversationController::class, 'allUsers']);
    Route::get('/chat/mute/{conversation}', [ConversationController::class, 'mute']);
    Route::get('/chat/unmute/{conversation}', [ConversationController::class, 'unmute']);
    Route::get('/message/delete/{message}', [ConversationController::class, 'delete_message']);
    Route::get('/chat/delete/{message}', [ConversationController::class, 'delete_chat']);
    Route::get('/chat/block/{conversation}', [ConversationController::class, 'block_chat']);
    Route::get('/chat/unblock/{conversation}', [ConversationController::class, 'unblock_chat']);
    Route::get('/conversation/{id}/{last_message_id}', [ConversationController::class, 'prev_messages']);
    // Upload Video Api
    Route::post('/upload-reel', [UserDetailController::class, 'addReel']);
    Route::post('/profile', [AuthController::class, 'Profile']);
    Route::post('/updateActorProfile', [UserDetailController::class, 'updateUser']);
    // Ad Social Medioa links
    Route::post('/add-link', [UserDetailController::class, 'addLinks']);
    // Actor profile Api
    Route::post('/headshot', [InitialQuestionnaire::class, 'uploadHeadshot']);
    Route::post('/balance', [InitialQuestionnaire::class, 'ensureBalance']); // code in correct
    Route::post('/actorProfile', [UserDetailController::class, 'getProfile']);
    Route::get('/actorMedia', [UserDetailController::class, 'getUserMedia']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::get('/points-activity', [UserDetailController::class, 'pointsActivity']);
    Route::post('/delete-account', [AuthController::class, 'deleteAccount']);
    Route::post('/save-feed', [SavedFeedController::class, 'savePost']);
    Route::get('/saved-feeds', [SavedFeedController::class, 'getSavedPosts']);
    Route::post('/delete-feed', [SavedFeedController::class, 'deletFeed']);
    Route::get('/get-all-profiles', [UserDetailController::class, 'getAllProfile']);
    Route::get('/earnings', [UserDetailController::class, 'userEarnings']);
});
Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'achievements'], function () {
    Route::apiResource('/', AchievementController::class);
    Route::get('/point', [AchievementController::class, 'trackPointsAndAwardAchievements']);
    Route::get('/get-point', [AchievementController::class, 'getProfileProgressAchievements']);
    Route::post('/share', [AchievementController::class, 'shareAchievement']);
    Route::post('/deleteAch', [AchievementController::class, 'destroy']);
    Route::post('/updateAch', [AchievementController::class, 'update']);
    Route::post('/addAch', [AchievementController::class, 'store']);
});
Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'activitypoints'], function () {
    Route::post('/', [ActivityPointController::class, 'store']);
    Route::get('/', [ActivityPointController::class, 'index']);
    Route::get('/points', [ActivityPointController::class, 'load']);
    Route::post('/update', [ActivityPointController::class, 'update']);
    Route::post('/purchase', [ActivityPointController::class, 'purchase']);
});

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'admin'], function () {
    Route::post('/points', [PointsController::class, 'update']);
    Route::get('/points', [PointsController::class, 'load']);
    Route::get('/filmmakers', [UserDetailController::class, 'filmmakers']);
    Route::get('/performers', [UserDetailController::class, 'performers']);
    Route::get('/progress', [PointsController::class, 'progress']);
    Route::post('/flag', [PointsController::class, 'flag']);
});
// Soft delete Account
Route::post('/save-token', [NotificationController::class, 'saveToken']);
Route::get('/notifications', [NotificationController::class, 'list']);
Route::put('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead']);
Route::get('/send-notification', [NotificationController::class, 'sendNotification'])->name('send.notification');
Route::post('/recover-account', [AuthController::class, 'restoreAccount']);
Route::post('/permanent-delete-account', [AuthController::class, 'permanentDeleteAccount']);
Route::post('/permanent-delete-user-account', [AuthController::class, 'DeleteUserAccount']);
Route::post('/delete-filmmaker-account', [FilmMakerController::class, 'deleteFilmAccount']);

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'filmmaker'], function () {
    Route::post('/delete-account', [FilmMakerController::class, 'deleteAccount']);
    Route::get('/profile', [FilmMakerController::class, 'getFimakerProfile']);
    Route::post('/updaProfileImage', [AuthController::class, 'updaProfileImage']);
    Route::get('/delete/cover/image', [AuthController::class, 'deleteCoverImage']);
    Route::post('/updacoverImage', [AuthController::class, 'updateCoverImage']);
    Route::get('/delete/profile/image', [AuthController::class, 'deleteProfileImage']);
});
Route::any('{path}', function () {
    return response()->json([
        'message' => 'Route not found'
    ], 404);
})->where('path', '.*');
