<?php

use Illuminate\Support\Facades\Route;


Route::group(['middleware' => ['preventbackhistory']], function () {

    Route::group(['middleware' => ['checkislogin']], function () {
        Route::get('/', 'pageControllers@index');
        Route::get('/login', 'pageControllers@login');
        Route::get('/register', 'pageControllers@register');
        Route::post('/loginweb', 'apiControllers@loginweb');
    });
    
    Route::group(['middleware' => ['checkisuser']], function () {
        Route::get('/main', 'pageControllers@main');
        Route::get('mypage', 'pageControllers@mypage');
        Route::post('fetchProducts', 'apiControllers@fetchProducts');
        Route::post('addToCart', 'apiControllers@addToCart');
        Route::get('fetchMyOrders', 'apiControllers@fetchMyOrders');
        Route::post('addToGroup', 'apiControllers@addToGroup');
        Route::get('fetchQueuedOrders', 'apiControllers@fetchQueuedOrders');
        Route::post('deleteThis', 'apiControllers@deleteThis');
    });

});