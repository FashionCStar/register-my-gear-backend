<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group([
    'middleware' => 'api',
], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('user/register', 'AuthController@registerUser');
    Route::get('user/getUserRoles', 'AuthController@getUserRoles');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('sendPasswordResetLink', 'ResetPasswordController@sendPasswordResetEmail');
    Route::post('changePassword', 'ResetPasswordController@resetPassword');
    Route::post('createUser', 'ManageUsersController@createUser');
    Route::post('updateUser', 'ManageUsersController@updateUser');
    Route::post('activeUser', 'ManageUsersController@activeUser');
    Route::post('deleteUser', 'ManageUsersController@deleteUser');
    Route::get('getRoleNames', 'ManageUsersController@getRoleNames');
    Route::get('getUsers', 'ManageUsersController@getUsers');
    Route::get('getAllUsers', 'ManageUsersController@getAllUsers');
    Route::get('getUser', 'ManageUsersController@getUserByID');
    Route::post('addPermission', 'ManageUsersController@addPermission');
    Route::post('updatePermission', 'ManageUsersController@updatePermission');
    Route::post('deletePermission', 'ManageUsersController@deletePermission');
    Route::get('getPermissions', 'ManageUsersController@getPermissions');
    Route::post('addRole', 'ManageUsersController@addRole');
    Route::post('updateRole', 'ManageUsersController@updateRole');
    Route::post('deleteRole', 'ManageUsersController@deleteRole');
    Route::get('getRoles', 'ManageUsersController@getRoles');
    Route::get('getRole', 'ManageUsersController@getRoleByID');
    Route::post('addProperty', 'PropertyController@addProperty');
    Route::post('updateProperty', 'PropertyController@updateProperty');
    Route::post('changePropertyStatus', 'PropertyController@changePropertyStatus');
    Route::post('deleteProperty', 'PropertyController@deleteProperty');
    Route::get('getProperties', 'PropertyController@getProperties');
    Route::get('getPropertiesBySerial', 'PropertyController@getPropertiesBySerial');
    Route::get('getPropertyById', 'PropertyController@getPropertyById');
    Route::get('getPropertyUserById', 'PropertyController@getPropertyUserById');
    Route::post('fileListUpload', 'UploadImageController@fileListUpload');
    Route::post('fileUpload', 'UploadImageController@fileUpload');
});