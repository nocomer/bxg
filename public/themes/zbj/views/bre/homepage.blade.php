<div class="g-taskbarnav homemenu-taskbarnav">
    <div class="container clearfix">
        <div class="row g-nav">
            <div class="zbj_banner col-sm-12 col-left col-right">
                <h2 class="text-center">让工作变得更简单</h2>
                <p class="text-center brief"><em></em>为<span> 6,000,000 </span>企业主提供服务<em></em></p>
                <div class="tab_box">
                    <ul class="clearfix mar0">
                        <li class="col-md-4 active">
                            <p class="text-center" onclick="ssTab(this,'.immediate_search')">立即搜索&nbsp;&nbsp;&nbsp;<span>海量需求任您来挑</span></p>
                        </li>
                        <li class="col-md-4">
                            <p class="text-center" onclick="ssTab(this,'.consultation_fw')">咨询服务商&nbsp;&nbsp;&nbsp;<span>为您推荐人才和公司</span></p>
                        </li>
                        <li class="col-md-4">
                            <a href="/task/create">
                                <p class="text-center">发布需求&nbsp;&nbsp;&nbsp;<span>让人才和公司主动找您</span></p>
                            </a>
                        </li>
                    </ul>
                    <div class="tab_content">
                        <div class="immediate_search">
                            <form method="get" action="/task">
                                <div class="form-group clearfix">
                                    <input class="pull-left" type="text" name="keywords"/>
                                    <button class="pull-left" type="submit">搜索</button>
                                </div>
                            </form>
                            <div class="row">
                                <div class="col-md-6 rj">
                                    <h5 class="text-center"><em></em>全部任务分类<em></em></h5>
                                    <ul class="clearfix">
                                        @forelse(Theme::get('task_cate') as $k => $v)
                                            @if(isset($v['pid']) && $v['pid'] == 0 && $k < 6)
                                                <li class="col-md-6 clearfix">
                                                    <div class="img pull-left">
                                                        @if(!empty( $v['pic']) && is_file(url( $v['pic'])))
                                                            <img src="{!! url($v['pic']) !!}">
                                                        @else
                                                        <img src="{!! Theme::asset()->url('images/zbj/industry_icon.png') !!}">
                                                        @endif
                                                    </div>
                                                    <div class="pull-left text">
                                                        <h6>{!! $v['name']!!}</h6>
                                                            {{--<a href="/task?category={!! $v['id'] !!}">--}}
                                                                {{--{!! $v['name'] !!}--}}
                                                            {{--</a>--}}
                                                        <p>
                                                            @forelse($v['child_task_cate'] as $m => $n)
                                                                @if($m < 3)
                                                                    {!! $n['name'] !!}@if($m < 2)/@endif
                                                                @endif
                                                            @empty
                                                            @endforelse
                                                        </p>
                                                    </div>
                                                </li>
                                            @endif
                                        @empty
                                        @endforelse
                                    </ul>
                                </div>
                                <div class="col-md-6 ai">
                                    <h5 class="text-center"><em></em>成功案例<em></em></h5>
                                    <ul class="clearfix">
                                        @forelse($success as $k => $v)
                                            @if($k < 6)
                                                <a href="{{$v['url']}}" target="_blank">
                                                    <li class="col-md-4">
                                                        @if($v['recommend_pic'] && is_file(url($v['recommend_pic'])))
                                                            <img src="{!! URL($v['recommend_pic']) !!}">
                                                        @elseif(empty($v['recommend_pic']) && is_file(URL($v['success_pic'])))
                                                            <img src="{!! URL($v['success_pic']) !!}">
                                                        @else
                                                            <img src="{!! Theme::asset()->url('images/zbj/case_list.png') !!}">
                                                        @endif

                                                        <div class="img_bg">
                                                            <p class="">{{$v['title']}}</p>
                                                            <p>{{$v['name']}}</p>
                                                        </div>
                                                    </li>
                                                </a>
                                            @endif

                                        @empty

                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="consultation_fw" style="display:none;">
                            <ul id="myTab" class="clearfix">
                                @forelse($cate as $k => $v)
                                    @if($k < 6)
                                        <li class="pull-left @if($cate_id == $v['id'])active @endif">
                                            <a href="#home{{$k+1}}" data-toggle="tab" onclick="chagngeCate(this,'{{$v['id']}}','#home{{$k+1}}')">{{$v['name']}}</a>
                                        </li>
                                    @endif
                                @empty
                                @endforelse
                            </ul>
                            <div id="myTabContent" class="tab-content">
                                <ul class="tab-pane fade in active row" id="home1">
                                    @forelse($user_Arr as $k => $v)
                                        @if($k < 6)
                                            <li class="clearfix col-xs-4">
                                                <div class="pull-left">
                                                    <h6>{{$v['name']}}</h6>
                                                    <p>{{$v['introduce']}}</p>
                                                    <ul class="clearfix">
                                                        @if(isset($v['skill']) && is_array($v['skill']))
                                                            @forelse($v['skill'] as $key => $val)
                                                                @if($key < 3)
                                                                     <li class="pull-left">{{$val}}</li>
                                                                @endif
                                                            @empty
                                                            @endforelse
                                                        @endif

                                                    </ul>
                                                </div>
                                                <div class="pull-right">
                                                    <div class="img">
                                                        @if($v['avatar'] && is_file(url($v['avatar'])))
                                                            <img src="{!! url($v['avatar']) !!}">
                                                        @else
                                                            <img src="{!! Theme::asset()->url('images/zbj/tx1.png') !!}">
                                                        @endif

                                                    </div>
                                                    <a target="_blank" href="/bre/serviceEvaluateDetail/{{$v['uid']}}">免费咨询</a>
                                                </div>
                                            </li>
                                        @endif
                                    @empty
                                    @endforelse
                                    <div class="col-xs-12">
                                        <p class="text-center see_more">
                                            <a href="/bre/service">查看更多顾问 ></a>
                                        </p>
                                    </div>
                                </ul>
                                <ul class="tab-pane fade row" id="home2"></ul>
                                <ul class="tab-pane fade row" id="home3"></ul>
                                <ul class="tab-pane fade row" id="home4"></ul>
                                <ul class="tab-pane fade row" id="home5"></ul>
                                <ul class="tab-pane fade row" id="home6"></ul>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </div>
