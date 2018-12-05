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

Route::group(['prefix' => 'task','middleware' => 'auth'], function() {
//	Route::get('/', 'IndexController@index');//本地测试
	//任务发布
	Route::get('/create','IndexController@create')->name('taskCreatePage');//创建任务页面
	Route::post('/createTask','IndexController@createTask')->name('taskCreate');//创建任务提交
	Route::post('/fileUpload','IndexController@fileUpload')->name('fileCreate');//创建任务文件上床
	Route::get('/fileDelet','IndexController@fileDelet')->name('fileDelete');//创建任务文件上传删除
	Route::get('/bounty/{id}','IndexController@bounty')->name('bountyPage');//赏金托管
	Route::get('/getTemplate','IndexController@getTemplate')->name('ajaxTemplate');//ajax获取模板
	Route::get('/preview','IndexController@preview')->name('previewPage');//创建任务任务预览
	Route::get('/release/{id}','IndexController@release')->name('releaseDetail');//用户中心发布任务
	Route::get('/tasksuccess/{id}','IndexController@tasksuccess')->name('tasksuccess');//成功发布任务
	//任务详情
	Route::post('/workCreate','DetailController@workCreate')->name('workCreate');//任务详情悬赏模式竞标投稿提交
	Route::get('/workdelivery/{id}','DetailController@work')->name('workdeliveryPage');//任务详情竞标投稿页面

	Route::post('/ajaxAttatchment','DetailController@ajaxWorkAttatchment')->name('ajaxCreateAttatchment');//
	Route::get('/delAttatchment','DetailController@delAttatchment')->name('attatchmentDelete');
	Route::get('/winBid/{work_id}/{task_id}','DetailController@winBid')->name('winBid');//任务详情中标按钮(悬赏模式)
	Route::get('/download/{id}','DetailController@download')->name('download');//任务详情下载附件
	Route::get('/delivery/{id}','DetailController@delivery')->name('taskdeliveryPage');//任务详情交付页面
	Route::post('/deliverCreate','DetailController@deliverCreate')->name('deliverCreate');//任务详情交付提交
	Route::get('/check','DetailController@workCheck')->name('check');//任务详情验收通过
	Route::get('/lostCheck','DetailController@lostCheck')->name('lostCheck');//任务详情维权
	Route::get('/evaluate','DetailController@evaluate')->name('evaluatePage');//任务详情评价页面
	Route::post('/evaluateCreate','DetailController@evaluateCreate')->name('evaluateCreate');//任务详情评价提交
	//任务维权悬赏模式
	Route::post('/ajaxRights','DetailController@ajaxRights')->name('ajaxCreateRights');//任务维权提交
	//任务举报
	Route::post('/report','DetailController@report')->name('reportCreate');//任务举报提交

	//回复功能
	Route::get('/getComment/{id}','DetailController@getComment')->name('commentList');//任务详情拉取评价列表
	Route::post('/ajaxComment','DetailController@ajaxComment')->name('ajaxCreateComment');//任务详情评价提交

	//赏金托管
	Route::post('/bountyUpdate','IndexController@bountyUpdate')->name('bountyUpdate');//任务赏金托管提交
	Route::get('/result','IndexController@result')->name('resultCreate');//任务赏金托管支付宝同步回调
	Route::post('/notify','IndexController@notify')->name('notifyCreate');//任务赏金托管支付宝异步回调
	//微信支付
	Route::get('/weixinNotify','IndexController@weixinNotify')->name('weixinNotifyCreate');//任务赏金托管微信回调

	//地区三级联动
	Route::get('/ajaxcity','IndexController@ajaxcity')->name('ajaxcity');//任务发布地区三级联动(城市联动)
	Route::get('/ajaxarea','IndexController@ajaxarea')->name('ajaxarea');//任务发布地区三级联动(地区联动)

	//编辑器图片上传
	Route::get('/imgupload','IndexController@imgupload')->name('imgupload');//编辑器图片上传控件

	//2017-09-11
	//(招标模式)发布任务投稿截止日期验证
	Route::post('/checkDeadlineByBid','IndexController@checkDeadlineByBid')->name('checkDeadlineByBid');
	//(招标模式)发布任务购买增值服务
	Route::get('/buyServiceTaskBid/{id}','IndexController@buyServiceTaskBid')->name('buyServiceTaskBid');
	//(招标模式)发布任务购买增值服务
	Route::post('/buyServiceTaskBid','IndexController@postBuyServiceTaskBid')->name('postBuyServiceTaskBid');

	//任务详情招标模式竞标投稿页面
	Route::get('/tenderWork/{id}','DetailController@tenderWork')->name('tenderWork');
	//任务详情中标按钮(招标模式)
	Route::get('/bidWinBid/{work_id}/{task_id}','DetailController@bidWinBid')->name('bidWinBid');
	//招标模式选中服务商后托管赏金页面
	Route::get('/bidBounty/{id}','IndexController@bidBounty')->name('bidBounty');
	//任务赏金托管提交 招标模式
	Route::post('/bidBountyUpdate','IndexController@bidBountyUpdate')->name('bidBountyUpdate');
	//招标模式确认付款方式
	Route::get('/payType/{id}','DetailController@payType')->name('payType');
	//招标模式 雇主选择支付方式(阶段付款)
	Route::get('/ajaxPaySection','DetailController@ajaxPaySection')->name('ajaxPaySection');
	//招标模式 雇主保存支付方式
	Route::post('/postPayType','DetailController@postPayType')->name('postPayType');
	//招标模式 威客是否同意支付方式
	Route::get('/checkPayType/{taskid}/{status}','DetailController@checkPayType')->name('checkPayType');
	//招标模式雇主再次确认付款方式
	Route::get('/payTypeAgain/{id}','DetailController@payTypeAgain')->name('payTypeAgain');
	//招标模式威客阶段交付稿件
	Route::get('/bidDelivery/{id}','DetailController@bidDelivery')->name('bidDelivery');
	Route::post('/bidDeliverCreate','DetailController@bidDeliverCreate')->name('bidDeliverCreate');
	//任务详情招标模式验收操作(是否通过)
	Route::get('/bidWorkCheck','DetailController@bidWorkCheck')->name('bidWorkCheck');
	//任务维权提交 招标模式
	Route::post('/ajaxBidRights','DetailController@ajaxBidRights')->name('ajaxBidRights');

	//去kee交付台查看
	Route::get('/toKee/{id}','DetailController@toKee')->name('toKee');
});


