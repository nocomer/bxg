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

Route::group(['prefix' => 'advertisement'], function() {
	Route::get('/', function() {
		dd('This is the Advertisement module index page.');
	});
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['prefix' => 'advertisement', 'middleware' =>[ 'manageauth', 'RolePermission']], function () {
	//广告位路由
	Route::get('/adTarget','AdTargetController@index')->name('adTargetList');//广告位列表
	Route::get('/adList/{id}','AdTargetController@adListById')->name('adTargetDetail');//根据广告位查看广告列表
	//广告路由
	Route::get('/adList','AdController@adlist')->name('adList');//广告列表
	Route::get('/insert','AdController@getInsertAd')->name('adCreatePage');//加载创建广告页面
	Route::post('/adInfo','AdController@storeAdInfo')->name('adCreate');//创建广告信息
	Route::get('/update/{id}','AdController@getUpdateAd')->name('adUpdatePage');//加载编辑广告页面
	Route::post('/updateInfo/{id}','AdController@updateAdInfo')->name('adUpdate');//修改广告信息
	Route::get('/deleteInfo/{id}','AdController@deleteAdInfo')->name('adDelete');//删除广告信息

	//推荐管理
	Route::get('/recommendList','RecommendController@recommendList')->name('recommendList');//推荐位置列表
	Route::get('/nameUpdate','RecommendController@nameUpdate')->name('recommendUpdate');//编辑推荐位的名称
	Route::get('/serverListByID/{id}','RecommendController@serverListByID')->name('recommendDetail');//推荐位下的服务商列表
	Route::get('/serverList','RecommendController@serverList')->name('commendList');//所有推荐位下的服务商列表
	Route::get('/deleteReInfo/{id}','RecommendController@deleteReInfo')->name('commendDelete');//删除某个服务商信息
	Route::get('/insertRecommend','RecommendController@insertRecommend')->name('commendCreatePage');//跳转到创建服务商页面
	Route::post('/addRecommend','RecommendController@addRecommend')->name('commendCreate');//创建服务商信息
	Route::get('/updateRecommend/{id}','RecommendController@updateRecommend')->name('commendUpdatePage');//跳转到修改服务商页面
	Route::post('/modifyRecommend/{id}','RecommendController@modifyRecommend')->name('commendUpdate');//修改服务商信息
	Route::get('/getReInfo','RecommendController@getReInfo')->name('classificationDetail');//获取所属分类信息

});
