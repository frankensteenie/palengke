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
        Route::post('forPayment', 'apiControllers@forPayment');
        Route::post('paymentDone', 'apiControllers@paymentDone');
        Route::get('fetchForDeliveryOrdersClient', 'apiControllers@fetchForDeliveryOrdersClient');

        // sellers
        Route::get('fetchMyItems', 'apiControllers@fetchMyItems');
        Route::post('editThisItem', 'apiControllers@editThisItem');
        Route::post('deleteThisItem', 'apiControllers@deleteThisItem');
        Route::post('editThis', 'apiControllers@editThis');

        Route::get('fetchCategories', 'apiControllers@fetchCategories');
        Route::post('addItem', 'apiControllers@addItem');
        Route::get('fetchPendingPaidOrders', 'apiControllers@fetchPendingPaidOrders');
        Route::post('forDelivery', 'apiControllers@forDelivery');
        Route::get('fetchForDeliveryOrders', 'apiControllers@fetchForDeliveryOrders');
    });

});