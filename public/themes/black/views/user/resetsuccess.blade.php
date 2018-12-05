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
            <div class="sign-main-container register-main-container phone-main-container email-main-container">
                <div class="clearfix">
                    <p class="text-size14 text-center position-relative">
                        <a class="cor-gray66" href="/login">返回登录</a>
                        <a class="cor-green position-absolute" href="/password/mobile">
                            <span class="cor-graybb">&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</span>通过手机找回 <i class="fa fa-arrow-circle-o-right"></i>
                        </a>
                    </p>
                </div>
                <div class="password-wizard">
                    <ul class="wizard-steps hidden-xs">
                        <li class="active">
                                <span class="step">
                                    <img src="{!! Theme::asset()->url('images/sign/phone1.png') !!}" alt="">
                                </span>
                            <span class="title">输入邮箱</span>
                        </li>

                        <li class="active">
                                <span class="step">
                                    <img src="{!! Theme::asset()->url('images/sign/phone4.png') !!}" alt="">
                                </span>
                            <span class="title">验证邮箱</span>
                        </li>

                        <li class="active">
                                <span class="step">
                                    <img src="{!! Theme::asset()->url('images/sign/phone5.png') !!}" alt="">
                                </span>
                            <span class="title">重置密码</span>
                        </li>
                        <li class="active">
                                <span class="step">
                                    <img src="{!! Theme::asset()->url('images/sign/phone7.png') !!}" alt="">
                                </span>
                            <span class="title">完成</span>
                        </li>
                    </ul>
                </div>
                <div class="space"></div>
                <div class="clearfix">
                    <div class="space-12"></div>
                    <div class="text-center">
                        <img src="{!! Theme::asset()->url('images/sign/bg3.png') !!}" alt="">
                    </div>
                    <div class="space-16"></div>
                    <div class="text-center">
                        <h4 class="text-size16">恭喜您，密码设置成功!！</h4>
                        <p class="cor-gray66">页面将在<span id="show"></span>秒后自动跳转到<a class="cor-green" href="{!! url('login') !!}">登录</a></p>
                    </div>
                    <div class="space"></div>

                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var t=5;//设定跳转的时间
    setInterval("refer()",1000); //启动1秒定时
    function refer(){
        if(t==1){
            location="{!! url('login') !!}"; //#设定跳转的链接地址
        }
        document.getElementById('show').innerHTML=""+t+""; // 显示倒计时
        t--; // 计数器递减
    }
</script>
