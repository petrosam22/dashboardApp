<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\ProductController;
use App\Http\Controllers\api\DashboardController;

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







Route::prefix('user')->group(function () {
    Route::post('/register',[UserController::class,'register']);
    Route::post('/verifyEmail',[UserController::class,'verifyEmail']);
    Route::post('/login',[UserController::class,'login']);
    Route::post('/logout' , [UserController::class,'logout'])->middleware('auth:sanctum');


    Route::get('/info',[UserController::class,'userInformation'])->middleware('auth:sanctum');
    Route::patch('/update/{id}',[UserController::class,'update'])->middleware('auth:sanctum');
    Route::patch('/change/password',[UserController::class,'updatePassword']);


    Route::post('/forgotPassword',[UserController::class,'forgotPassword']);
    Route::post('/resetPassword',[UserController::class,'resetPassword']);

    Route::get('/product/{id}',[UserController::class,'userProducts']);

});



Route::middleware(['auth:sanctum', 'admin'])->prefix('product')->group(function () {

Route::post('/store',[ProductController::class,'store']);
Route::post('/update/{id}',[ProductController::class,'update']);
Route::delete('/delete/{id}',[ProductController::class,'destroy']);
Route::get('/all',[ProductController::class,'index']);
Route::delete('/forceDelete/{id}',[ProductController::class,'forceDeleteProduct']);
Route::post('/assign/{id}',[ProductController::class,'assignProductToUser']);


Route::get('/user/{id}' ,[DashboardController::class,'userProducts']);

}
);


Route::middleware(['auth:sanctum', 'admin'])->prefix('dashboard')->group(function () {
Route::get('/users' ,[DashboardController::class,'listUsers']);
Route::post('/create/user' ,[DashboardController::class,'createUser']);
Route::patch('/update/user/{id}' ,[DashboardController::class,'updateUser']);
Route::delete('/delete/user/{id}' ,[DashboardController::class,'destroyUser']);
Route::get('/user/{id}' ,[DashboardController::class,'userInformation']);


Route::post('/profile/{id}' ,[DashboardController::class,'profile']);


Route::get('/products' ,[DashboardController::class,'listProducts']);
Route::post('/create/product' ,[DashboardController::class,'createProduct']);
Route::post('update/product/{id}' ,[DashboardController::class,'updateProducts']);
Route::delete('/delete/product/{id}' ,[DashboardController::class,'deleteProducts']);
Route::post('assign/product/user/{id}' ,[DashboardController::class,'assignProductToUser']);

});