Route::group(['prefix'=>'task'],function(){
	//任务大厅
	Route::get('/','IndexController@tasks')->name('taskList');//任务大厅
	//任务详情
	Route::get('/{id}','DetailController@index')->name('taskDetailPage')->where('id', '[0-9]+');;//任务详情页面

	//成功案例
	Route::get('/successCase','SuccessCaseController@index')->name('successCaseList');//成功案例列表页面
	Route::get('/successDetail/{id}','SuccessCaseController@detail')->name('successDetail');//成功案例详情页
	Route::get('/successJump/{id}','SuccessCaseController@jump')->name('successJump');//成功案例中间跳转
	//验证赏金符合后台配置
	Route::post('/checkbounty','IndexController@checkBounty')->name('checkbounty');//发布任务赏金ajax验证
	Route::post('/checkdeadline','IndexController@checkDeadline')->name('checkdeadline');//发布任务投稿截止日期验证

	//ajax分页
	Route::get('/ajaxPageWorks/{id}','DetailController@ajaxPageWorks')->name('ajaxPageWorks');//任务详情稿件筛选与分页
	Route::get('/ajaxPageDelivery/{id}','DetailController@ajaxPageDelivery')->name('ajaxPageDelivery');//任务详情交付类容分页
	Route::get('/ajaxPageComment/{id}','DetailController@ajaxPageComment')->name('ajaxPageComment');//任务详情评价筛选与分页

	//收藏任务
	Route::get('/collectionTask/{task_id}','IndexController@collectionTask');
	Route::post('/collectionTask','IndexController@postCollectionTask');
	//记住table
	Route::get('/rememberTable','DetailController@rememberTable');



});
