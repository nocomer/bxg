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
            <div class="sign-main-container fail-main-container">
                <div class="space-12"></div>
                <div class="text-center">
                    <img src="{!! Theme::asset()->url('images/sign/bg2.png') !!}" alt="">
                </div>
                <div class="space-16"></div>
                <div class="text-center">
                    <h4 class="text-size16">很遗憾，验证失败！</h4>
                    <p class="cor-gray66">验证链接已过期，请重新<a class="cor-green" href="javascript:;">发送邮件</a>或者重新<a class="cor-green" href="{!! url('register') !!}">注册</a>。</p>
                </div>
            </div>
        </div>
    </div>
</div>