</div>
<!-- 推荐服务商 -->
<div class="container">
    <div class="clearfix pa-t53 tj_fw pa-b70">
        <h4 class="text-center text-size27 cor-gray33 le-h40 ma-0"><em class="em"></em>推荐服务商<em class="em"></em></h4>
        <p class="center text-size16 cor-gray99 le-h30 ma-b53">1300万专业人才供您挑选 满意付款 先行赔付</p>
        <ul class="clearfix ma-0">

            @forelse($shop_before as $i => $v)
                @if($i < 15)
                    <li class="col-lg-2  col-md-3 col-sm-4 col-xs-6">
                        <div class="img text-center">
                            @if(is_file(url($v['shop_pic'])) && $v['shop_pic'])
                                <img src="{!! url($v['shop_pic']) !!}">
                            @else
                                <img src="{!! Theme::asset()->url('images/zbj/fw_logo.png') !!}">
                            @endif

                        </div>
                        <div class="fw_bg text-center">
                            <h6>
                                @if(!empty($v['shop_name']))
                                    {{$v['shop_name']}}
                                @else
                                    这是店铺名称
                                @endif
                            </h6>
                            <p>@if(isset($v['skill_name'])){{$v['skill_name']}}@endif</p>
                            <a href="{{$v['url']}}">进入店铺</a>
                        </div>
                    </li>
                @endif
            @empty
            @endforelse

        </ul>
    </div>
