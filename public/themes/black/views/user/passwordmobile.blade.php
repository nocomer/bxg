<div class="sign-main-bg">
    @if(count($ad))
        <a href="{!! $ad[0]['ad_url'] !!}"><img class="sign-bg1" src="{!! URL($ad[0]['ad_file']) !!}" alt=""></a>
    @else
        <img class="sign-bg1" src="{!! Theme::asset()->url('images/sign/bg1.png') !!}" alt="">
    @endif
    <div class="sign-main">
        <div class="sign-main-content">
            <div class="sign-logo">
                <a href="{!! CommonClass::homePage() !!}">
                    @if(Theme::get('site_config')['site_logo_1'])
                        <img src="{!! url(Theme::get('site_config')['site_logo_1'])!!}" alt="kppw">
                    @else
                        <img src="{!! Theme::asset()->url('images/sign/logo.png') !!}" alt="kppw">
                    @endif
                </a>
            </div>
            <div class="sign-main-container register-main-container phone-main-container">
                <div class="clearfix">
                    <p class="text-size14 text-center position-relative">
                        <a class="cor-gray66" href="/login">返回登录</a>
                        <a class="cor-green position-absolute" href="/password/email">
                            <span class="cor-graybb">&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</span>通过邮箱找回 <i class="fa fa-arrow-circle-o-right"></i>
                        </a>
                    </p>
                </div>
                <div class="password-wizard">
                    <ul class="wizard-steps hidden-xs">
                        <li class="active">
                                <span class="step">
                                    <img src="{!! Theme::asset()->url('images/sign/phone1.png') !!}" alt="">
                                </span>
                            <span class="title">输入手机</span>
                        </li>

                        <li class="">
                                <span class="step">
                                    <img src="{!! Theme::asset()->url('images/sign/phone2.png') !!}" alt="">
                                </span>
                            <span class="title">重置密码</span>
                        </li>

                        <li class="">
                                <span class="step">
                                    <img src="{!! Theme::asset()->url('images/sign/phone3.png') !!}" alt="">
                                </span>
                            <span class="title">完成</span>
                        </li>
                    </ul>
                </div>
                <div class="space"></div>
                <div class="clearfix">
                    <form class="login-form passwordform form-horizontal" method="post" action="{!! url('password/mobile') !!}">
                        {!! csrf_field() !!}
                        <div class="userPassword user-position-relative">
                            <span class="block clearfix cor-gray99 user-position">请输入注册手机号</span>
                                <span class="block input-icon input-icon-right login-error_wrong Validform-wrong-red Validform-wrong-red-height">
                                    <input type="text" name="mobile" id="form-field-2" class="inputbd form-control inputxt bor-radius2 inputLength "
                                           placeholder=""  datatype="m" value="{{old('mobile')}}" nullmsg="请输入注册手机号" errormsg="手机号格式不对！" autocomplete="off" disableautocomplete>

                                    <span class="mobile_check Validform_checktip validform-login-form login-validform-static user-position-valid">
                                        <span class="login-red"></span>
                                    </span>
                                </span>
                        </div>

                        <div class="space-14"></div>
                        <!-- 滑块验证 -->
                        <div class="form-group step-validform" >
                            <div class="col-xs-12 col-lg-8 col-md-9 col-sm-10">
                                <span class="block input-icon input-icon-right" style="width:100%;">
                                <div id="embed-captcha" data-info="1"></div>
                                <p id="wait" class="show">正在加载验证码......</p>
                                <p id="notice" class="hide">请先完成验证</p>
                                </span>
                            </div>
                        </div>

                        <div class="space-14"></div>
                        <div class="userCode user-position-relative">
                            <span class="block clearfix cor-gray99 user-position">请输入验证码</span>
                            <input type="text" id="form-field-3" name="code" class="form-control inputxt inputbd inputLength" placeholder="" nullmsg="请输入验证码" datatype="*" errormsg="请输入验证码">
                                <span class="Validform_checktip validform-login-form login-validform-static">
                                    <span class="login-red"></span>
                                </span>
                            <input type="button" token="{{csrf_token()}}" class="btn btnCode btn-xs" onclick="sendPasswordCode()" value="（获取验证码）" id="sendMobileCode">
                            @if($errors->first('code'))<span class="Validform_checktip Validform_wrong">{{$errors->first('code')}}</span>@endif
                        </div>

                        <div class="space-4"></div>
                        <div>
                            <button type="submit" class="btn-green text-size16">
                                下一步
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{!! Theme::asset()->container('specific-css')->usePath()->add('validform-css', 'plugins/jquery/validform/css/style.css') !!}
{!! Theme::asset()->container('specific-js')->usePath()->add('validform-css', 'plugins/jquery/validform/js/Validform_v5.3.2_min.js') !!}
        <!-- 拖拽验证 -->
{!! Theme::asset()->container('specific-js')->usepath()->add('gt', 'js/user/gt.js') !!}
{!! Theme::asset()->container('custom-js')->usePath()->add('huakuaiyanzheng', 'js/user/huakuaiyanzheng.js') !!}

{!! Theme::asset()->container('custom-js')->usepath()->add('payphoneword','js/doc/payphoneword.js') !!}
{!! Theme::asset()->container('custom-js')->usePath()->add('main-js', 'js/main.js') !!}
