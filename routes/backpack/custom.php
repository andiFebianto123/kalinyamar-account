<?php

use App\Http\Controllers\Admin\BalanceSheetCrudController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CastAccountsCrudController;
use App\Http\Controllers\Admin\InvoiceClientCrudController;
use App\Http\Controllers\Admin\CastAccountsLoanCrudController;
use App\Http\Controllers\Admin\ProfitLostAccountCrudController;

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
        Route::crud('purchase-order-tab', 'PurchaseOrderTabController');
        Route::crud('spk-trans', 'SpkCrudController');
        Route::post('download-po', 'PurchaseOrderCrudController@exportExcel');
        Route::post('download-po-pdf', 'PurchaseOrderCrudController@exportPdf');
    });

    Route::prefix('client')->group(function(){
        Route::crud('client-list', 'ClientCrudController');
        Route::post('select2-client', 'ClientPoCrudController@select2Client');
        Route::crud('po', 'ClientPoCrudController');
    });
    Route::crud('invoice-client', 'InvoiceClientCrudController');
    Route::get('invoice-client/{id}/print', [InvoiceClientCrudController::class, 'printInvoice']);
    Route::post('invoice-client/select2-client-po', [InvoiceClientCrudController::class, 'select2ClientPo']);
    Route::get('invoice-client/get-client-po', [InvoiceClientCrudController::class, 'selectedClientPo']);

    Route::prefix('cash-flow')->group(function(){
        Route::crud('cast-accounts', 'CastAccountsCrudController');
        Route::post('cast-accounts-transaction', [CastAccountsCrudController::class, 'storeTransaction']);
        Route::get('cast-accounts-show', [CastAccountsCrudController::class, 'showTransaction']);
        Route::get('cast-accounts-select-to-account', [CastAccountsCrudController::class, 'getSelectToAccount']);
        Route::post('cast-accounts-move-transaction', [CastAccountsCrudController::class, 'storeMoveTransfer']);

        Route::crud('cast-account-loan', 'CastAccountsLoanCrudController');
        Route::post('cast-account-loan-transaction', [CastAccountsLoanCrudController::class, 'storeTransaction']);
        Route::post('cast-account-loan-move-transaction', [CastAccountsLoanCrudController::class, 'storeMoveTransaction']);
        Route::get('cast-account-loan-show', [CastAccountsLoanCrudController::class, 'showTransaction']);
    });

    Route::prefix('finance-report')->group(function(){
        Route::crud('expense-account', 'ExpenseAccountCrudController');
        Route::crud('profit-lost', 'ProfitLostAccountCrudController');
        Route::post('profit-lost/store-project', [ProfitLostAccountCrudController::class, 'storeProject']);
        Route::get('profit-lost/{id}/detail', [ProfitLostAccountCrudController::class, 'detail']);
        Route::crud('balance-sheet', 'BalanceSheetCrudController');
        Route::get('show-total-account', [BalanceSheetCrudController::class, 'showTotalAccount']);
    });
    Route::post('account/select2-account', [CastAccountsCrudController::class, 'account_select2']);

}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
