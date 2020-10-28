<?php

use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', 'Auth\LoginController@logout');

    Route::get('/user', 'Auth\UserController@current');

    Route::get('/users', 'Api\UserController@index');
    Route::post('/users', 'Api\UserController@store');
    Route::patch('/users/{user}', 'Api\UserController@update');
    Route::delete('/users/{user}', 'Api\UserController@destroy');

    Route::get('/organizations', 'Api\OrganizationController@index');
    Route::get('/roles', 'Api\RoleController@index');

    Route::patch('settings/profile', 'Settings\ProfileController@update');
    Route::patch('settings/password', 'Settings\PasswordController@update');

    Route::get('/active-checks', 'Api\CheckController@index');
    Route::get('/archive-checks', 'Api\CheckController@indexArchive');
    Route::get('/checks/export', 'Api\CheckController@exportChecks');
    Route::get('/checks/{check}/export', 'Api\CheckController@exportCheck');
    Route::get('/checks/{check}/toggle-status', 'Api\CheckController@toggleStatus');
    Route::get('/checks/{check}/move-to-archive', 'Api\CheckController@moveToArchive');
    Route::get('/checks/{check}/copy-from-archive', 'Api\CheckController@copyFromArchive');
    Route::get('/checks/{check}/items', 'Api\CheckController@items');
    Route::get('/checks/{check}/items/{item}/finish', 'Api\CheckController@finish');
    Route::get('/checks/{check}/items/{item}/{manager}', 'Api\CheckController@getItemStatus');
    Route::post('/checks/{check}/items/{item}/approve/{manager}', 'Api\CheckController@approveItem');
    Route::post('/checks/{check}/items/{item}/reject/{manager}', 'Api\CheckController@rejectItem');
    Route::post('/checks/{check}/items', 'Api\CheckController@storeItem');
    Route::patch('/checks/{check}/items/{item}', 'Api\CheckController@updateItem');
    Route::delete('/checks/{check}/items/{item}', 'Api\CheckController@deleteItem');
    Route::get('/checks/{check}/managers', 'Api\CheckController@managers');
    Route::post('/checks', 'Api\CheckController@store');
    Route::patch('/checks/{check}', 'Api\CheckController@update');
});

Route::group(['middleware' => 'guest:api'], function () {
    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register');

    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');

    Route::post('email/verify/{user}', 'Auth\VerificationController@verify')->name('verification.verify');
    Route::post('email/resend', 'Auth\VerificationController@resend');

    Route::post('oauth/{driver}', 'Auth\OAuthController@redirectToProvider');
    Route::get('oauth/{driver}/callback', 'Auth\OAuthController@handleProviderCallback')->name('oauth.callback');
});
