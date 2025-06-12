<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportAccountController;

Route::get('/', function () {
    return view('public/admin/login');
});
