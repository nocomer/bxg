<div class="sign-main-bg">
    <img class="sign-bg1" src="{!! Theme::asset()->url('images/sign/bg1.png') !!}" alt="">
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
                        已有帐号？ 请<a href="{!! url('login') !!}" class="cor-gray66">登录</a>
                    </p>

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
                        <span class="cor-gray66">我们已向<a class="cor-blue167 text-under" href="javascript:viewMail('{!! $emailType !!}');void(0)">{!! $email !!}</a>发送了验证邮件，请点击邮件中的
                            链接完成邮箱验证。</span>
                    </div>
                </div>

            </div>
            <div class="validateemail-footer cor-gray66 position-relative">
                <p>未收到邮件？</p>
                <p>1、试试检查垃圾邮件、订阅邮件目录；</p>
                <input type="hidden" id="reEmail" value="{!! Crypt::encrypt($email) !!}">
                <p>2、若长时间未收到邮件，可<a id="reset" class="cor-green">重新发送</a>。</p>
            </div>
        </div>
    </div>
</div>

{!! Theme::asset()->container('specific-js')->usePath()->add('cookie', 'plugins/jquery/cookies.js') !!}
{!! Theme::asset()->container('custom-js')->usePath()->add('active', 'js/active.js') !!}