</div>
<!--最新任务-->
<div class="clearfix pa-t53 zx_rw pa-b70">
    <div class="container">
        <h4 class="text-center text-size27 cor-gray33 le-h40 ma-0"><em class="em"></em>最新任务/动态<em class="em"></em></h4>
        <p class="center text-size16 cor-gray99 le-h30 ma-b53">1300万专业人才供您挑选 满意付款 先行赔付</p>
        <div class="col-lg-9 col-md-12 col-sm-12 col-xs-12 m-task col-left col-right bk-ff">
            <div class="clearfix">
                <h4 class="pull-left text-size16 cor-gray33 bl">最新任务</h4>
                <!-- <a class="pull-right cor-gray97 u-more" href="/task" target="_blank">More></a> -->
            </div>
            <div class="img">
                @if(count($adZbj) && is_file(url($adZbj[0]['ad_url'])))
                    <a  target="_blank" href="{{url($adZbj[0]['ad_url'])}}">
                        <img src="{{url($adZbj[0]['ad_file'])}}" alt="">
                    </a>
                @else
                <img src="{!! Theme::asset()->url('images/zbj/gg_list1.png') !!}">
                @endif
            </div>
            <div class="g-taskleft clearfix">
                <ul class=" clearfix text-size14 m-homelist cor-grayC2 mg-margin col-sm-12 ">
                    @if($task)
                        @forelse($task as $k1 => $v1)
                            @if($k1<8)
                            <li class="col-md-6 col-sm-6 col-xs-6 g-taskItem">
                                <div class="clearfix">
                                    <p class="p-space pull-left ma-0"><span class="cor-orange s-homewrap1 p-space">@if($v1['show_cash'])￥{{$v1['show_cash']}}@else ￥0 @endif</span><a class="cor-gray51 s-hometit" href="/task/{{$v1['id']}}" target="_blank">{{$v1['title']}}</a></p>
                                    <span class="s-homewrap1 p-space pull-right">{{$v1['delivery_count']}}投标</span>
                                </div>
                                <p class="p-space mg-margin">
                                    <span class="s-homewrap1 p-space">{{$v1['name']}}发布</span>
                                </p>
                            </li>
                            @endif
                        @empty
                        @endforelse
                    @endif
                </ul>
            </div>
        </div>

        <!--最新动态-->
        <div class="col-lg-3  g-taskright hidden-md hidden-sm hidden-xs col-left col-right bk-ff">
            <h4 class=" text-size16 cor-gray33 bl">最新动态</h4>
            <div class="txtMarquee-top u-rightwrap1 clearfix">
                <div class="bd clearfix">
                    <ul class="mg-margin  clearfix">
                        @if($active)
                            @foreach($active as $k2 => $v2)
                                <li class="u-rlistitem clearfix">
                                    <div class="pull-left img" style="width:20%;">
                                        @if($v2['avatar'] && is_file(url($v2['avatar'])))
                                            <img src="{!! url($v2['avatar']) !!}">
                                        @else
                                        <img src="{!! Theme::asset()->url('images/zbj/logo1.png') !!}">
                                        @endif
                                    </div>
                                    <div class="clearfix pull-left" style="width:80%;padding-left:20px;">
                                        <p class="text-size13 s-hometit cor-gray99 clearfix">
                                            <!-- <i class="fa fa-circle-thin cor-grayC2 text-size12"></i> -->
                                            <a href="/bre/serviceCaseList/{{$v2['uid']}}" style="width:56px;" class="cor-blue2f text-size14 cor-gray33 pull-left p-space" target="_blank">{{$v2['name']}}</a>
                                            &nbsp;&nbsp;&nbsp;&nbsp;成功中标了<span class="time pull-right">@if(intval((time() - strtotime($v2['created_at']))/60) > 60)
                                                    1小时前 @else {{intval((time() - strtotime($v2['created_at']))/60)}}
                                                    分钟前 @endif
                                            </span>
                                        </p>

                                        <div class="clearfix cor-grayC2 p-space">
                                            <span class="pull-left price text-size13 cor-grayf8"><span class="cor-orange">@if($v2['show_cash'])
                                                        ￥{{$v2['show_cash']}}@else ￥0 @endif</span>
                                            </span>
                                            <span class="pull-left shop">
                                                <a href="/task/{{$v2['task_id']}}" class="text-size13 cor-gray66" target="_blank">{{$v2['title']}}</a>
                                            </span>
                                            
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="container">
    <div class="clearfix pa-t53 tj_zp pa-b70">
        <h4 class="text-center text-size27 cor-gray33 le-h40 ma-0"><em class="em"></em>推荐作品<em class="em"></em></h4>
        <p class="center text-size16 cor-gray99 le-h30 ma-b53">1300万专业人才供您挑选 满意付款 先行赔付</p>
        <ul class="clearfix mg-margin g-servicer  g-servicer-list">
            @forelse($work as $k => $v)
                @if($k < 6)
                    <li class="col-lg-4  col-md-4 col-sm-4 col-xs-6 u-listitem1 col-left col-right">
                        <div class="u-index">
                            <div class="f-pr f-pr-shop clearfix">
                                <div class="pull-left img">
                                    <a href="{{$v['url']}}">
                                        <img @if($v['recommend_pic'] && is_file(url($v['recommend_pic'])))src="{!! URL($v['recommend_pic']) !!}"
                                             @elseif(empty($v['recommend_pic']) && is_file(url(URL($v['cover'])))) src="{!! URL($v['cover']) !!}"  @else src="{!! Theme::asset()->url('images/zbj/tx2.png') !!}"
                                             @endif
                                             alt="First slide" width="100%" class="img-responsive j-img">
                                    </a>
                                </div>
                                <div class="pull-left">
                                    <h5>{{  $v['title'] }} <span>作品</span></h5>
                                    <p>@if(isset($v['name'])){{$v['name']}}@endif</p>
                                </div>
                            </div>
                            <div class="g-scueeitem1 clearfix p-space">
                                <h4 class="text-size14 mg-margin p-space">
                                    <a href="{{$v['url']}}" class="cor-gray51">
                                        {{  $v['title'] }}
                                    </a>
                                </h4>
                                <div class="space-2"></div>
                                <p class="cor-gray89">好评率：
                                    @if(!empty($v['comments_num']))
                                        {!! intval(($v['good_comment']/ $v['comments_num']))*100 !!}%
                                    @else
                                        0%
                                    @endif
                                    |@if(!empty($v['sales_num']))
                                        {!! $v['sales_num'] !!}
                                    @else
                                        0
                                    @endif人购买
                                </p>
                                <div class="space-6"></div>
                                <p class="cor-gray89 mg-margin clearfix">
                                    <span class="cor-orange text-size16 pull-left">
                                        <span class="text-size12">￥</span>
                                        {!! $v['cash'] !!}
                                    </span>
                                    <a class="pull-right see_info" href="{{$v['url']}}">查看详情</a>
                                </p>
                            </div>
                        </div>
                    </li>
                @endif
            @empty
            @endforelse
            <div class="img col-lg-12 col-left col-right">

                @if(count($ad) && is_file(url($ad[0]['ad_url'])))
                    <a  target="_blank" href="{{url($ad[0]['ad_url'])}}">
                        <img src="{{url($ad[0]['ad_file'])}}" alt="">
                    </a>
                @else
                    <img src="{!! Theme::asset()->url('images/zbj/gg_list2.png') !!}">
                @endif

            </div>
        </ul>
    </div>
