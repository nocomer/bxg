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
            <div class="sign-main-container fail-main-container">
                <div class="space-12"></div>
                <div class="text-center">
                    <img src="{!! Theme::asset()->url('images/sign/bg3.png') !!}" alt="">
                </div>
                <div class="space-16"></div>
                <div class="text-center">
                    <h4 class="text-size16">恭喜您注册成功！</h4>
                    <p class="cor-gray66">页面将在<span id="show"></span>秒后自动跳转到<a class="text-under" href="{!! CommonClass::homePage() !!}">首页</a></p>

                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var t=5;//设定跳转的时间
    setInterval("refer()",1000); //启动1秒定时
    function refer(){
        if(t==0){
            window.location.href="/"; //#设定跳转的链接地址
        }
        if(t>0) {
            document.getElementById('show').innerHTML = "" + t + ""; // 显示倒计时

        }
        t--; // 计数器递减
    }
</script>
