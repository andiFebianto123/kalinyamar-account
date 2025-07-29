<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AssetCrudController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VoucherCrudController;
use App\Http\Controllers\Admin\ClientPoCrudController;
use App\Http\Controllers\Admin\BalanceSheetCrudController;
use App\Http\Controllers\Admin\CastAccountsCrudController;
use App\Http\Controllers\Admin\InvoiceClientCrudController;
use App\Http\Controllers\Admin\StatusProjectCrudController;
use App\Http\Controllers\Admin\VoucherPaymentCrudController;
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
    Route::crud('dashboard', 'DashboardController');
    Route::get('dashboard/get-chart', [DashboardController::class, 'totalAlldashboard']);
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
        Route::get('po/total', [ClientPoCrudController::class, 'countAllPPn']);
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
        Route::crud('list-asset', 'AssetCrudController');
        Route::post('select2-account-id', [AssetCrudController::class, 'select2account']);
    });

    Route::prefix('fa')->group(function(){
        Route::crud('voucher', 'VoucherCrudController');
        Route::post('voucher/select2-po-spk', [VoucherCrudController::class, 'select2_no_po_spk']);
        Route::post('voucher/{id}/approve', [VoucherCrudController::class, 'approvedStore']);
        Route::get('voucher/total', [VoucherCrudController::class, 'total_voucher']);
        Route::crud('voucher-payment', 'VoucherPaymentCrudController');
        Route::post('voucher-payment/{id}/approve', [VoucherPaymentCrudController::class, 'approvedStore']);
        Route::get('voucher-payment/total', [VoucherPaymentCrudController::class, 'total_voucher']);
    });

    Route::prefix('monitoring')->group(function(){
        Route::crud('project-system-setup', 'ProjectSystemSetupCrudController');
        Route::crud('project-list', 'ProjectListCrudController');
        Route::crud('project-report', 'ProjectListReportCrudController');
        Route::crud('quotation', 'QuotationCrudController');
        Route::crud('quotation-status', 'StatusQuotaionCrudController');
        Route::crud('quotation-check', 'QuotationCheckCrudController');
        Route::crud('project-status', 'StatusProjectCrudController');
        Route::get('project-status/resume-total', [StatusProjectCrudController::class, 'resumeTotal']);
    });

    Route::post('account/select2-account', [CastAccountsCrudController::class, 'account_select2']);

}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
