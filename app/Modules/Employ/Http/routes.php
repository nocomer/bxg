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

Route::group(['prefix' => 'employ','middleware' => 'auth'], function() {
	Route::get('/','IndexController@index');
	Route::get('/create/{id}/{service?}','IndexController@employCreate');//创建一条雇佣服务商的记录
	Route::post('/update','IndexController@employUpdate');//提交雇佣数据
	Route::get('/bounty/{id}','IndexController@employBounty')->name('bounty');//雇佣托管赏金
	Route::post('/bounty','IndexController@employBountyUpdate')->name('bounty_update');//雇佣托管赏金
	Route::get('/result','IndexController@result')->name('resultCreate');//支付宝回调

	Route::get('/workin/{id}','IndexController@workin')->name('workin');//威客工作
	Route::get('/success','IndexController@success')->name('success');//雇佣托管赏金等待页面
	Route::get('/except/{id}/{type}','IndexController@except')->name('except');//雇佣托管赏金等待页面
	Route::post('/validBounty','IndexController@validBounty');//验证赏金
	Route::post('/workCreate','IndexController@workCreate');//提交稿件
	Route::get('/acceptWork/{id}','IndexController@acceptWork');//验收通过
	Route::post('/employRights','IndexController@employRights');//维权提交
	Route::post('/employEvaluate','IndexController@employEvaluate');//评价提交
	Route::get('/employCheck/{id}','IndexController@employCheck');//评价提交
});
