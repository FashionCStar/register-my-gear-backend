<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;

class UploadImageController extends Controller
{
  //
  public function index()
  {
//        $url = 'https://s3.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/' . env('AWS_BUCKET') . '/';
    $url = env('AWS_URL');
    $images = [];
    $files = Storage::disk('s3')->files('images');
    foreach ($files as $file) {
      $images[] = [
        'name' => str_replace('images/', '', $file),
        'src' => $url . $file
      ];
    }

    return view('welcome', compact('images'));
  }

  public function fileListUpload(Request $request)
  {
    $photo_urls = (object)[];

    if ($request->hasFile('file1')) {
      $file = $request->file('file1');
      $name = time() . $file->getClientOriginalName();
      $filePath = $name;
      Storage::disk('s3')->put($filePath, file_get_contents($file));
      $photo_urls->property_photo1 = Storage::disk('s3')->url($name);
    }
    if ($request->hasFile('file2')) {
      $file = $request->file('file2');
      $name = time() . $file->getClientOriginalName();
      $filePath = $name;
      Storage::disk('s3')->put($filePath, file_get_contents($file));
//            array_push($photo_urls, ['property_photo2'=>Storage::disk('s3')->url($name)]);
      $photo_urls->property_photo2 = Storage::disk('s3')->url($name);
    }
    if ($request->hasFile('file3')) {
      $file = $request->file('file3');
      $name = time() . $file->getClientOriginalName();
      $filePath = $name;
      Storage::disk('s3')->put($filePath, file_get_contents($file));
//            array_push($photo_urls, ['property_photo3'=>Storage::disk('s3')->url($name)]);
      $photo_urls->property_photo3 = Storage::disk('s3')->url($name);
    }
    return response()->json(['success' => 'Images Uploaded Successfully!!!', 'photo_urls' => $photo_urls], 200);
//        return $photo_urls;
//        return Storage::disk('s3')->allDirectories();
  }

  public function fileUpload(Request $request)
  {
    $this->validate($request, [
      'avatar' => 'required|image|max:2048',
    ]);
    if ($request->hasFile('avatar')) {
      $file = $request->file('avatar');
      $name = time() . $file->getClientOriginalName();
      $filePath = $name;
      Storage::disk('s3')->put($filePath, file_get_contents($file));
      $avatar_url = Storage::disk('s3')->url($name);
    }
    return response()->json(['success' => 'Avatar Uploaded Successfully!!!', 'avatar_url' => $avatar_url], 200);
  }

  public function destroy($image)
  {
    Storage::disk('s3')->delete('images/' . $image);

    return back()->withSuccess('Image was deleted successfully');
  }
}
