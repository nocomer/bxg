<body>
<div class="sign-main-bg">
    @if(count($ad))
        <a href="{!! $ad[0]['ad_url'] !!}"><img src="{!! URL($ad[0]['ad_file']) !!}" class="sign-bg1" alt=""></a>
    @else
    <img class="sign-bg1" src="{!! Theme::asset()->url('images/sign/bg1.png') !!}" alt="">
    @endif
    <div class="sign-main">
        <div class="sign-main-content">
            <div class="sign-logo">
                <a href="{!! CommonClass::homePage() !!}">
                    @if(Theme::get('site_config')['site_logo_1'])
                        <img src="{!! url(Theme::get('site_config')['site_logo_1'])!!}" alt="">
                    @else
                        <img src="{!! Theme::asset()->url('images/sign/logo.png') !!}" alt="">
                    @endif
                </a>
            </div>
            <div class="sign-main-container register-main-container">
                <div class="widget-main loginmain bor-radius2 loginmain-container">
                    <ul class="clearfix logintabtit">
                        <li class="col-sm-6 col-xs-6 text-center active">
                            <a href=" #phone" class=" lighter bigger text-left mg-margin text-size16 cor-gray66" data-toggle="tab" aria-expanded="true">手机号码注册</a>
                        </li>
                        <li class="col-sm-6 col-xs-6 text-center ">
                            <a href="#email" class=" lighter bigger text-left mg-margin text-size16 cor-gray66" data-toggle="tab" aria-expanded="false">邮箱注册</a>
                        </li>
                    </ul>
                    <div class="tab-content no-padding">
                        <div id="phone" class="tab-pane fade active in">
                            <form class="login-form registerform" method="post" action="{!! url('register/phone') !!}" >
                                {!! csrf_field() !!}
                                <input type="hidden" name="from_uid" value="{!! $from_uid !!}">
                                <div class="space"></div>
                                <div class="user user-position-relative">
                                    <span class="block clearfix cor-gray99 user-position">用户名</span>
                                    <input type="text" class="form-control inputxt inputbd inputLength " placeholder="" name="username" value="" ajaxurl="{!! url('checkUserName') !!}" nullmsg="请输入用户名"  datatype="*4-15" errormsg="用户名长度为4到15位字符" value="{{old('username')}}">
                                    <span class="Validform_checktip validform-login-form login-validform-static user-position-valid">
                                        <span class="login-red"></span>
                                    </span>
                                </div>

                                <!-- 拖拽滑块验证 -->
                                <div class="space-12"></div>
                                <!-- 拖拽滑块验证 -->
                                <div class="space-12"></div>
                                <span class="block input-icon input-icon-right" style="width:100%;">
                                    <div id="embed-captcha" data-info="1"></div>
                                    <p id="wait" class="show">正在加载验证码......</p>
                                    <p id="notice" class="hide">请先完成验证</p>
                                </span>

                                <div class="space-12"></div>
                                <div class="userPassword user-position-relative">
                                    <span class="block clearfix cor-gray99 user-position">请输入常用手机号码</span>
                                    <span class="block input-icon input-icon-right login-error_wrong Validform-wrong-red Validform-wrong-red-height">
                                        <input type="text" class="inputbd form-control inputxt bor-radius2 inputLength " placeholder="" ajaxurl="{!! url('checkMobile') !!}" name="mobile" nullmsg="请输入常用手机号码" datatype="m" errormsg="手机号格式错误！" value="{{old('mobile')}}">

                                        <span class="mobile_check Validform_checktip validform-login-form login-validform-static user-position-valid">
                                            <span class="login-red"></span>
                                        </span>
                                    </span>
                                </div>
                                <div class="space-12"></div>
                                <div class="userCode user-position-relative">
                                    <span class="block clearfix cor-gray99 user-position">请输入验证码</span>
                                    <input type="text" class="form-control inputxt inputbd inputLength " placeholder="" name="code" value="" nullmsg="请输入验证码" datatype="*" errormsg="请输入验证码">
                                    <span class="position-absolute-right Validform_checktip validform-login-form login-validform-static {{ ($errors->first('code'))?'Validform_wrong':'' }}" >
                                        <span class="login-red">{!! $errors->first('code') !!}</span>
                                    </span>
                                    <input type="button" class="btn btnCode btn-xs" value="（获取验证码）" token="{{csrf_token()}}" onclick="sendRegisterCode()" class="btn btn-white btn-primary c-btntime" id="sendMobileCode">
                                </div>
                                <div class="space-12"></div>
                                <div class="userPassword user-position-relative">
                                    <span class="block clearfix cor-gray99 user-position">请输入密码</span>
                                    <span class="block input-icon input-icon-right login-error_wrong Validform-wrong-red Validform-wrong-red-height">
                                        <input type="password" class="inputbd form-control inputxt bor-radius2 inputLength " placeholder="" name="password" nullmsg="请输入您的密码" datatype="*6-16" errormsg="密码长度为6-16位字符">

                                        <span class="Validform_checktip validform-login-form login-validform-static user-position-valid">
                                            <span class="login-red"></span>
                                        </span>

                                    </span>
                                </div>
                                <div class="space-12"></div>
                                <div class="userPassword user-position-relative">
                                    <span class="block clearfix cor-gray99 user-position">请确认输入密码</span>
                                    <span class="block input-icon input-icon-right login-error_wrong Validform-wrong-red Validform-wrong-red-height">
                                        <input type="password" class="inputbd form-control inputxt bor-radius2 inputLength " placeholder="" name="confirm_password" recheck="password" nullmsg="请输入确认密码" datatype="*" errormsg="两次密码不一致">

                                        <span class="Validform_checktip validform-login-form login-validform-static user-position-valid">
                                            <span class="login-red"></span>
                                        </span>

                                    </span>
                                </div>
                                <div class="space-8"></div>
                                <div class="clearfix">
                                    <label class="sign-checklabel">
                                        <input name="form-field-checkbox" type="checkbox" class="ace" checked="checked" datatype="*" nullmsg="请先阅读并同意">
                                        <span class="sign-check text-size12 cor-gray66 lbl">&nbsp;&nbsp; 我已阅读并同意<a class="text-under" target="_blank" href="/bre/agree/register">《{!! $agree->name !!}》</a></span>
                                        <span class="Validform_checktip validform-login-form login-validform-static">
                                            <span class="login-red"></span>
                                        </span>
                                    </label>
                                    <a href="{!! url('password/email') !!}" class="pull-right text-size12 cor-gray02">忘记密码？</a>
                                </div>
                                <div class="space-8"></div>
                                <div>
                                    <button type="submit" class="btn-green text-size16">
                                        立即注册
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div id="email" class="tab-pane fade ">
                            <form class="login-form registerform" method="post" action="{!! url('register') !!}" >
                                {!! csrf_field() !!}
                                <div class="space"></div>
                                <div class="user user-position-relative">
                                    <span class="block clearfix cor-gray99 user-position">请输入用户名</span>
                                    <input type="text" class="form-control inputxt inputbd inputLength" placeholder="" name="username" value="" nullmsg="请输入用户名"  ajaxurl="{!! url('checkUserName') !!}" datatype="*4-15"  errormsg="用户名长度为4到15位字符" value="{{old('username')}}">
                                    <span class="Validform_checktip validform-login-form login-validform-static user-position-valid">
                                        <span class="login-red"></span>
                                    </span>
                                </div>
                                <div class="space-12"></div>
                                <div class="userPassword user-position-relative">
                                    <span class="block clearfix cor-gray99 user-position">请输入邮箱</span>
                                    <span class="block input-icon input-icon-right login-error_wrong Validform-wrong-red Validform-wrong-red-height">
                                        <input type="email" class="inputbd form-control inputxt bor-radius2 inputLength " placeholder=""  ajaxurl="{!! url('checkEmail') !!}" name="email" datatype="e" nullmsg="请输入邮箱帐号" errormsg="邮箱地址格式不对！" value="{{old('email')}}">

                                        <span class="Validform_checktip validform-login-form login-validform-static user-position-valid">
                                            <span class="login-red"></span>
                                        </span>
                                    </span>
                                </div>
                                <div class="space-12"></div>
                                <div class="userPassword user-position-relative">
                                    <span class="block clearfix cor-gray99 user-position">请输入密码</span>
                                    <span class="block input-icon input-icon-right login-error_wrong Validform-wrong-red Validform-wrong-red-height">
                                        <input type="password" class="inputbd form-control inputxt bor-radius2 inputLength " placeholder="" name="password" nullmsg="请输入您的密码" datatype="*6-16" errormsg="请输入6-16个字符">

                                        <span class="Validform_checktip validform-login-form login-validform-static user-position-valid">
                                            <span class="login-red"></span>
                                        </span>

                                    </span>
                                </div>
                                <div class="space-12"></div>
                                <div class="userPassword user-position-relative">
                                    <span class="block clearfix cor-gray99 user-position">请确认输入密码</span>
                                    <span class="block input-icon input-icon-right login-error_wrong Validform-wrong-red Validform-wrong-red-height">
                                        <input type="password" class="inputbd form-control inputxt bor-radius2 inputLength " placeholder="" name="confirmPassword" recheck="password" nullmsg="请输入确认密码" datatype="*" errormsg="两次密码不一致">

                                        <span class="Validform_checktip validform-login-form login-validform-static user-position-valid">
                                            <span class="login-red"></span>
                                        </span>

                                    </span>
                                </div>
                                <div class="space-8"></div>
                                <div class="clearfix">
                                    <label class="sign-checklabel">
                                        @if(!empty($agree))
                                        <input  type="checkbox" class="ace" name="agree" checked="checked" datatype="*" nullmsg="请先阅读并同意">
                                        <span class="sign-check text-size12 cor-gray66 lbl">&nbsp;&nbsp; 我已阅读并同意 <a class="text-under" target="_blank" href="/bre/agree/register">《{!! $agree->name !!}》</a></span>
                                        <span class="Validform_checktip validform-login-form login-validform-static">
                                            <span class="login-red"></span>
                                        </span>
                                        @endif
                                    </label>
                                </div>
                                <div class="space-8"></div>
                                <div>
                                    <button type="submit" class="btn-green text-size16">
                                        立即注册
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="space"></div>
            <div class="text-center cor-gray52">
                <span class="cor-white">已有账号，</span><a class="cor-green" href="{!! url('login') !!}" >直接登录</a>
            </div>
        </div>
    </div>
</div>

</body>
{!! Theme::asset()->container('specific-css')->usePath()->add('validform-css', 'plugins/jquery/validform/css/style.css') !!}
{!! Theme::asset()->container('specific-js')->usePath()->add('validform-js', 'plugins/jquery/validform/js/Validform_v5.3.2_min.js') !!}

        <!-- 拖拽验证 -->
{!! Theme::asset()->container('specific-js')->usepath()->add('register', 'js/user/gt.js') !!}


{!! Theme::asset()->container('custom-js')->usePath()->add('custom-validform-js', 'js/auth.js') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('payphoneword','js/doc/payphoneword.js') !!}

