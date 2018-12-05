<div class="header-bg">
    <div class="container">
        <div class="banner-wrap clearfix">
            <div id="carousel-example-generic" class="carousel slide banner-bar pull-left" data-ride="carousel">
                <!-- Indicators -->
                @if(!empty(Theme::get('banner')))
                <ol class="carousel-indicators">
                    @foreach(Theme::get('banner') as $k => $v)
                        <li data-target="#carousel-example-generic" data-slide-to="{!! $k !!}" @if($k == 0) class="active" @endif></li>
                    @endforeach
                </ol>
                @else
                <ol class="carousel-indicators">
                    <li data-target="#carousel-example-generic" data-slide-to="0" class="active"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="1"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="2"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="3"></li>
                    <li data-target="#carousel-example-generic" data-slide-to="4"></li>
                </ol>
                @endif
                <!-- Wrapper for slides -->
                <div class="carousel-inner" role="listbox">
                    @if(!empty(Theme::get('banner')))
                        @foreach(Theme::get('banner') as $key => $value)
                            <div class="item @if($key == 0)active @endif ">
                                <a href="{{$value['ad_url']}}"><img src="{!! url($value['ad_file']) !!}" alt="..."></a>
                            </div>
                        @endforeach
                    @else
                    <div class="item active">
                        <img src="/themes/black/assets/images/index/banner-bg.png" alt="...">
                    </div>
                    <div class="item">
                        <img src="/themes/black/assets/images/index/banner-bg.png" alt="...">
                    </div>
                    <div class="item">
                        <img src="/themes/black/assets/images/index/banner-bg.png" alt="...">
                    </div>
                    <div class="item">
                        <img src="/themes/black/assets/images/index/banner-bg.png" alt="...">
                    </div>
                    <div class="item">
                        <img src="/themes/black/assets/images/index/banner-bg.png" alt="...">
                    </div>
                    @endif
                </div>

                <!-- Controls -->
                <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
                    <span class="glyphicon glyphicon-chevron-right"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
            <div class="pull-right banner-right">
                <div class="banner-list">
                    <div class="clearfix banner-listhead">
                        <div class="pull-left">动态</div>
                        <!--<div class="pull-right"><a href="">更多></a></div>-->
                    </div>
                    <div class="banner-listhide maquee">
                        <ul class="renav">
                            @if($active)
                                @foreach($active as $k2 => $v2)
                            <li class="clearfix">
                                <div class="pull-left banner-lititle p-space"><span class="cor-gray99"><a
                                                href="/bre/serviceCaseList/{{$v2['uid']}}" class="cor-gray99">{{$v2['name']}}</a> 接受了</span>
                                    <a class="cor-gray33" href="/task/{{$v2['task_id']}}">{{str_limit($v2['title'], 25)}}</a></div>
                                <div class="pull-left"><span class="banner-litime">@if(intval((time() - strtotime($v2['created_at']))/60) > 60)
                                            1小时前 @else {{intval((time() - strtotime($v2['created_at']))/60)}}
                                            分钟前 @endif</span></div>
                            </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                    <a class="banner-listdown text-size16" href="javascript:;"><i class="fa fa-angle-double-down"></i></a>
                </div>
                <div class="clearfix banner-headline">
                    @if(!empty($article))
                    <div class="pull-left"><img src="{{url($article[0]['recommend_pic'])}}" alt=""></div>
                    <div class="pull-left banner-linewrap">
                        <div class="banner-linebor">
                            <div class="p-space"><a href="{{$article[0]['url']}}" class="cor-gray33">{{str_limit($article[0]['title'], 25)}}</a></div>
                            <div class="space-4"></div>
                            <p class="cor-gray99 text-size12 p-space">{{str_limit($article[0]['summary'], 35)}}</p>
                            <div class="space-4"></div>
                        </div>
                        <div class="banner-linemore">
                            <div class="space-6"></div>
                            <div class="p-space"><a href="{{$article[1]['url']}}" class="cor-gray33">{{str_limit($article[1]['title'], 25)}}</a></div>
                            <div class="space-6"></div>
                            <p><a class="cor-gray66 text-size12" href="/article">更多></a></p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div></div>
