<?php

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/convert', function(Request $request){
    // Validate the incoming file. Refuses anything bigger than 2048 kilobyes (=2MB)
    $request->validate([
        'skel_upload' => 'required',
        'atlas_upload' => 'required',
        'png_upload' => 'required',
        'frames' => 'required',
    ]);
    $folder_name = md5(Carbon::now()->toString());
    File::makeDirectory(public_path("/uploads/$folder_name"));

    // Store the file in storage\app\public folder
    $skel = $request->file('skel_upload');
    $atlas = $request->file('atlas_upload');
    $png = $request->file('png_upload');
    $frames = (int) $request->get('frames');


    $skel_name = $skel->getClientOriginalName();
    $skel_content = $skel->get();
    $skel_path = "/uploads/$folder_name/$skel_name";
    File::put(public_path($skel_path), $skel_content);

    $atlas_name = $atlas->getClientOriginalName();
    $atlas_content = $atlas->get();
    $atlas_path = "/uploads/$folder_name/$atlas_name";
    File::put(public_path($atlas_path), $atlas_content);

    $png_name = $png->getClientOriginalName();
    $png_content = $png->get();
    $png_path = "/uploads/$folder_name/$png_name";
    File::put(public_path($png_path), $png_content);

    return response()->view('converter', [
        'frames' => $frames,
        'asset_path' => asset($skel_path),
        'has_animation' => true,
        'scale' => $request->get('scale', 0.2),
        'height' => $request->get('height', 1200),
        'width' => $request->get('width', 400),
    ]);
})->name('convert');
