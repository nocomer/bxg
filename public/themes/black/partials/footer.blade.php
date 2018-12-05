<div class="ifooter-bg">
    <div class="container">
        <div class="space-8"></div>
        <div class="row">
            <div class="col-sm-6">
                <p>
                    @if(!empty(Theme::get('article_cate')))
                        @foreach(Theme::get('article_cate') as $item)
                    <a target="_blank" href="/article/aboutUs/{!! $item['id'] !!}">{!! $item['cate_name'] !!}</a>
                        @endforeach
                    @endif
                </p>
                <p>{!! config('kppw.kppw_powered_by') !!}{!! config('kppw.kppw_version') !!}
                    {!! Theme::get('site_config')['copyright'] !!}{!! Theme::get('site_config')['record_number'] !!}</p>
            </div>
            <div class="col-sm-3">
                <p>服务热线：{!! Theme::get('site_config')['phone'] !!}       邮箱：{!! Theme::get('site_config')['Email'] !!}</p>
                <p>地址：{!! Theme::get('site_config')['company_address'] !!}</p>
            </div>
            <div class="col-sm-3">
                <div class="ifooter-about">
                    <div class="foc-ewm">
                        <div class="foc-ewm-arrow1"></div>
                        <div class="foc-ewm-arrow2"></div>
                        <img src="{!! url(Theme::get('site_config')['wechat']['wechat_pic']) !!}" alt="" width="100" height="100">
                    </div>

                    关注我们：
                    <a class="ifooter-wx" href=""><i></i></a>
                    @if(Theme::get('site_config')['tencent']['tencent_switch'] == 1)
                    <a class="ifooter-qq" href="{!! Theme::get('site_config')['tencent']['tencent_url'] !!}"><i></i></a>
                    @endif
                    @if(Theme::get('site_config')['sina']['sina_switch'] == 1)
                    <a class="ifooter-wb" href="{!! Theme::get('site_config')['sina']['sina_url'] !!}"><i></i></a>
                    @endif
                </div>
            </div>
        </div>
        <div class="space-2"></div>
    </div>
</div>
<a class="sidefix-onep" href="{{url('user/index')}}">个人中心</a>
<a class="sidefix-put" href="{{url('task/create')}}">发布需求</a>
{{--
<a class="sidefix-put sidefix-state" href="{{url('task/create')}}">最新动态</a>
--}}

<div class="go-top" id="go-top">
    <div class="uc-2vm u-hov">
        <span class="cor-white">意见反馈</span>
        <form class="form-horizontal" action="/bre/feedbackInfo" method="post" id="complain">
            {!! csrf_field() !!}
            <div class="u-pop dn clearfix" style="display: none;">
                <input type="text" name="uid" style="display:none">
                <h2 class="mg-margin text-size12 cor-gray51 no-margin-top">一句话点评</h2>
                <div class="space-4"></div>
                <textarea class="form-control" rows="3" name="desc" placeholder="期待您的一句话点评，不管是批评、感谢还是建议，我们都将会细心聆听，及时回复" @if(!empty(Theme::get('complaints_user'))) value="{!! Theme::get('complaints_user')->uid !!}"@endif>

                </textarea>

                <div class="space-4"></div>
                <input style="height: 36px" type="text" name="phone" placeholder="填写手机号"  @if(!empty(Theme::get('complaints_user'))) value="{!! Theme::get('complaints_user')->mobile !!}" readonly="readonly" @endif >

                <button type="submit" class="btn-blue btn btn-sm btn-primary">提交</button>
                <div class="arrow">
                    <div class="arrow-sanjiao"></div>
                    <div class="arrow-sanjiao-big"></div>
                </div>
            </div>
        </form>
    </div>
    <div class="feedback u-hov">
        <span class="cor-white">联系客服</span>
        <div class="dn dnd" style="display: none;">
            <h2 class="mg-margin text-size12 cor-gray51 no-margin-top">在线时间：09:00 -18:00</h2>
            <div class="space-4"></div>
            <div>
                <a href="{!! CommonClass::contactClient(Theme::get('basis_config')['qq']) !!}" target="_blank"><img src="{!! Theme::asset()->url('images/pa.jpg') !!}" alt=""></a>
            </div>
            <div class="hr"></div>
            <div class="iss-ico1">
                <p class="cor-gray51 mg-margin">全国免长途电话：</p>
                <p class="text-size20 cor-gray51">{!! Theme::get('site_config')['phone'] !!}</p>
            </div>
            <div class="arrow">
                <div class="arrow-sanjiao feedback-sanjiao"></div>
                <div class="arrow-sanjiao-big feedback-sanjiao-big"></div>
            </div>
        </div>
    </div>
    <a href="javascript:;" class="go u-hov">返回顶部</a>
</div>
{!! Theme::get('site_config')['statistic_code'] !!}
{!! Theme::widget('popup')->render() !!}
{{--{!! Theme::widget('statement')->render() !!}--}}
@if(Theme::get('is_IM_open') == 1)
{!! Theme::widget('im',array('attention' => Theme::get('attention'),'ImIp' => Theme::get('basis_config')['IM_config']['IM_ip'],
'ImPort' => Theme::get('basis_config')['IM_config']['IM_port']))->render() !!}
@endif