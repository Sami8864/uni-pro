<?php

namespace App\Http\Controllers\api;

use App\Models\FilmMaker;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreFilmMakerRequest;
use App\Http\Requests\UpdateFilmMakerRequest;

use Illuminate\Http\Request;
use App\Models\User;
class FilmMakerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function getFimakerProfile()
    {
        try {
            $user = Auth::user();
            $data = FilmMaker::where('user_id',$user->id )->first();
            $data->getdata();

            return response()->json(['code' => 200, 'data' => $data->getdata(), 'message' => 'profile has been Fetched successfully']);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFilmMakerRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(FilmMaker $filmMaker)
    {
        //
    }

    public function deleteFilmAccount(Request $request)
    {
        $user = User::where('id',$request->user_id)->first();

        $filmmaker = FilmMaker::where('user_id', $user->user_id)->first();

        if (!$filmmaker) {
            $user->forceDelete();
            return response()->json(['message' => 'Filmmaker Deleted'], 200);
        }

        // Delete the associated User (if the relationship is set up with onDelete('cascade'))
        $user = $filmmaker->user;
        if ($user) {
            $user->delete();
        }

        // Delete the Filmmaker
        $filmmaker->delete();

        return response()->json(['message' => 'Filmmaker account and associated User account deleted successfully']);
    }
    public function deleteAccount()
    {
        $user = Auth::user();

        $filmmaker = FilmMaker::where('user_id', $user->id )->first();

        if (!$filmmaker) {
            return response()->json(['message' => 'Filmmaker not found'], 404);
        }

        // Delete the associated User (if the relationship is set up with onDelete('cascade'))
        $user = $filmmaker->user;
        if ($user) {
            $user->delete();
        }

        // Delete the Filmmaker
        $filmmaker->delete();

        return response()->json(['message' => 'Filmmaker account and associated User account deleted successfully']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFilmMakerRequest $request, FilmMaker $filmMaker)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FilmMaker $filmMaker)
    {
        //
    }
}
