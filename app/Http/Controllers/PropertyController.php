<?php

namespace App\Http\Controllers;

use App\Property;
use Illuminate\Http\Request;
use DB;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use Response;

use App\Http\Controllers\Controller;
use JWTFactory;
use JWTAuth;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Illuminate\Support\Facades\Input;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function fileUpload(Request $request) {
        $request->validate(['input_img' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        echo $request->get('input_img');

        if ($request->hasFile('input_img')) {
            $image = $request->file('input_img');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = 'public/assets/images';
            $image->move($destinationPath, $name);
            return response()->json(['success' => 'Image Uploaded Successfully!!!', 'image_url'=>$destinationPath.'/'.$name], 200);
        } else {
            return response()->json(['error' => "Image Upload error"], 500);
        }
    }

    public function addProperty(Request $request) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $validator = Validator::make($request->all(), [
                'serial_number' => 'required|string|max:255|unique:properties',
                'make' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 409);
            }

            try {
                $property = Property::create([
                    'user_id' => $current_user['id'],
                    'property_type' => $request->get('property_type'),
                    'make' => $request->get('make'),
                    'model' => $request->get('model'),
                    'property_photo1' => $request->get('property_photo1'),
                    'property_photo2' => $request->get('property_photo2'),
                    'property_photo3' => $request->get('property_photo3'),
                    'about' => $request->get('about'),
                    'serial_number' => $request->get('serial_number'),
                ]);
                return Response::json(['success' => 'Property is Successfully created', 'property' => $property], 200);
            } catch (JWTException $e) {
                return Response::json(['error' => 'This Property is already exist'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function changePropertyStatus(Request $request) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $property = Property::find($request->get('id'));
//            $property->update(['safe_status' => $request->get('safe_status')]);
            $property->safe_status = $request->get('safe_status');
            $property->save();

            return response()->json(['success' => 'Property Successfully Changed', 'property' => $property], 201);
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function updateProperty(Request $request) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $validator = Validator::make($request->all(), [
                'serial_number' => 'required|string|max:255',
                'make' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 500);
            }

            try {
                $property = Property::find($request->get('id'));
                if ($property['serial_number'] === $request->get('serial_number')) {
                    $property->update([
                        'user_id' => $current_user['id'],
                        'property_type' => $request->get('property_type'),
                        'make' => $request->get('make'),
                        'model' => $request->get('model'),
                        'property_photo1' => $request->get('property_photo1'),
                        'property_photo2' => $request->get('property_photo2'),
                        'property_photo3' => $request->get('property_photo3'),
                        'about' => $request->get('about'),
                    ]);
                } else {
                    $property->update([
                        'user_id' => $current_user['id'],
                        'property_type' => $request->get('property_type'),
                        'make' => $request->get('make'),
                        'model' => $request->get('model'),
                        'property_photo1' => $request->get('property_photo1'),
                        'property_photo2' => $request->get('property_photo2'),
                        'property_photo3' => $request->get('property_photo3'),
                        'about' => $request->get('about'),
                        'serial_number' => $request->get('serial_number'),
                    ]);
                }
                return Response::json(['success' => 'Property is Successfully updated', 'property' => $property], 200);
            } catch (JWTException $e) {
                return Response::json(['error' => 'This Property is already exist'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function deleteProperty(Request $request) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $property = Property::find($request->get('id'));
            try {
                $property->delete();
                return response()->json(['success' => 'Property Successfully Removed']);
            } catch (JWTException $e) {
                return response()->json(['error' => 'Property Remove Failed']);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function getProperties() {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $selectedUserId = Input::get('selectedUserId') != "null" ? Input::get('selectedUserId') : '';
            $page = Input::get('pageNo') != "null" ? Input::get('pageNo') : 0;
            $limit = Input::get('numPerPage') != "null" ? Input::get('numPerPage') : 10;
            $search = Input::get('search') != "null" ? Input::get('search') : "null";

            if ($selectedUserId == '') {
                if ($search != "null") {
                    $totalCount = count(Property::where('make', 'like', '%' . $search . '%')->orWhere('serial_number', 'like', '%' . $search . '%')->get());
                    $properties = Property::where('make', 'like', '%' . $search . '%')->orWhere('serial_number', 'like', '%' . $search . '%')->orderBy('updated_at', 'desc')->skip(($page - 1) * $limit)->take($limit)->get();
                } else {
                    $totalCount = count(Property::all());
                    $properties = Property::orderBy('updated_at', 'desc')->skip(($page - 1) * $limit)->take($limit)->get();
                }
            } else {
                if ($search != "null") {
                    $totalCount = count(Property::where('user_id','=', $selectedUserId)
                        ->where(function($q) use ($search) {
                            $q->where('make', 'LIKE', '%' . $search . '%')
                                ->orWhere('serial_number', 'LIKE', '%' . $search . '%');
                        })->get());
                    $properties = Property::where('user_id','=', $selectedUserId)
                        ->where(function($q) use ($search) {
                            $q->where('make', 'LIKE', '%' . $search . '%')
                                ->orWhere('serial_number', 'LIKE', '%' . $search . '%');
                        })
                        ->orderBy('updated_at', 'desc')->skip(($page - 1) * $limit)->take($limit)->get();
                } else {
                    $totalCount = count(Property::where('user_id','=', $selectedUserId)->get());
                    $properties = Property::where('user_id','=', $selectedUserId)->orderBy('updated_at', 'desc')->skip(($page - 1) * $limit)->take($limit)->get();
                }
            }
            if ($totalCount == 0) {
                return response()->json(['totalCount' => $totalCount, 'data' => []], 200);
            } else {
                return response()->json(['totalCount' => $totalCount, 'data' => $properties], 200);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function getPropertiesBySerial() {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $page = Input::get('pageNo') != "null" ? Input::get('pageNo') : 0;
            $limit = Input::get('numPerPage') != "null" ? Input::get('numPerPage') : 10;
//            $serial = Input::get('serial') != '' ? Input::get('serial') : "null";
            $serial = Input::get('serial');


            if ($serial != '') {
                $totalCount = count(Property::where('serial_number', 'like', '%' . $serial . '%')->get());
                $properties = Property::where('serial_number', 'like', '%' . $serial . '%')->orderBy('updated_at', 'desc')->skip(($page - 1) * $limit)->take($limit)->get();
            }
            else {
                $totalCount = 0;
                $properties = [];
            }

            if ($totalCount == 0) {
                return response()->json(['totalCount' => $totalCount, 'data' => []], 200);
            } else {
                return response()->json(['totalCount' => $totalCount, 'data' => $properties], 200);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function getPropertyById() {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $propertyID = Input::get('propertyId') != "null" ? Input::get('propertyId') : 1;
            try {
                $property = Property::find($propertyID);
                return response()->json(['result' => 'success', 'data' => $property], 200);
            } catch (JWTException $e) {
                return response()->json(['error' => 'Property with this id is not exist'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function getPropertyUserById() {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $propertyID = Input::get('propertyId') != "null" ? Input::get('propertyId') : 1;
            try {
                $property = Property::find($propertyID);
                try {
                    $user = User::find($property->user_id);
                    return response()->json(['result' => 'success', 'property' => $property, 'user'=>$user], 200);
                } catch (JWTException $e) {
                    return response()->json(['error' => 'User with this id is not exist'], 404);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => 'Property with this id is not exist'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function show(Property $property)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function edit(Property $property)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Property $property)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function destroy(Property $property)
    {
        //
    }
}
