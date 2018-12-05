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

                        <li class="active">
                                <span class="step">
                                    <img src="{!! Theme::asset()->url('images/sign/phone4.png') !!}" alt="">
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
                    <form class="login-form registerform form-horizontal " method="post" action="{!! url('password/mobileReset') !!}">
                        {!! csrf_field() !!}
                        <div class="userPassword user-position-relative">
                            <span class="block clearfix cor-gray99 user-position">新密码</span>
                            <span class="block input-icon input-icon-right login-error_wrong Validform-wrong-red Validform-wrong-red-height">
                                <input type="password" name="password"  class="inputbd form-control inputxt bor-radius2 inputLength" placeholder=""  nullmsg="请输入您的密码" datatype="*6-18" errormsg="请输入6-18个字符，支持英文、数字" autocomplete="off" disableautocomplete>

                                <span class="Validform_checktip validform-login-form login-validform-static user-position-valid">
                                    <span class="login-red"></span>
                                </span>

                            </span>

                        </div>
                        <div class="space-12"></div>
                        <div class="userPassword user-position-relative">
                            <span class="block clearfix cor-gray99 user-position">请确认新密码</span>
                            <span class="block input-icon input-icon-right login-error_wrong Validform-wrong-red Validform-wrong-red-height">
                                <input type="password" name="confirmPassword" class="inputbd form-control inputxt bor-radius2 inputLength"
                                       placeholder=""  nullmsg="请确认新密码" datatype="*6-18" errormsg="两次密码不一致" autocomplete="off" disableautocomplete>

                                <span class="Validform_checktip validform-login-form login-validform-static user-position-valid">
                                    <span class="login-red"></span>
                                </span>

                            </span>

                        </div>
                        <div class="space"></div>
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
{!! Theme::asset()->container('custom-js')->usePath()->add('auth-js', 'js/resetpassword.js') !!}