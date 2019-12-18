<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use JWTFactory;
use JWTAuth;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use Response;
use Illuminate\Support\Facades\Auth;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthController extends Controller
{
    //
//    public function __construct()
//    {
//        $this->middleware('auth:api', ['except' => ['login']]);
//    }

    public function getUserRoles() {
        try {
//            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $roles = Role::all();
//            foreach ($roles as $role) {
//                $role_names[] = $role['name'];
//            }
            return response()->json(['data'=>$roles], 200);
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }


    public function registerUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'name' => 'required|string|max:255|unique:users',
            'password' => 'required',
            'firstName' => 'required',
            'lastName' => 'required',
            'phone' => 'required',
            'street' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 500);
        }

        try {
            $role=Role::findByName($request->get('role'));
            try {
                $user = User::create([
                    'avatar' => $request->get('avatar'),
                    'name' => $request->get('name'),
                    'email' => $request->get('email'),
                    'password' => bcrypt($request->get('password')),
                    'firstName' => $request->get('firstName'),
                    'lastName' => $request->get('lastName'),
                    'agency_name' => $request->get('agency_name'),
                    'phone' => $request->get('phone'),
                    'street' => $request->get('street'),
                    'apt_unit' => $request->get('apt_unit'),
                    'city' => $request->get('city'),
                    'state' => $request->get('state'),
                    'zip_code' => $request->get('zip_code'),
                    'active_status' => $request->get('role')=='User' ? 1 : 0,
                ]);
                $user->assignRole($role);
                $token = JWTAuth::fromUser($user);
                return Response::json(['result' => 'success', 'user' => $user, 'token' => $token], 200);
            } catch (JWTException $e) {
                return Response::json(['error' => 'This email is already registered'], 500);
            }
        } catch (JWTException $e) {
            return Response::json(['error' => $e->getMessage()], 404);
        }
    }

    public function setUserRole(Request $request)
    {
//        $request = $request->all();
        $email=$request['email'];
        $userRole=$request['userRole'];
        $user = User::where('email',$email)->first();
        if ($user == null) {
            return response()->json(['error'=>'can\'t find user with this email'], 500);
        }
//        $role = $request->get('userRole');
        $role = Role::findByName($userRole);
        if ($role == null) {
            return response()->json(['error'=>'can\'t find this user role'], 500);
        }
        $user->assignRole($role);
        return response()->json(['result'=>'success']);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string|max:255',
            'password'=> 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $loginField = $request['login'];
        $credentials = null;
        $loginType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        request()->merge([$loginType => $loginField]);
        $credentials = $request->only($loginType, 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid Email / Username or password!'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Couldn\'t create token'], 500);
        }
        if (Auth::validate($credentials))
        {
            $user = Auth::getUser();
        }

        if (!$user->active_status) {
            return response()->json(['error'=>'Your Account is Deactivated, Contact Admin'], 409);
        }

        $roles = array();
        foreach ($user->roles as $role){
            $roles[] = $role->name;
        }
        $user->roleNames = $roles;
//        $token = JWTAuth::fromUser($user);
        return response()->json(['token'=>$token, 'user'=>$user]);
    }

    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }



    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}
