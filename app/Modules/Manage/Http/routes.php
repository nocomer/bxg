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
Route::get('/manage/login', 'Auth\AuthController@getLogin')->name('loginCreatePage');
Route::group(['middleware' => 'systemlog'], function() {
    Route::post('/manage/login', 'Auth\AuthController@postLogin')->name('loginCreate');
});
Route::get('/manage/logout', 'Auth\AuthController@getLogout')->name('logout');

Route::group(['prefix' => 'manage', 'middleware' => ['manageauth', 'RolePermission','systemlog']], function() {

    Route::get('/', 'IndexController@getManage')->name('backstagePage');//后台首页
    //RBAC路由
    Route::get('/addRole', 'IndexController@addRole')->name('roleCreate');
    Route::get('/addPermission', 'IndexController@addPermission')->name('permissionCreate');
    Route::get('/attachRole', 'IndexController@attachRole')->name('attachRoleCreate');
    Route::get('/attachPermission', 'IndexController@attachPermission')->name('attachPermissionCreate');
    //实名认证管理路由
    Route::get('/realnameAuthList', 'AuthController@realnameAuthList')->name('realnameAuthList');//实名认证列表
    Route::get('/realnameAuthHandle/{id}/{action}', 'AuthController@realnameAuthHandle')->name('realnameAuthHandle');//实名认证处理
    Route::get('/realnameAuth/{id}', 'AuthController@realnameAuth')->name('realnameAuth');//实名认证详情


    //支付宝认证管理路由
    Route::get('/alipayAuthList', 'AuthController@alipayAuthList')->name('alipayAuthList');//支付宝认证列表
    Route::get('/alipayAuthHandle/{id}/{action}', 'AuthController@alipayAuthHandle')->name('alipayAuthHandle');//支付宝认证处理
    Route::post('/alipayAuthMultiHandle', 'AuthController@alipayAuthMultiHandle')->name('alipayAuthMultiHandle');//支付宝认证批量处理
    Route::get('alipayAuth/{id}', 'AuthController@getAlipayAuth')->name('alipayAuth');//支付宝认证详情
    Route::post('alipayAuthPay', 'AuthController@alipayAuthPay')->name('alipayAuthPayCreate');//支付宝后台打款

    //银行认证管理路由
    Route::get('/bankAuthList', 'AuthController@bankAuthList')->name('bankAuthList');//银行认证列表
    Route::get('/bankAuthHandle/{id}/{action}', 'AuthController@bankAuthHandle')->name('bankAuthHandle');//银行认证处理
    Route::post('/bankAuthMultiHandle', 'AuthController@bankAuthMultiHandle')->name('bankAuthMultiHandle');//银行认证批量审核
    Route::get('/bankAuth/{id}', 'AuthController@getBankAuth')->name('bankAuth');//银行认证列表
    Route::post('bankAuthPay', 'AuthController@bankAuthPay')->name('bankAuthPayCreate');//银行后台支付

    //任务管理路由
    Route::get('/taskList', 'TaskController@taskList')->name('taskList');//任务列表	
    Route::get('/taskHandle/{id}/{action}', 'TaskController@taskHandle')->name('taskUpdate');//任务处理
    Route::post('/taskMultiHandle', 'TaskController@taskMultiHandle')->name('taskMultiUpdate');//任务批量处理
    Route::get('/taskDetail/{id}', 'TaskController@taskDetail')->name('taskDetail');//任务详情
    Route::post('/taskDetailUpdate', 'TaskController@taskDetailUpdate')->name('taskDetailUpdate');//任务详情提交
    Route::get('/taskMassageDelete/{id}', 'TaskController@taskMassageDelete')->name('taskMassageDelete');//删除任务留言
    //招标管理路由
	Route::get('/bidList', 'BidController@bidList')->name('bidList');//任务列表
	Route::get('/bidDetail/{id}', 'BidController@bidDetail')->name('bidDetail');//任务详情
	Route::get('/bidConfig/{id}', 'BidController@bidConfig')->name('bidConfig');//任务配置
	Route::post('/bidConfigUpdate', 'BidController@bidConfigUpdate')->name('bidConfigUpdate');//任务配置修改
    //财务管理路由
    Route::get('/financeList', 'FinanceController@financeList')->name('financeList');//网站流水列表
    Route::get('/financeListExport/{param}', 'FinanceController@financeListExport')->name('financeListExportCreate');//导出网站流水记录
    Route::get('/userFinanceListExport/{param}', 'FinanceController@userFinanceListExport')->name('userFinanceListExportCreate');//用户流水导出
    Route::get('/financeStatement', 'FinanceController@financeStatement')->name('financeStatementList');//财务报表
    Route::get('/financeRecharge', 'FinanceController@financeRecharge')->name('financeRechargeList');//财务报表-充值记录
    Route::get('/financeRechargeExport/{param}', 'FinanceController@financeRechargeExport')->name('financeRechargeExportCreate');//充值记录导出
    Route::get('/financeWithdraw', 'FinanceController@financeWithdraw')->name('financeWithdrawList');//财务报表-提现记录
    Route::get('/financeWithdrawExport/{param}', 'FinanceController@financeWithdrawExport')->name('financeWithdrawExportCreate');//提现记录导出
    Route::get('/financeProfit', 'FinanceController@financeProfit')->name('financeProfitList');//财务报表-利润统计

    //地区管理路由
    Route::get('/area','AreaController@areaList')->name('areaList');//地区管理列表
    Route::post('/areaCreate','AreaController@areaCreate')->name('areaCreate');//地区管理添加
    Route::get('/areaDelete/{id}','AreaController@areaDelete')->name('areaDelete');//地区管理删除
    Route::get('/ajaxcity','AreaController@ajaxCity')->name('ajaxCity');//地区管理筛选（城市）
    Route::get('/ajaxarea','AreaController@ajaxArea')->name('ajaxArea');//地区管理筛选（地区）

    //行业管理路由
    Route::get('/industry','IndustryController@industryList')->name('industryList');//行业管理列表
    Route::post('/industryCreate','IndustryController@industryCreate')->name('industryCreate');//行业管理提交
    Route::get('/industryDelete/{id}','IndustryController@industryDelete')->name('industryDelete');//行业管理删除
    Route::get('/ajaxSecond','IndustryController@ajaxSecond')->name('ajaxSecond');//行业管理筛选（城市）
    Route::get('/ajaxThird','IndustryController@ajaxThird')->name('ajaxThird');//行业管理筛选（地区）
    Route::get('/tasktemplate/{id}','IndustryController@taskTemplates')->name('taskTemplates');//行业实例页面
    Route::post('/templateCreate','IndustryController@templateCreate')->name('templateCreate');//行业实例添加控制器
    Route::get('/industryInfo/{id}','IndustryController@industryInfo')->name('industryDetail');//编辑行业分类图标
    Route::post('/industryInfo','IndustryController@postIndustryInfo')->name('postIndustryDetail');//编辑行业分类图标


    Route::get('/userFinance', 'FinanceController@userFinance')->name('userFinanceCreate');//用户流水记录
    Route::get('/cashoutList', 'FinanceController@cashoutList')->name('cashoutList');//提现审核列表
    Route::get('/cashoutHandle/{id}/{action}', 'FinanceController@cashoutHandle')->name('cashoutUpdate');//提现审核处理
    Route::get('cashoutInfo/{id}', 'FinanceController@cashoutInfo')->name('cashoutDetail');//提现记录详情

    Route::get('userRecharge', 'FinanceController@getUserRecharge')->name('userRechargePage');//后台充值视图
    Route::post('userRecharge', 'FinanceController@postUserRecharge')->name('userRechargeUpdate');//后台用户充值
    Route::get('rechargeList', 'FinanceController@rechargeList')->name('rechargeList');// 用户充值订单列表
    Route::get('confirmRechargeOrder/{order}', 'FinanceController@confirmRechargeOrder')->name('confirmRechargeOrder');//后台确认订单充值

    //全局配置
    Route::get('/config', 'ConfigController@getConfigBasic')->name('configDetail');//
    Route::get('/config/basic', 'ConfigController@getConfigBasic')->name('basicConfigDetail');//基本配置
    Route::post('/config/basic', 'ConfigController@saveConfigBasic')->name('configBasicUpdate');//保存基本配置
    Route::get('/config/seo', 'ConfigController@getConfigSEO')->name('seoConfigDetail');//seo配置
    Route::post('/config/seo', 'ConfigController@saveConfigSEO')->name('configSeoUpdate');//保存seo配置
    Route::get('/config/nav', 'ConfigController@getConfigNav')->name('navConfigDetail');//获取导航配置
    Route::post('/config/nav', 'ConfigController@postConfigNav')->name('configNavCreate');//新增导航
    Route::get('/config/nav/{id}/delete', 'ConfigController@deleteConfigNav')->name('configNavDelete');//删除导航
    Route::get('/config/attachment', 'ConfigController@getAttachmentConfig')->name('attachmentConfigDetail');//附件配置
    Route::post('/config/attachment', 'ConfigController@postAttachmentConfig')->name('attachmentConfigCreate');//保存附件配置信息

    Route::get('/config/site', 'ConfigController@getConfigSite')->name('siteConfigDetail');//站点配置视图
    Route::post('/config/site', 'ConfigController@saveConfigSite')->name('configSiteUpdate');//保存站点配置
    Route::get('/config/email', 'ConfigController@getConfigEmail')->name('emailConfigDetail');//邮箱配置视图
    Route::post('/config/email', 'ConfigController@saveConfigEmail')->name('configEmailUpdate');//保存邮箱配置

    Route::post('/config/sendEmail', 'ConfigController@sendEmail')->name('sendEmail');//发送测试邮件

    Route::get('/config/link', 'ConfigController@configLink')->name('configLink');//站点配置关注链接
    Route::post('/config/link', 'ConfigController@link')->name('postConfigLink');//站点配置关注链接

    Route::get('/config/phone', 'ConfigController@getConfigPhone')->name('phoneConfigDetail');//短信配置视图
    Route::post('/config/phone', 'ConfigController@saveConfigPhone')->name('configphoneUpdate');//保存短信配置

    Route::get('/config/appalipay', 'ConfigController@getConfigAppAliPay')->name('getConfigAppAliPay');//app支付宝支付配置视图
    Route::post('/config/appalipay', 'ConfigController@saveConfigAppAliPay')->name('configAppAliPayUpdate');//保存app支付宝支付配置

    Route::get('/config/appwechat', 'ConfigController@getConfigAppWeChat')->name('getConfigAppWeChat');//app微信支付配置视图
    Route::post('/config/appwechat', 'ConfigController@saveConfigAppWeChat')->name('configAppWeChatUpdate');//保存app微信支付配置

    Route::get('/config/wechatpublic', 'ConfigController@getConfigWeChatPublic')->name('getConfigWeChatPublic');//微信端配置视图
    Route::post('/config/wechatpublic', 'ConfigController@saveConfigWeChatPublic')->name('configWeChatPublicUpdate');//保存微信端配置


    //任务配置
    Route::get('/taskConfig/{id}','TaskConfigController@index')->name('taskConfigPage');//任务配置页面
    Route::post('/taskConfigUpdate','TaskConfigController@update')->name('taskConfigUpdate');//任务配置提交
    Route::get('/ajaxUpdateSys','TaskConfigController@ajaxUpdateSys')->name('ajaxUpdateSys');//任务配置系统辅助流程开关
    Route::post('/baseConfig','TaskConfigController@baseConfig')->name('baseConfigCreate');//任务配置基本配置


    //接口管理
    Route::get('payConfig', 'InterfaceController@getPayConfig')->name('payConfigDetail');//支付配置
    Route::post('payConfig', 'InterfaceController@postPayConfig')->name('payConfigUpdate');//保存支付配置
    Route::get('thirdPay', 'InterfaceController@getThirdPay')->name('thirdPayDetail');//第三方支付配置列表
    Route::get('thirdPayHandle/{id}/{action}', 'InterfaceController@thirdPayHandle')->name('thirdPayStatusUpdate');//启用/禁用支付接口
    Route::get('thirdPayEdit/{id}', 'InterfaceController@getThirdPayEdit')->name('thirdPayUpdatePage');//配置支付接口视图
    Route::post('thirdPayEdit', 'InterfaceController@postThirdPayEdit')->name('thirdPayUpdate');//保存支付配置

    //第三方登陆
    Route::get('thirdLogin', 'InterfaceController@getThirdLogin')->name('thirdLoginPage');//第三方登录授权配置
    Route::post('thirdLogin', 'InterfaceController@postThirdLogin')->name('thirdLoginCreate');//保存第三方登录配置

    //资讯中心路由
    Route::get('/article/{upID}','ArticleController@articleList')->name('articleList'); //资讯中心文章列表
    Route::get('/articleFooter/{upID}','ArticleController@articleList')->name('articleFooterList'); //页脚配置文章列表
    Route::get('/addArticle/{upID}','ArticleController@addArticle')->name('articleCreatePage'); //添加资讯文章视图
    Route::get('/addArticleFooter/{upID}','ArticleController@addArticle')->name('articleFooterCreatePage'); //添加页脚文章视图
    Route::post('/addArticle', 'ArticleController@postArticle')->name('articleCreate'); //添加文章
    Route::get('/articleDelete/{id}/{upID}','ArticleController@articleDelete')->name('articleDelete'); //删除文章
    Route::get('/editArticle/{id}/{upID}','ArticleController@editArticle')->name('articleUpdatePage'); //编辑资讯文章视图
    Route::get('/editArticleFooter/{id}/{upID}','ArticleController@editArticle')->name('articleFooterUpdatePage'); //编辑页脚文章视图
    Route::post('/editArticle', 'ArticleController@postEditArticle')->name('articleUpdate'); //编辑文章
    Route::post('/allDelete', 'ArticleController@allDelete')->name('allDelete'); //批量删除文章

    //资讯中心分类路由
    Route::get('/categoryList/{upID}','ArticleCategoryController@categoryList')->name('categoryList'); //资讯文章分类列表
    Route::get('/categoryFooterList/{upID}','ArticleCategoryController@categoryList')->name('categoryFooterList'); //页脚文章分类列表
    Route::get('/categoryDelete/{id}/{upID}','ArticleCategoryController@categoryDelete')->name('categoryDelete'); //删除文章分类
    Route::get('/categoryAdd/{upID}','ArticleCategoryController@categoryAdd')->name('categoryCreatePage'); //添加资讯文章分类视图
    Route::post('/categoryAdd', 'ArticleCategoryController@postCategory')->name('categoryCreate');//添加文章分类
    Route::get('/categoryEdit/{id}/{upID}','ArticleCategoryController@categoryEdit')->name('categoryUpdatePage');//编辑资讯文章分类视图
    Route::post('/categoryEdit','ArticleCategoryController@postEditCategory')->name('categoryUpdate');//编辑文章分类
    Route::post('/categoryAllDelete','ArticleCategoryController@cateAllDelete')->name('categoryAllDelete');//批量删除文章分类
    Route::get('/getChildCateList/{id}','ArticleCategoryController@getChildCateList')->name('getChildCateList'); //页脚文章分类列表
    Route::get('/categoryFooterAdd/{upID}','ArticleCategoryController@categoryAdd')->name('categoryFooterCreatePage'); //添加页脚文章分类视图
    Route::get('/categoryFooterEdit/{id}/{upID}','ArticleCategoryController@categoryEdit')->name('categoryFooterUpdatePage');//编辑页脚文章分类视图
    Route::get('/add/{upID}','ArticleCategoryController@add')->name('addCategory');//进入新建视图 判断资讯或页脚
    Route::get('/edit/{id}/{upID}','ArticleCategoryController@edit')->name('editCategory');//进入编辑视图 判断资讯或页脚


    //后台成功案例
    Route::get('/successCaseList','SuccessCaseController@successCaseList')->name('successCaseList');//成功案例列表
    Route::get('/successCaseAdd','SuccessCaseController@create')->name('successCaseCreatePage');//成功案例添加页面
    Route::post('/successCaseUpdate','SuccessCaseController@update')->name('successCaseCreate');//成功案例提交页面
    Route::get('/successCaseDel/{id}','SuccessCaseController@successCaseDel')->name('successCaseDel');//成功案例删除
    Route::post('/ajaxGetSecondCate','SuccessCaseController@ajaxGetSecondCate')->name('ajaxGetSecondCate');//成功案例提交页面

    //自定义导航
    Route::get('/navList','NavController@navList')->name('navList'); //自定义导航列表
    Route::get('/addNav','NavController@addNav')->name('navCreatePage');  //添加自定义导航视图
    Route::post('/addNav','NavController@postAddNav')->name('navCreate'); //添加自定义导航
    Route::get('/editNav/{id}','NavController@editNav')->name('navUpdatePage'); //编辑自定义导航视图
    Route::post('/editNav','NavController@postEditNav')->name('navUpdate'); //编辑自定义导航
    Route::get('/deleteNav/{id}','NavController@deleteNav')->name('navDelete');//删除自定义导航
    Route::get('/isFirst/{id}','NavController@isFirst')->name('isFirst'); //设为首页

    //用户管理
    Route::get('/userList', 'UserController@getUserList')->name('userList');//普通用户列表
    Route::get('/handleUser/{uid}/{action}', 'UserController@handleUser')->name('userStatusUpdate');//用户处理
    Route::get('/userAdd', 'UserController@getUserAdd')->name('userCreatePage');//添加用户视图
    Route::post('/userAdd', 'UserController@postUserAdd')->name('userCreate');//添加用户
    Route::post('checkUserName', 'UserController@checkUserName')->name('checkUserName');//检测用户名是否存在
    Route::post('checkEmail', 'UserController@checkEmail')->name('checkEmail');//检测邮箱
    Route::get('/userEdit/{uid}', 'UserController@getUserEdit')->name('userUpdatePage');//用户详情
    Route::post('/userEdit', 'UserController@postUserEdit')->name('userUpdate');//用户详情更新
    Route::get('/managerList', 'UserController@getManagerList')->name('managerList');//系统用户列表
    Route::get('/handleManage/{uid}/{action}', 'UserController@handleManage')->name('userStatusUpdate');//系统用户处理
    Route::get('/managerAdd', 'UserController@managerAdd')->name('managerCreatePage');//系统用户添加视图
    Route::post('/managerAdd', 'UserController@postManagerAdd')->name('managerCreate');//系统用户添加
    Route::post('checkManageName', 'UserController@checkManageName')->name('checkManageName');//检测系统用户名
    Route::post('checkManageEmail', 'UserController@checkManageEmail')->name('checkManageEmail');//检测系统用户邮箱
    Route::get('/managerDetail/{id}', 'UserController@managerDetail')->name('managerDetail');//系统用户详情
    Route::post('/managerDetail', 'UserController@postManagerDetail')->name('managerDetailUpdate');//更新系统用户
    Route::get('/managerDel/{id}', 'UserController@managerDel')->name('managerDelete');//系统用户删除
    Route::post('/managerDeleteAll', 'UserController@postManagerDeleteAll')->name('managerAllDelete');//系统用户批量删除

    Route::get('/rolesList', 'UserController@getRolesList')->name('rolesList');//用户组列表
    Route::get('/rolesAdd', 'UserController@getRolesAdd')->name('rolesCreatePage');//用户组添加视图
    Route::post('/rolesAdd', 'UserController@postRolesAdd')->name('rolesCreate');//用户组添加
    Route::get('/rolesDel/{id}', 'UserController@getRolesDel')->name('rolesDelete');//用户组删除
    Route::get('/rolesDetail/{id}', 'UserController@getRolesDetail')->name('rolesDetail');//用户组详情
    Route::post('/rolesDetail', 'UserController@postRolesDetail')->name('rolesDetailUpdate');//用户组更新

    Route::get('/permissionsList', 'UserController@getPermissionsList')->name('permissionsList');//权限列表
    Route::get('/permissionsAdd', 'UserController@getPermissionsAdd')->name('permissionsCreatePage');//权限添加视图
    Route::post('/permissionsAdd', 'UserController@postPermissionsAdd')->name('permissionsCreate');//权限添加
    Route::get('/permissionsDel/{id}', 'UserController@getPermissionsDel')->name('permissionsDelete');//删除权限
    Route::get('/permissionsDetail/{id}', 'UserController@getPermissionsDetail')->name('permissionsDetail');//权限详情
    Route::post('/permissionsDetail', 'UserController@postPermissionsDetail')->name('postPermissionsDetailUpdate');//权限更新

    //后台权限添加
    Route::get('/menuList/{id}/{level}','MenuController@getMenuList')->name('getMenuList');
    Route::get('/addMenu/{id?}','MenuController@addMenu')->name('addMenu');
    Route::post('/menuCreate','MenuController@menuCreate')->name('menuCreate');
    Route::get('/menuDelete/{id}','MenuController@menuDelete')->name('menuDelete');
    Route::get('/menuUpdate/{id}','MenuController@menuUpdate')->name('menuUpdate');
    Route::post('/updateMenu','MenuController@updateMenu')->name('updateMenu');
    //用户举报

    Route::get('/reportList','TaskReportController@reportList')->name('reportList');//用户举报列表
    Route::get('/reportDelet/{id}','TaskReportController@reportDelet')->name('reportDelete');//用户举报单个删除
    Route::post('/reportDeletGroup','TaskReportController@reportDeletGroup')->name('reportGroupDelete');//用户举报批量删除
    Route::get('/reportDetail/{id}','TaskReportController@reportDetail')->name('reportDetail');//用户举报详情
    Route::post('/handleReport','TaskReportController@handleReport')->name('reportUpdate');//用户举报处理

    //交易维权
    Route::get('/rightsList','TaskRightsController@rightsList')->name('rightsList');//交易维权列表
    Route::get('/rightsDelet/{id}','TaskRightsController@rightsDelet')->name('rightsDelete');//交易维权单个删除
    Route::post('/rightsDeletGroup','TaskRightsController@rightsDeletGroup')->name('rightsGroupDelete');//交易维权批量删除
    Route::get('/rightsDetail/{id}','TaskRightsController@rightsDetail')->name('rightsDetail');//交易维权详情
    Route::post('/handleRights','TaskRightsController@handleRights')->name('handleRightsCreate');//交易维权处理

    //增值工具
    Route::get('/serviceList','ServiceController@serviceList')->name('adServiceList'); //增值工具列表
    Route::get('/addService','ServiceController@addService')->name('addServiceCreatePage'); //添加增值工具视图
    Route::post('/addService','ServiceController@postAddService')->name('addServiceCreate');//添加增值工具
    Route::get('/editService/{id}','ServiceController@editService')->name('addServiceUpdatePage');//编辑增值工具视图
    Route::post('/postEditService','ServiceController@postEditService')->name('addServiceUpdate');//编辑增值工具
    Route::get('/deleteService/{id}','ServiceController@deleteService')->name('addServiceDelete');//删除增值工具
    Route::get('/serviceBuy','ServiceController@serviceBuy')->name('serviceBuyList'); //增值工具购买列表

    //友情链接
    Route::get('/link', 'LinkController@linkList')->name('linkList');//友情链接列表
    Route::post('/addlink', 'LinkController@postAdd')->name('linkCreate');//友情链接添加
    Route::get('/editlink/{id}', 'LinkController@getEdit')->name('linkUpdatePage');//友情链接详情
    Route::get('/deletelink/{id}', 'LinkController@getDeleteLink')->name('linkDelete');//友情链接删除
    Route::post('/allDeleteLink', 'LinkController@allDeleteLink')->name('allLinkDelete');//友情链接批量删除
    Route::get('/handleLink/{id}/{action}', 'LinkController@handleLink')->name('linkStatusUpdate');//友情链接处理
    Route::post('/updatelink/{id}', 'LinkController@postUpdateLink')->name('linkUpdate');//友情链接更新


    //投诉建议
    Route::get('/feedbackList', 'FeedbackController@listInfo')->name('feedbackList');//查看投诉建议列表信息
    Route::get('/feedbackDetail/{id}', 'FeedbackController@feedbackDetail')->name('feedbackDetail');//查看投诉建议详情
    Route::get('/feedbackReplay/{id}', 'FeedbackController@feedbackReplay')->name('feedbackReplayUpdate');//回复某个投诉建议
    Route::get('/deleteFeedback/{id}', 'FeedbackController@deletefeedback')->name('feedbackDelete');//删除某个投诉建议
    Route::get('/feedbackUpdate', 'FeedbackController@feedbackUpdate')->name('feedbackUpdate');//修改某个投诉建议

    //热词管理
    Route::get('/hotwordsList','HotwordsController@hotwordsInfo')->name('hotwordsList');//热词列表
    Route::post('/hotwordsCreate','HotwordsController@hotwordsCreate')->name('hotwordsCreate');//添加热词
    Route::get('/listorderUpdate','HotwordsController@listorderUpdate')->name('listorderUpdate');//热词排序修改
    Route::get('/hotwordsDelete/{id}','HotwordsController@hotwordsDelete')->name('hotwordsDelete');//删除热词信息
    Route::get('/hotwordsMulDelte','HotwordsController@hotwordsMulDelte')->name('hotwordsMulDelete');//批量删除热词信息

    //站长工具
    Route::get('attachmentList', 'ToolController@getAttachmentList')->name('attachmentList');//附件管理列表
    Route::get('attachmentDel/{id}', 'ToolController@attachmentDel')->name('attachmentDelete');//附件删除处理


    //短信模板
    Route::get('/messageList','MessageController@messageList')->name('messageList');//模板列表
    Route::get('/editMessage/{id}','MessageController@editMessage')->name('messageUpdatePage'); //编辑模版视图
    Route::post('/editMessage','MessageController@postEditMessage')->name('messageUpdate'); //编辑模版
    Route::get('/changeStatus/{id}/{isName}/{status}','MessageController@changeStatus')->name('messageStatusUpdate'); //改变模版状态

    //系统日志
    Route::get('/systemLogList','SystemLogController@systemLogList')->name('systemLogList');//系统日志列表
    Route::get('/systemLogDelete/{id}','SystemLogController@systemLogDelete')->name('systemLogDelete');//删除某个系统日志信息
    Route::get('/systemLogDeleteAll','SystemLogController@systemLogDeleteAll')->name('systemLogDeleteAll');//清空日志
    Route::post('/systemLogMulDelete','SystemLogController@systemLogMulDelete')->name('systemLogMulDelete');//批量删除

    //用户互评
    Route::get('/getCommentList','TaskCommentController@getCommentList')->name('commentList');//用户互评列表页面
    Route::get('/commentDel/{id}','TaskCommentController@commentDel')->name('commentDelete');//用户互评删除按钮
	
    //协议管理
    Route::get('/agreementList','AgreementController@agreementList')->name('agreementList'); //协议列表
    Route::get('/addAgreement','AgreementController@addAgreement')->name('agreementCreatePage');//添加协议视图
    Route::post('/addAgreement','AgreementController@postAddAgreement')->name('agreementCreate');//添加协议
    Route::get('/editAgreement/{id}','AgreementController@editAgreement')->name('agreementUpdatePage');//编辑协议视图
    Route::post('/editAgreement','AgreementController@postEditAgreement')->name('agreementUpdate');//编辑协议
    Route::get('/deleteAgreement/{id}','AgreementController@deleteAgreement')->name('agreementDelete');//删除协议

    //模板管理
    Route::get('/skin','AgreementController@skin')->name('manageSkin');//模板管理页面
    Route::get('/skinChange/{name}','AgreementController@skinChange')->name('skinChange');//模板更换
    Route::get('/skinSet/{number}','AgreementController@skinSet')->name('skinSet');//经典模板选择
    //关于我们
    Route::get('/aboutUs','ConfigController@aboutUs')->name('aboutUs');

    //雇佣管理
    Route::get('/employConfig','EmployController@employConfig')->name('employConfig');//雇佣配置
    Route::get('/employList','EmployController@employList')->name('employList');//雇佣列表
    Route::get('/employEdit/{id}','EmployController@employEdit')->name('employEdit');//雇佣编辑页面
    Route::post('/employUpdate','EmployController@employUpdate')->name('employUpdate');//雇佣修改控制器
    Route::get('/employDelete/{id}','EmployController@employDelete')->name('employDelete');//删除雇佣数据
    Route::get('/download/{id}','EmployController@download')->name('download');//下载附件
    Route::post('/configUpdate','EmployController@configUpdate')->name('configUpdate');//雇佣配置提交

    //企业认证管理路由
    Route::get('/enterpriseAuthList', 'AuthController@enterpriseAuthList')->name('enterpriseAuthList');//企业认证列表
    Route::get('/enterpriseAuthHandle/{id}/{action}', 'AuthController@enterpriseAuthHandle')->name('enterpriseAuthHandle');//企业认证处理
    Route::get('/enterpriseAuth/{id}', 'AuthController@enterpriseAuth')->name('enterpriseAuth');//企业认证详情
    Route::post('/allEnterprisePass', 'AuthController@allEnterprisePass')->name('allEnterprisePass');//企业认证批量通过
    Route::post('/allEnterpriseDeny', 'AuthController@allEnterpriseDeny')->name('allEnterpriseDeny');//企业认证批量失败

    //店铺管理路由
    Route::get('/shopList', 'ShopController@shopList')->name('shopList');//店铺列表
    Route::get('/shopInfo/{id}', 'ShopController@shopInfo')->name('shopInfo');//店铺详情
    Route::post('/updateShopInfo', 'ShopController@updateShopInfo')->name('updateShopInfo');//后台修改店铺详情
    Route::get('/openShop/{id}', 'ShopController@openShop')->name('openShop');//开启店铺
    Route::get('/closeShop/{id}', 'ShopController@closeShop')->name('closeShop');//关闭店铺
    Route::get('/recommendShop/{id}', 'ShopController@recommendShop')->name('recommendShop');//推荐店铺
    Route::get('/removeRecommendShop/{id}', 'ShopController@removeRecommendShop')->name('removeRecommendShop');//取消推荐店铺
    Route::post('/allOpenShop', 'ShopController@allOpenShop')->name('allOpenShop');//批量开启店铺
    Route::post('/allCloseShop', 'ShopController@allCloseShop')->name('allCloseShop');//批量关闭店铺

    Route::get('/shopConfig', 'ShopController@shopConfig')->name('shopConfig');//店铺配置视图
    Route::post('/postShopConfig', 'ShopController@postShopConfig')->name('postShopConfig');//保存店铺配置

    Route::get('/goodsList', 'GoodsController@goodsList')->name('goodsList');//商品列表
    Route::get('/goodsInfo/{id}', 'GoodsController@goodsInfo')->name('goodsInfo');//商品详情
    Route::get('/goodsComment/{id}', 'GoodsController@goodsComment')->name('goodsComment');//商品详情
    Route::post('/saveGoodsInfo', 'GoodsController@saveGoodsInfo')->name('saveGoodsInfo');//保存商品信息
    Route::post('/changeGoodsStatus', 'GoodsController@changeGoodsStatus')->name('changeGoodsStatus');//修改商品状态
    Route::post('/checkGoodsDeny', 'GoodsController@checkGoodsDeny')->name('checkGoodsDeny');//商品审核失败
    Route::post('/ajaxGetSecondCate', 'GoodsController@ajaxGetSecondCate')->name('ajaxGetSecondCate');//获取二级行业分类

    Route::get('/goodsConfig', 'GoodsController@goodsConfig')->name('goodsConfig');//商品流程配置视图
    Route::post('/postGoodsConfig', 'GoodsController@postGoodsConfig')->name('postGoodsConfig');//保存商品流程配置

    Route::get('/ShopRightsList', 'ShopController@rightsList')->name('ShopRightsList');//店铺维权列表
    Route::get('/shopRightsInfo/{id}', 'ShopController@shopRightsInfo')->name('shopRightsInfo');//店铺维权详情
    Route::post('/download', 'ShopController@download')->name('download');//下载附件
    Route::get('/ShopRightsSuccess/{id}', 'ShopController@ShopRightsSuccess')->name('ShopRightsSuccess');//处理维权成功
    Route::post('/serviceRightsSuccess', 'ShopController@serviceRightsSuccess')->name('serviceRightsSuccess');//处理雇佣维权成功
    Route::get('/ShopRightsFailure/{id}', 'ShopController@ShopRightsFailure')->name('ShopRightsFailure');//处理维权失败
    Route::get('/serviceRightsFailure/{id}', 'ShopController@serviceRightsFailure')->name('serviceRightsFailure');//处理维权失败
    Route::get('/deleteShopRights/{id}', 'ShopController@deleteShopRights')->name('deleteShopRights');//删除已经处理的维权

    Route::get('/shopOrderList', 'ShopOrderController@orderList')->name('shopOrderList');//店铺商品订单列表
    Route::get('/shopOrderInfo/{id}', 'ShopOrderController@shopOrderInfo')->name('shopOrderInfo');//店铺商品订单详情


    Route::get('/goodsServiceList','GoodsServiceController@goodsServiceList')->name('goodsServiceList');//店铺服务列表
    Route::get('/serviceOrderList','GoodsServiceController@serviceOrderList')->name('serviceOrderList');//店铺订单列表
    Route::get('/serviceOrderInfo/{id}','GoodsServiceController@serviceOrderInfo')->name('serviceOrderInfo');//店铺订单详情
    Route::get('/serviceConfig','GoodsServiceController@serviceConfig')->name('serviceConfig');//店铺流程配置
    Route::get('/serviceInfo/{id}','GoodsServiceController@serviceInfo')->name('serviceInfo');//店铺流程配置
    Route::post('/serviceConfigUpdate','GoodsServiceController@serviceConfigUpdate')->name('serviceConfigUpdate');//店铺流程配置提交
    Route::get('/serviceComments/{id}','GoodsServiceController@serviceComments')->name('serviceComments');//店铺流程配置
    Route::post('/saveServiceInfo','GoodsServiceController@saveServiceInfo')->name('saveServiceInfo');//店铺流程配置
    Route::get('/checkServiceDeny','GoodsServiceController@checkServiceDeny')->name('checkServiceDeny');//店铺服务审核失败
    Route::get('/changeServiceStatus','GoodsServiceController@changeServiceStatus')->name('changeServiceStatus');//店铺服务状态修改
    Route::get('/serviceOrderEdit/{id}','GoodsServiceController@serviceOrderEdit')->name('serviceOrderEdit');//服务订单修改
    Route::post('/serviceOrderUpdate','GoodsServiceController@serviceOrderUpdate')->name('serviceOrderUpdate');//服务订单修改提交


    Route::get('/questionList','QuestionController@getList')->name('questionList');//问答列表
    Route::get('/verify/{id}/{status}','QuestionController@verify')->name('verify');//问答验证
    Route::get('/getDetail/{id}','QuestionController@getDetail')->name('getDetail');//问答详情
    Route::post('/postDetail','QuestionController@postDetail')->name('postDetail');//问答详情修改
    Route::get('/getDetailAnswer/{id}','QuestionController@getDetailAnswer')->name('getDetailAnswer');//问答回答
    Route::get('/questionConfig','QuestionController@getConfig')->name('questionConfig');//问答配置
    Route::post('/postConfig','QuestionController@postConfig')->name('postConfig');//问答配置修改
    Route::get('/ajaxCategory/{id}','QuestionController@ajaxCategory')->name('ajaxCategory');//问答类别切换
    Route::get('/questionDelete/{id}','QuestionController@questionDelete')->name('questionDelete');//问答类别切换


    Route::get('/download/{id}','TaskController@download');

    Route::get('/promoteConfig','PromoteController@promoteConfig')->name('promoteConfig');//推广配置视图
    Route::post('/promoteConfig','PromoteController@postPromoteConfig')->name('postPromoteConfig');//推广配置

    Route::get('/promoteRelation','PromoteController@promoteRelation')->name('promoteRelation');//推广关系
    Route::get('/promoteFinance','PromoteController@promoteFinance')->name('promoteFinance');//推广财务

    Route::get('/substationConfig','SubstationController@substationConfig')->name('substationConfig');//分站配置
    Route::post('/addSubstation','SubstationController@postAdd')->name('addSubstation');//添加分站点
    Route::get('/deleteSubstation/{id}','SubstationController@deleteSub')->name('deleteSubstation');//删除分站配置
    Route::post('/postEditSubstation','SubstationController@editSub')->name('postEditSubstation');//编辑分站配置
    Route::post('/changeSubstation','SubstationController@changeSubstation')->name('changeSubstation');//改变分站状态


    //vip店铺
    Route::get('/vipConfig', 'VipShopController@vipShopConfig')->name('vipConfig');//vip首页配置
    Route::get('/vipPackageList', 'VipShopController@vipPackageList')->name('vipPackageList');//vip套餐管理
    Route::get('/addPackagePage', 'VipShopController@addPackagePage')->name('addPackagePage');//vip添加套餐管理
    Route::get('/vipInfoList', 'VipShopController@vipInfoList')->name('vipInfoList');//vip特权列表
    Route::get('/vipShopList', 'VipShopController@vipShopList')->name('vipShopList');//vip店铺
    Route::get('/vipShopAuth/{id}', 'VipShopController@vipShopAuth')->name('vipShopAuth');//vip店铺查看
    Route::get('/vipDetailsList', 'VipShopController@vipDetailsList')->name('vipDetailsList');//vip访谈列表
    Route::get('/vipDetailsAuth', 'VipShopController@vipDetailsAuth')->name('vipDetailsAuth');//vip添加访谈

    Route::post('/config/vip', 'VipShopController@vipConfigUpdate')->name('vipConfigUpdate');//保存vip配置信息
    Route::get('/packageStatus/{id}','VipShopController@updatePackageStatus')->name('packageStatusUpdate');//更改套餐状态
    Route::get('/packageDelete/{id}','VipShopController@packageDelete')->name('packageDelete');//删除套餐
    Route::get('/editPackagePage/{id}','VipShopController@editPackagePage')->name('editPackagePage');//编辑套餐页面视图
    Route::post('/addPackage','VipShopController@addPackage')->name('addPackage');//添加套餐
    Route::get('/interviewDelete/{id}','VipShopController@interviewDelete')->name('interviewDelete');//删除访谈
    Route::post('/addInterview','VipShopController@addInterview')->name('addInterview');//添加访谈
    Route::get('/editInterviewPage/{id}','VipShopController@editInterviewPage')->name('editInterviewPage');//编辑访谈视图
    Route::post('/editInterview/{id}','VipShopController@editInterview')->name('interviewUpdate');//编辑访谈
    Route::post('/endTimeUpdate','VipShopController@endTimeUpdate')->name('endTimeUpdate');//编辑购买记录的到期时间
    Route::get('/privilegesDelete/{id}','VipShopController@privilegesDelete')->name('privilegesDelete');//删除特权
    Route::get('/updateStatus/{id}','VipShopController@updateStatus')->name('statusUpdate');//启用或停用特权
    Route::get('/updateRecommend/{id}','VipShopController@updateRecommend')->name('recommendUpdate');//推荐或取消推荐特权
    Route::get('/addPrivilegesPage', 'VipShopController@addPrivilegesPage')->name('addPrivilegesPage');//添加特权视图
    Route::post('/addPrivileges','VipShopController@addPrivileges')->name('addPrivileges');//添加特权
    Route::get('/editPrivilegesPage/{id}','VipShopController@editPrivilegesPage')->name('editPrivilegesPage');//编辑特权视图
    Route::post('/updatePrivileges/{id}','VipShopController@updatePrivileges')->name('privilegesUpdate');//编辑特权
    Route::post('/editPackage/{id}','VipShopController@editPackage')->name('packageUpdate');//编辑套餐

    //工具 接入交付台
    Route::get('/keeLoad', 'KeeController@keeLoad')->name('keeLoad');//kee接入展示页面
    Route::get('/keeLoadFirst', 'KeeController@keeLoadFirst')->name('keeLoadFirst');//首次申请kee接入
    Route::get('/keeLoadAgain', 'KeeController@keeLoadAgain')->name('keeLoadAgain');//再次申请接入kee
    Route::get('/isOpenKee', 'KeeController@isOpenKee')->name('isOpenKee');//是否开启kee
});