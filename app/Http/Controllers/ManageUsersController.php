<?php

namespace App\Http\Controllers;

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

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Input;



class ManageUsersController extends Controller
{
    //
    public function createUser(Request $request)
    {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
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
                    'active_status' => 1,
                ]);

                $role_id = $request->get('role_id'); // get  Roles from post request

                $role = Role::find($role_id);
                $user->roles()->attach($role);

                $token = JWTAuth::fromUser($user);

                return Response::json(['result' => 'success', 'user' => $user, 'token' => $token], 200);
            } catch (JWTException $e) {
                return Response::json(['error' => 'This email is already registered'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function updateUser(Request $request)
    {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
                'name' => 'required',
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
                return response()->json($validator->errors());
            }

            $user = User::find($request->get('id'));
            if ($user->email == $request->get('email')) {
                $user->update([
                    'avatar' => $request->get('avatar'),
                    'name' => $request->get('name'),
                    'password' => bcrypt($request->get('password')),
                    'firstName' => $request->get('firstName'),
                    'lastName' => $request->get('lastName'),
                    'phone' => $request->get('phone'),
                    'agency_name' => $request->get('agency_name'),
                    'street' => $request->get('street'),
                    'apt_unit' => $request->get('apt_unit'),
                    'city' => $request->get('city'),
                    'state' => $request->get('state'),
                    'zip_code' => $request->get('zip_code'),
                ]);
            } else {
                $user->update([
                    'avatar' => $request->get('avatar'),
                    'name' => $request->get('name'),
                    'email' => $request->get('email'),
                    'password' => bcrypt($request->get('password')),
                    'firstName' => $request->get('firstName'),
                    'lastName' => $request->get('lastName'),
                    'phone' => $request->get('phone'),
                    'agency_name' => $request->get('agency_name'),
                    'street' => $request->get('street'),
                    'apt_unit' => $request->get('apt_unit'),
                    'city' => $request->get('city'),
                    'state' => $request->get('state'),
                    'zip_code' => $request->get('zip_code'),
                ]);
            }

            DB::table('model_has_roles')->where('model_id', $request->get('id'))->delete();
            $role_id = $request->get('role_id');
            $role = Role::find($role_id);
            $user->assignRole($role);

            $token = JWTAuth::fromUser($user);

            return Response::json(['result' => 'User Successfully Updated', 'user' => $user, 'token' => $token], 200);
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function activeUser(Request $request) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $user = User::find($request->get('id'));
            $user->update(['active_status' => $request->get('active_status')]);

            $user = User::first();
            $token = JWTAuth::fromUser($user);

            return response()->json(['data' => 'User Successfully Changed', 'token' => $token], 201);
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function deleteUser(Request $request) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $user = User::find($request->get('id'));
            $user->syncRoles([]);
            try {
                $user->delete();
                return response()->json(['success' => 'User Successfully Removed'], 200);
            } catch (\Exception $e) {
                return response()->json(['error' => 'User Remove Failed'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function getRoleNames() {
        try {
//            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $roles = Role::all();
            foreach ($roles as $role) {
                $role_names[] = $role['name'];
            }
            return $roles;
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function getUserRoles($user) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $roles = $user->getRoleNames();
//            $roles = DB::table('roles')->select('roles.*')->leftJoin('model_has_roles', 'model_has_roles.role_id', '=', 'roles.id')
//                ->where('model_has_roles.model_id', $user->id)->get();
            return $roles;
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function getUsers() {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $page = Input::get('pageNo') != "null" ? Input::get('pageNo') : 1;
            $limit = Input::get('numPerPage') != "null" ? Input::get('numPerPage') : 10;
            $role_name = Input::get('rolename') != "null" ? Input::get('rolename') : "null";
            $search = Input::get('search') != "null" ? Input::get('search') : "null";

            if ($role_name=="null") {
                if ($search == "null") {
                    $totalCount = count(User::all());
                    $users = User::orderBy('updated_at', 'desc')->skip(($page - 1) * $limit)->take($limit)->get();
                } else {
                    $totalCount = count(User::where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')->get());
                    $users = User::where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')->orderBy('updated_at', 'desc')->skip(($page - 1) * $limit)->take($limit)->get();
                }
            } else {
                $role = Role::findByName($role_name);
                if ($search == "null") {
                    $totalCount = count(User::role($role)->get());
                    $users = User::role($role)->skip(($page - 1) * $limit)->take($limit)->get();
                } else {
                    $totalCount = count(User::role($role)
                        ->where(function($q) use ($search) {
                            $q->where('users.name', 'LIKE', '%' . $search . '%')
                                ->orWhere('users.email', 'LIKE', '%' . $search . '%');
                        })->get());
                    $users = User::role($role)
                        ->where(function($q) use ($search) {
                        $q->where('users.name', 'LIKE', '%' . $search . '%')
                            ->orWhere('users.email', 'LIKE', '%' . $search . '%');
                        })
                        ->skip(($page - 1) * $limit)->take($limit)->get();

//                    $users = DB::table('users')->select('users.*')
//                        ->leftJoin('model_has_roles', 'model_has_roles.model_id','=','users.id')
//                        ->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id')
//                        ->where('model_has_roles.role_id', $role->id)
//                        ->where(function($q) use ($search) {
//                            $q->where('users.name', 'LIKE', '%' . $search . '%')
//                            ->orWhere('users.email', 'LIKE', '%' . $search . '%');
//                        })
//                        ->skip(($page - 1) * $limit)->take($limit)->get();
                }
            }
            if ($totalCount == 0) {
                return response()->json(['totalCount'=>$totalCount, 'userdata'=>[]], 200);
            } else {
                foreach ($users as $user) {
                    $roles = $this->getUserRoles($user);
                    $user->roles = $roles;
                    $userdatas[] = $user;
                }
                return response()->json(['totalCount'=>$totalCount, 'userdata'=>$userdatas], 200);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function getAllUsers() {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());

            $users = User::orderBy('updated_at', 'desc')->get();
            return response()->json(['all_users'=>$users], 200);

        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function getUserByID() {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $userId = Input::get('userId') != null ? Input::get('userId') : 1;
            $user = User::where('id', $userId)->first();
            $datas = [];
            if ($user != null) {
                $role_id = DB::table('model_has_roles')->where('model_id', $userId)->first();
                if ($role_id != null) {
                    $user->role_id = [];
//                    foreach ($role_ids as $role_id) {
//                        array_push($datas, $role_id->role_id);
//                    }
                    $user->role_id = $role_id->role_id;
                } else {
                    $user->roles = [];
                }
                return response()->json(['userdata' => $user], 200);
            } else {
                return response()->json(['error' => 'No user with this id: ' . $userId], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function addPermission(Request $request) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            try {
                $permission = Permission::create(['name' => $request->get('name')]);
                return response()->json(['result' => 'Permission Successfully Created!', 'permission' => $permission], 200);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Permission Already Exist!'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function updatePermission(Request $request) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $permission = DB::table('permissions')->where('id', $request['id'])->update(['name' => $request['name']]);
            return response()->json(['result' => 'Permission Successfully Updated!'], 200);
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function deletePermission(Request $request) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $permission = DB::table('permissions')->where('id', $request['id'])->delete();
            if ($permission) {
                return response()->json(['result' => 'Permission Successfully Deleted!'], 200);
            }
            else {
                return response()->json(['result' => 'Current Permission is not exist!'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function getPermissions(Request $request) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $page = $request->filled('pageNo') ? $request->get('pageNo') : 1;
            $limit = $request->filled('numPerPage') ? $request->get('numPerPage') : 10;
            $permissions = Permission::orderBy('updated_at', 'desc')->skip(($page-1)*$limit)->take($limit)->get();
            $totalCount = Permission::count();
            return response()->json(['total '=>$totalCount, 'data'=>$permissions], 200);;
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function addRole(Request $request) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            try {
                $role = Role::Create(['name' => $request->get('name')]);
                $permission_ids = $request['permission_ids'];
                foreach ($permission_ids as $permission_id) {
                    $permission = Permission::findById($permission_id);
                    $role->givePermissionTo($permission);
                }
                return response()->json(['result' => 'Successfully Created Role with Permissions'], 200);
            } catch (JWTException $e) {
                return response()->json(['error' => 'Already Created This Role'], 500);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function getRoleByID() {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $roleId = Input::get('roleId') != null ? Input::get('roleId') : 1;
            $role = Role::where('id', $roleId)->first();
            $datas = [];
            if ($role != null) {
                $permission_ids = DB::table('role_has_permissions')->where('role_id', $roleId)->get();
                if ($permission_ids != null) {
                    $role->permission_ids = [];
                    foreach ($permission_ids as $permission_id) {
                        array_push($datas, $permission_id->permission_id);
                    }
                    $role->permission_ids = $datas;
                } else {
                    $role->permission_ids = [];
                }
                return response()->json(['roledata' => $role], 200);
            } else {
                return response()->json(['error' => 'No user with this id: ' . $roleId], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function updateRole(Request $request) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $cur_role = Role::findById($request['id']);
            if ($request['role_name'] != $cur_role->name) {
                DB::table('roles')->where('id', $request['id'])->update(['name' => $request['name']]);
            }
            $role = Role::findById($request['id']);
            DB::table('role_has_permissions')->where('role_id', $request['id'])->delete();
            $permission_ids = ($request['permission_ids']);
            foreach ($permission_ids as $permission_id) {
                $permission = Permission::findById($permission_id);
                $role->givePermissionTo($permission);
                $permission->assignRole($role);
            }
            return response()->json(['result' => 'Successfully Updated Role with Permissions'], 200);
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function deleteRole(Request $request) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            DB::table('role_has_permissions')->where('role_id', $request['id'])->delete();
            $role = DB::table('roles')->where('id', $request['id'])->delete();
            if ($role) {
                return response()->json(['result' => 'Successfully Removed Role with Permissions'], 200);
            } else {
                return response()->json(['error' => 'User not Found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }

    public function getRoles(Request $request) {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $page = $request->filled('pageNo') ? $request->get('pageNo') : 1;
            $limit = $request->filled('numPerPage') ? $request->get('numPerPage') : 20;
            $totalCount = Role::count();
            $roles = Role::orderBy('updated_at', 'desc')->skip(($page-1)*$limit)->take($limit)->get();

            if ($roles != null) {
                $roledatas = [];
                foreach ($roles as $role) {
                    $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
                        ->where("role_has_permissions.role_id",$role->id)
                        ->get();
                    $role->permissions = $rolePermissions;
                    $roledatas[] = $role;
                }
                return response()->json(['totalCount'=>$totalCount, 'data'=> $roledatas], 200);
            } else {
                return response()->json(['error'=>'No Role is created!'], 500);
            }

        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }
    }
}
