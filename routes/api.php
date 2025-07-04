<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ApiController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//AuthController
Route::post('register', [AuthController::class, 'register']);
Route::get('demoUsers', [ApiController::class, 'fillUsersToTwentyThousand']);







Route::post('login', [AuthController::class, 'login']);
Route::post('OAUTH2', [AuthController::class, 'OAUTH2']);
Route::post('checkIp', [ApiController::class, 'checkIp']);
Route::post('saveExcelData', [ApiController::class, 'saveExcelData']);
Route::post('/getUsersData', [ApiController::class, 'getUsersData']);
Route::post('/getStudentsData', [ApiController::class, 'getStudentsData']);
Route::post('/getColumns', [ApiController::class, 'getColumns']);
Route::get('/menu/{userId}', [ApiController::class, 'getSidebarByUser']);
Route::post('/menus/permissions', [ApiController::class, 'menusForSetPermission']);
Route::get('/getRoles/{adminId}/{branchId}', [ApiController::class, 'getRoles']);
Route::get('/states', [ApiController::class, 'getStates']);
Route::post('createRole', [ApiController::class, 'createRole'])->middleware('auth:sanctum');
Route::post('create/{roleId}', [ApiController::class, 'createUser'])->middleware('auth:sanctum');

Route::get('userLoggedIn', [AuthController::class, 'userLoggedIn'])->middleware('auth:sanctum');
Route::post('roleInputAssignment', [ApiController::class, 'roleInputAssignment'])->middleware('auth:sanctum');
Route::post('getRoleInputAssignments', [ApiController::class, 'getRoleInputAssignments'])->middleware('auth:sanctum');


Route::match(['get', 'post'], 'appData', [AuthController::class, 'appDataApi']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('formFields', [ApiController::class, 'getFormFields']);
// Route::post('firebaseMessageApi', [ApiController::class, 'firebaseMessageApi']);
Route::get('getFcmToken', [ApiController::class, 'getFcmToken']);

// // Branches resource routes with auth:sanctum middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::post('subscribePackage', [ApiController::class, 'subscribePackage']);
    Route::post('getMyReviews', [ApiController::class, 'getMyReviews']);
    Route::post('getMyTransactions', [ApiController::class, 'getMyTransactions']);
    Route::post('postDummyProperty', [ApiController::class, 'postDummyProperty']);
    Route::post('uploadPropertyImages', [ApiController::class, 'uploadPropertyImages']);
    Route::post('deletePropertyImage', [ApiController::class, 'deletePropertyImage']);
    Route::post('imageCategory', [ApiController::class, 'imageCategory']);
    Route::get('search/projects', [ApiController::class, 'searchProjects']);
    Route::post('addProjects', [ApiController::class, 'addProjects']);
    Route::get('search/localities', [ApiController::class, 'searchLocalities']);
    Route::post('search/getProfileProperties', [ApiController::class, 'getProfileProperties']);
    Route::post('common-delete', [ApiController::class, 'commonDelete']);
  Route::match(['get', 'post'], 'check-unique', [ApiController::class, 'checkUnique']);
  Route::match(['get', 'post'], '/excelUpload/{modal}', [ApiController::class, 'excelUpload']);
 
 
 
    Route::post('getBranches', [ApiController::class, 'getBranches']);
    Route::post('branch', [ApiController::class, 'createBranch']);
    Route::put('branch/{id}', [ApiController::class, 'updateBranch']);
    Route::delete('delete/{id}/{modal}', [ApiController::class, 'deleteCommonSingle']);
    Route::post('bulk-delete/{modal}', [ApiController::class, 'deleteCommonBulk']);
    Route::patch('status/{id}/{modal}', [ApiController::class, 'statusChangeSingle']);
    
    Route::post('role', [ApiController::class, 'createRole']);
    Route::put('role/{id}', [ApiController::class, 'updateRole']);
    Route::delete('deleteFeesGroup/{id}', [ApiController::class, 'deleteFeesGroup']);
    Route::post('getRoles', [ApiController::class, 'getRoles']);
    
    Route::post('user', [ApiController::class, 'createUser']);
    Route::put('user/{id}', [ApiController::class, 'updateUser']);
    Route::post('getUsers', [ApiController::class, 'getUsers']);

  Route::post('student', [ApiController::class, 'createStudent']);
  Route::post('getStudents', [ApiController::class, 'getStudents']);
  Route::put('student/{id}', [ApiController::class, 'updateStudent']);

  Route::post('feesGroup', [ApiController::class, 'createFeesGroup']);
  Route::put('feesGroup/{id}', [ApiController::class, 'updateFeesGroup']);
  Route::post('getFeesGroup', [ApiController::class, 'getFeesGroup']);

  Route::post('feesType', [ApiController::class, 'createFeesType']);
  Route::put('feesType/{id}', [ApiController::class, 'updateFeesType']);
  Route::post('getFeesType', [ApiController::class, 'getFeesType']);
  Route::delete('deleteFeesType/{id}', [ApiController::class, 'deleteFeesType']);

  Route::post('feesMaster', [ApiController::class, 'createFeesMaster']);
  Route::post('getFeesMaster', [ApiController::class, 'getFeesMaster']);
  Route::delete('deleteFeesMaster/{id}', [ApiController::class, 'deleteFeesMaster']);

  Route::post('class', [ApiController::class, 'createClass']);
  Route::put('class/{id}', [ApiController::class, 'updateClass']);
  Route::post('getClass', [ApiController::class, 'getClass']);
  Route::delete('deleteClass/{id}', [ApiController::class, 'deleteClass']);

  Route::post('saveFcmToken/{id}', [ApiController::class, 'saveFcmToken']);
   
});