<section>
    <div class="container">
        <div class="space-20"></div>
        <div class="text-size18 cor-gray24">服务商/<span class="text-size16">SERVICE PROVIDER</span><a href="/bre/service" class="hovA cor-orange text-size14 pull-right">更多></a></div>
        <div class="space-10"></div>
        <div class="index-service">
            <ul id="da-thumbs" class="da-thumbs clearfix">
                @forelse($shop_before as $item)
                <li>
                    <div class="index-serwrap">
                        <div class="index-serimg">
                            <a href="{{$item['url']}}">
                                <img src="@if($item['recommend_pic']){!! URL($item['recommend_pic']) !!} @else {!! Theme::asset()->url('images/default_avatar.png') !!} @endif" alt="">
                                <div class="index-serimgp">
                                    <div class="space-6"></div>
                                    <p class="no-margin-bottom">服务：@if(empty($item['skill_name']))
                                            暂无标签
                                        @else
                                           {{$item['skill_name']}}
                                        @endif</p>
                                    <div class="space-2"></div>
                                </div></a>
                        </div>
                        <div class="index-serinfo">
                            <div class="space-6"></div>
                            <a class="cor-gray24 text-size14" href="">{!! $item['recommend_name'] !!}</a>
                            <div class="space-4"></div>
                            <p class="no-margin-bottom clearfix">
                                <span class="text-size12 pull-left">好评率 <span class="cor-orange">{!! $item['good_comment_rate'] !!}%</span></span>
                                <span class="pull-right">
                                    @if(isset($item['bank_auth']) && $item['bank_auth'] == true)
                                    <i class="bank-attestation"></i>
                                    @endif
                                    @if(isset($item['realname_auth']) && $item['realname_auth'] == true)
                                    <i class="cd-card-attestation"></i>
                                    @endif
                                    @if(isset($item['email_status']) && $item['email_status'] == 2)
                                    <i class="email-attestation"></i>
                                    @endif
                                    @if(isset($item['alipay_auth']) && $item['alipay_auth'] == true)
                                    <i class="alipay-attestation"></i>
                                    @endif
                                    @if(isset($item['enterprise_auth']) && $item['enterprise_auth'] == true)
                                    <i class="com-attestation"></i>
                                    @endif
                                </span>
                            </p>
                        </div>
                    </div>
                </li>
                @empty
                @endforelse
            </ul>
        </div>
        <div class="space-20"></div>
        <div class="text-size18 cor-gray24">案例/<span class="text-size16">CASE</span><a href="/task/successCase" class="hovA cor-orange text-size14 pull-right">更多></a></div>
        <div class="space-10"></div>
        <div class="index-case clearfix">
            @if(!empty($success))
                @foreach($success as $k => $v)
                    @if($k == 0)
            <div class="index-casebig pull-left">
                <a href="{{$v['url']}}">
                    <img src="{{$v['recommend_pic']}}" alt="">
                    <p class="index-caseinfo clearfix">
                        <span class="pull-left">{{$v['recommend_name']}}</span>
                        <span class="pull-right index-casetag text-size12">{{$v['name']}}</span>
                    </p>
                </a>
            </div>
                    @endif
                @endforeach
            <div class="pull-left index-cardwrap">
                @foreach($success as $k => $v)
                    @if($k > 0 && $k < 4)
                        @if($k != 2)
                        <div class="index-card">
                            <a class="index-cardimg" href="{{$v['url']}}">
                                <img class="index-cardimg1" src="{!! URL($v['recommend_pic']) !!}" alt="">
                                <img class="index-cardimg2" src="{!! URL($v['recommend_pic']) !!}" alt="">
                                <img class="index-cardimg3" src="{!! URL($v['recommend_pic']) !!}" alt="">
                            </a>
                            <div class="index-cardinfo text-center">
                                <a href="{{$v['url']}}"><p class="text-size14 cor-gray24">{{$v['recommend_name']}}</p></a>
                                <span class="index-casetagray text-size12 cor-gray99">{{$v['name']}}</span>
                            </div>
                        </div>
                        @else
                            <div class="index-card">
                                <div class="index-cardinfo text-center">
                                    <a href="{{$v['url']}}"><p class="text-size14 cor-gray24">{{$v['recommend_name']}}</p></a>
                                    <span class="index-casetagray text-size12 cor-gray99">{{$v['name']}}</span>
                                </div>
                                <a class="index-cardimg" href="{{$v['url']}}">
                                    <img class="index-cardimg1" src="{!! URL($v['recommend_pic']) !!}" alt="">
                                    <img class="index-cardimg2" src="{!! URL($v['recommend_pic']) !!}" alt="">
                                    <img class="index-cardimg3" src="{!! URL($v['recommend_pic']) !!}" alt="">
                                </a>
                            </div>
                        @endif
                    @endif
                @endforeach
            </div>
            @endif
        </div>
        <div class="space-20"></div>
    </div>
</section>

<div class="index-barragebg">
    <div class="index-barrage">

    </div>
</div>

<section>
    <div class="container">
        <div class="space-20"></div>
        <div class="text-size18 cor-gray24">商城/<span class="text-size16">SHOP</span><a href="/bre/shop" class="hovA cor-orange text-size14 pull-right">更多></a></div>
        <div class="space-10"></div>
        <div>
            <div class="device" id="device">
            </div>
            <div class="device-link text-center"><a class="text-size14" href="{{url('bre/shop')}}">更多></a></div>
            <div class="space-30"></div>
        </div>
    </div>
</section>

<div class="index-flinkbg">
    <div class="container clearfix">
        @if($friendUrl)
            @foreach($friendUrl as $k6 => $v6)
        <p><a href="{{url($v6['content'])}}"><img src="{{url($v6['pic'])}}" alt=""></a></p>
            @endforeach
        @endif
    </div>
</div>

<div id="goods" data-shop="{{$goods}}"></div>
<div id="danmu" data-danmu="{{$danmu}}"></div>

{!! Theme::asset()->container('custom-css')->usepath()->add('case-css', 'css/index/case.css') !!}