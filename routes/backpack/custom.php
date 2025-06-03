<?php

use App\Http\Controllers\Admin\InvoiceClientCrudController;
use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group(['middleware' => 'web', 'prefix' => config('backpack.base.route_prefix'), 'namespace' => 'App\Http\Controllers'], function () {
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('backpack.auth.login');
    Route::post('login', 'Auth\LoginController@login');
    Route::get('logout', 'Auth\LoginController@logout')->name('backpack.auth.logout');
    Route::post('logout', 'Auth\LoginController@logout');

    Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('backpack.auth.register');
    Route::post('register', 'Auth\RegisterController@register');
});

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('tag', 'TagCrudController');
    Route::prefix('auth')->group(function () {
        Route::crud('permission', 'PermissionCrudController');
        Route::crud('role', 'RoleCrudController');
        Route::crud('user', 'UserCrudController');
    });
    Route::prefix('vendor')->group(function(){
        Route::crud('subkon', 'SubkonCrudController');
        Route::post('select2-subkon-id', 'PurchaseOrderCrudController@select2SubkonId')->name('select2-subkon-id');
        Route::crud('purchase-order', 'PurchaseOrderCrudController');
        Route::crud('spk-trans', 'SpkCrudController');
    });

    Route::prefix('client')->group(function(){
        Route::crud('client-list', 'ClientCrudController');
        Route::post('select2-client', 'ClientPoCrudController@select2Client');
        Route::crud('po', 'ClientPoCrudController');
    });
    Route::crud('invoice-client', 'InvoiceClientCrudController');
    Route::post('invoice-client/select2-client-po', [InvoiceClientCrudController::class, 'select2ClientPo']);
    Route::get('invoice-client/get-client-po', [InvoiceClientCrudController::class, 'selectedClientPo']);
}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