</div>
<!-- 最新资讯 -->
<div class="clearfix pa-t53 pa-b70 g-info">
    <div class="container">
        <h4 class="text-center text-size27 cor-gray33 le-h40 ma-0"><em class="em"></em>最新资讯<em class="em"></em></h4>
        <p class="center text-size16 cor-gray99 le-h30 ma-b53">1300万专业人才供您挑选 满意付款 先行赔付</p>

        <div class="clearfix">

            <div class="col-lg-4 hidden-xs hidden-md hidden-sm col-left col-right">
                @if(!empty($article) && is_array($article))
                <div class="f-pr">
                    <a href="{{$article[0]['url']}}" target="_blank">
                        <img src="{{url($article[0]['recommend_pic'])}}" alt="" class="img-responsive j-img">
                    </a>
                    <div class="f-prwarp">
                        <h5>
                            <a href="{{$article[0]['url']}}" target="_blank" class="cor-white">
                                {{$article[0]['title']}}
                            </a>
                        </h5>
                    </div>
                </div>
                @endif
                <ul class="bk-ff new_mas">
                    <li>
                        <p>{{$article[0]['summary']}}</p>
                    </li>
                    <li>
                        <p>{{$article[0]['cate_name']}}</p>
                    </li>
                    <li>
                        <p>{{date('Y-m-d',strtotime($article[0]['created_at']))}}</p>
                    </li>
                </ul>
            </div>
            <div class="col-lg-8 col-md-12">
                <ul class="bk-ff zx">
                    @forelse($article as $k => $v)
                        @if($k > 0 && $k < 4)
                            <li class="clearfix">
                                <h5 class="pull-left">{{date('d',strtotime($v['created_at']))}}</h5>
                                <div class="pull-left">
                                    <p class="s_title">{{CommonClass::getMonthEn($v['created_at'])}}</p>
                                    <span>{{date('Y',strtotime($v['created_at']))}}</span>
                                </div>
                                <div class="pull-left info_content">
                                    <h6>{{$v['title']}}</h6>
                                    <p>{{$v['summary']}}</p>
                                </div>
                            </li>
                        @endif
                    @empty
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="container">
    <!-- 友情链接 -->
    <div class="clearfix pa-t53 tj_zp pa-b70  g-lk">
        <div class="col-sm-12 col-left col-right">
            <h4 class="text-center text-size27 cor-gray33 le-h40 ma-0"><em class="em"></em>友情链接<em class="em"></em></h4>
            <p class="center text-size16 cor-gray99 le-h30 ma-b53">1300万专业人才供您挑选 满意付款 先行赔付</p>
            <div class="clearfix u-gray g-lkroll">
                <div class="clearfix">
                    <a class="z-btn1 next " href="javascript:;"><i class="fa fa-angle-left text-size24"></i></a>
                    <a class="z-btn2 prev" href="javascript:;" ><i class="fa fa-angle-right text-size24"></i></a>
                </div>
                <div class="bd">
                    <ul class="mg-margin picList">
                        @if($friendUrl)
                            @foreach($friendUrl as $k6 => $v6)
                                <li class=" text-center u-item">
                                    <div class="">
                                        <a target="_blank" href="{{url($v6['content'])}}">
                                            <img src="{{url($v6['pic'])}}" alt="kppw">
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="col-sm-12 col-left col-right">
        @if(count($ad))
            <div class="for-advertise">
                <a  target="_blank" href="{{url($ad[0]['ad_url'])}}"><img src="{{url($ad[0]['ad_file'])}}" alt=""></a>
            </div>
        @endif
        <div class="space-10"></div>
    </div> -->
</div>





{!! Theme::asset()->container('custom-css')->usepath()->add('index','css/index.css') !!}
{!! Theme::asset()->container('specific-js')->usepath()->add('SuperSlide','plugins/jquery/superSlide/jquery.SuperSlide.2.1.1.js') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('homepage','js/doc/homepage.js') !!}
{!! Theme::asset()->container('specific-js')->usepath()->add('adaptive','plugins/jquery/adaptive-backgrounds/jquery.adaptive-backgrounds.js') !!}

