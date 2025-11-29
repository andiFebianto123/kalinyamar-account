<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportAccountController;
use GuzzleHttp\Psr7\Response;

Route::get('/', function () {
    return redirect('/admin/login');
});

