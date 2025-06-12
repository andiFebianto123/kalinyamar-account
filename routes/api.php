<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportAccountController;


Route::middleware('api')->group(function(){
    Route::post('import-account', [ImportAccountController::class, 'import']);
});
// Route::group(['middleware' => 'api'], function () {
// });
