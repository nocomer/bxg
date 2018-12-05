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

                        <li class="">
                                <span class="step">
                                    <img src="{!! Theme::asset()->url('images/sign/phone3.png') !!}" alt="">
                                </span>
                            <span class="title">重置密码</span>
                        </li>
                        <li class="">
                                <span class="step">
                                    <img src="{!! Theme::asset()->url('images/sign/phone6.png') !!}" alt="">
                                </span>
                            <span class="title">完成</span>
                        </li>
                    </ul>
                </div>
                <div class="space"></div>
                <div class="clearfix">
                    <div class="space-12"></div>
                    <div class="text-center">
                        <img src="{!! Theme::asset()->url('images/sign/phone8.png') !!}" alt="">
                    </div>
                    <div class="space-16"></div>
                    <div class="">
                        <h4 class="text-center text-size16 cor-green">验证邮件发送成功！</h4>
                        <span class="cor-gray66">我们已向 <a href="javascript:viewMail('{!! $emailType !!}');void(0)" class="cor-blue167">{!! $email !!}</a>发送了验证邮件，请点击邮件中的
                            链接完成邮箱验证。</span>
                    </div>
                </div>
            </div>
            <div class="validateemail-footer cor-gray66 position-relative">
                <p>未收到邮件？</p>
                <p>1、试试检查垃圾邮件、订阅邮件目录；</p>
                <p>2、若长时间未收到邮件，可<a id="reSendEmail" href="javascript:reSendEmail('{!! Crypt::encrypt($email) !!}');void(0)" class="cor-green">重新发送。</a></p>
            </div>
        </div>
    </div>
</div>

{!! Theme::asset()->container('custom-js')->usePath()->add('active', 'js/active.js') !!}