<body>
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
                        <img src="{!! url(Theme::get('site_config')['site_logo_1'])!!}" alt="">
                    @else
                    <img src="{!! Theme::asset()->url('images/sign/logo.png') !!}" alt="">
                    @endif
                </a>
            </div>
            <div class="sign-main-container email-main-container">
                <div class="sign-main-head clearfix">
                    <span class="pull-left title">账号密码登录</span> <a href="{!! url('register') !!}" class="text-size14 pull-right cor-gray02">免费注册</a>
                </div>
                <form class="login-form" method="post" action="{!! url('login') !!}" >
                    {!! csrf_field() !!}
                    <div class="user user-position-relative">
                        <span class="block clearfix cor-gray99 user-position">用户名/邮箱/手机号</span>
                        <input type="text" class="form-control inputxt inputbd inputLength" placeholder="" name="username" value="{!! old('username') !!}" nullmsg="请输入您的账号" datatype="*" errormsg="请输入您的账号" autocomplete="off" disableautocomplete>
                        <span class="Validform_checktip validform-login-form login-validform-static user-position-valid">
                            <span class="login-red">{!! $errors->first('username') !!}</span>
                        </span>
                    </div>
                    <div class="space-16"></div>
                    <div class="userPassword user-position-relative">
                        <span class="block clearfix cor-gray99 user-position">请输入密码</span>
                        <span class="block input-icon input-icon-right login-error_wrong Validform-wrong-red Validform-wrong-red-height">
                            <input type="password" class="inputbd form-control inputxt bor-radius2 inputLength" placeholder="" name="password" nullmsg="请输入您的密码" datatype="*6-16" errormsg="请输入6-12个字符，支持英文、数字" autocomplete="off" disableautocomplete>

                            <span class="Validform_checktip validform-login-form login-validform-static user-position-valid">
                                <span class="login-red">{!! $errors->first('password') !!}</span>
                            </span>
                        </span>
                    </div>
                    <div class="space-10"></div>
                    @if(!empty($errors->first('password')) || !empty($errors->first('code')))
                        {{--<div class="clearfix codeImg">
                            <label class="inline pull-left">
                                <input type="text" class="form-control form-input-code" placeholder="验证码" name="code">

                                <div class="error_wrong">{!! $errors->first('code') !!}</div>
                            </label>
                            <img src="{!! $code !!}" alt="" class="pull-right" onclick="flushCode(this)">
                        </div>--}}
                        {{--<div class="user codeImg">
                            <input type="text" class="form-control form-input-code inputxt inputbd" placeholder="验证码" name="username" name="code">
                            <span class="Validform_checktip validform-login-form login-validform-static">
                                <span class="login-red">{!! $errors->first('code') !!}</span>
                            </span>
                            <img src="{!! $code !!}" alt="" class="pull-right" onclick="flushCode(this)">
                        </div>--}}
                    <div class="uesr">
                        <span class="position-relative block login-error_wrong Validform-wrong-red Validform-wrong-red-height">
                            <input type="text" class="form-control form-input-code inputxt inputbd" placeholder="请输入验证码" name="password" nullmsg="请输入验证码" datatype="*" errormsg="请输入验证码">
                            <div class="imgCode position-absolute">
                                <img class="register-codeimg img-responsive" src="{!! $code !!}" id="codeimg" onclick="flushCode(this)">
                            </div>
                            <span class="Validform_checktip validform-login-form login-validform-static">
                                <span class="login-red">{!! $errors->first('code') !!}</span>
                            </span>
                        </span>
                    </div>
                    @endif
                  {{--  <div class="space-10"></div>--}}
                    <div class="clearfix">
                        <label class="sign-checklabel">
                            <input name="form-field-checkbox" type="checkbox" class="ace" name="remember">
                            <span class="sign-check text-size12 cor-gray66 lbl">&nbsp;&nbsp;记住密码</span>
                        </label>
                        <a href="{!! url('password/email') !!}" class="pull-right text-size12 cor-gray02">忘记密码？</a>
                    </div>
                    <div class="space-8"></div>
                    <div>
                        <button type="submit" class="btn-green text-size16">
                            登 录
                        </button>
                    </div>
                </form>
            </div>
            <div class="space"></div>
            <div class="text-center cor-gray52 sign-main-footer">
                <span class="cor-graybb">第三方登录</span>
                @if(isset($oauth['wechat_api']['status']))
                <a href="{!! url('oauth/weixinweb') !!}" class="fa fa-weixin cor-graybb text-size16"></a>
                @endif
                @if(isset($oauth['qq_api']['status']))
                <a href="{!! url('oauth/qq') !!}" class="fa fa-qq cor-graybb text-size16"></a>
                @endif
                @if(isset($oauth['sina_api']['status']))
                <a href="{!! url('oauth/weibo') !!}" class="fa fa-weibo cor-graybb text-size16"></a>
                @endif
            </div>
        </div>
    </div>
</div>
<!--<script>
   function signBg(){
       var w = document.documentElement.scrollWidth;
       var h = document.documentElement.scrollHeight;
       $('.sign-bg1').width(w);
       $('.sign-bg1').height(h);
       console.log(w)
   }
   signBg();

</script>-->
{{--<script type="text/javascript">
    (function($) {
        var cache = [];
        $.preLoadImages = function() {
            var args_len = arguments.length;
            for (var i = args_len; i--;) {
                var cacheImage = document.createElement('img');
                cacheImage.src = arguments[i];
                cache.push(cacheImage);
            }
        }
    })(jQuery)
    $.preLoadImages('../../assets/images/sign/bg1.png','images/sign/logo.png','images/sample3.jpg');
</script>--}}
</body>
{!! Theme::asset()->container('custom-js')->usePath()->add('main-js', 'js/main.js') !!}
{!! Theme::asset()->container('specific-css')->usepath()->add('validform-style','plugins/jquery/validform/css/style.css') !!}
{!! Theme::asset()->container('specific-js')->usepath()->add('validform','plugins/jquery/validform/js/Validform_v5.3.2_min.js') !!}