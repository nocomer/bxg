<?php

/*
|--------------------------------------------------------------------------
| Module Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for the module.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::group(['prefix' => 'vipshop'], function() {
	Route::get('/','VipshopController@Index')->name('shopIndex');//vip店铺首页路由


	Route::get('index','VipshopController@Index')->name('vipIndex');//vip首页路由
	Route::get('page','VipshopController@Page')->name('vipPage');//vip访谈路由
	Route::get('details/{id}','VipshopController@Details')->name('vipDeails');//vip访谈详情路由
	Route::get('payvip','VipshopController@getPayvip')->name('payvip');//套餐购买
    Route::post('feedback','VipshopController@feedback')->name('addFeedback');//创建vip反馈
    Route::get('vipinfo','VipshopController@vipinfo')->name('vipinfo');//特权介绍
});

Route::group(['prefix' => 'vipshop', 'middleware' => 'auth'], function (){
    Route::post('payvip','VipshopController@postPayvip');//套餐购买
    Route::get('vipPayorder','VipshopController@vipPayorder')->name('vipPayorder');//套餐支付
    Route::post('vipPayorder', 'VipshopController@postVipPayorder');
    Route::post('thirdPayorder', 'VipshopController@thirdPayorder');
    Route::get('vipsucceed','VipshopController@vipSucceed')->name('vipSucceed');//套餐支付成功
    Route::get('vipfailure','VipshopController@vipFailure')->name('vipFailure');//套餐支付失败

});
