<div class="header-bg">
    <div class="container">
        <div class="header-nav clearfix">
            <div class="header-logo pull-left">
                <a href="{!! CommonClass::homePage() !!}">
                    @if(Theme::get('site_config')['site_logo_1'])
                        <img src="{!! url(Theme::get('site_config')['site_logo_1'])!!}" >
                    @else
                        <img src="{!! Theme::asset()->url('images/sign-logo.png') !!}" >
                    @endif
                </a>
            </div>
            <div class="header-info pull-left">
                <a class="header-infolink" href="">分类 <i class="fa fa-angle-down"></i></a>
                <div class="header-infodrop clearfix">
                    @if(!empty(Theme::get('task_cate')))
                        <div class="header-droptitle pull-left">
                            @foreach(Theme::get('task_cate') as $k => $v)
                                @if($k < 5)
                                    <a href="/task?category={!! Theme::get('task_cate')[$k]['id'] !!}">{!! Theme::get('task_cate')[$k]['name'] !!}</a>
                                @endif
                            @endforeach
                        </div>
                        @foreach(Theme::get('task_cate') as $k => $v)
                            @if($k < 5)
                                <ul class="pull-left header-dropul">
                                    @if(!empty(Theme::get('task_cate')[$k]['child_task_cate']) && is_array(Theme::get('task_cate')[$k]['child_task_cate']))
                                        @for($i = 0; $i < count(Theme::get('task_cate')[$k]['child_task_cate']); $i += 3)
                                            <li>
                                                @if(isset(Theme::get('task_cate')[$k]['child_task_cate'][$i]['id']))
                                                    <a href="/task?category={!! Theme::get('task_cate')[$k]['child_task_cate'][$i]['id'] !!}">
                                                        {!! Theme::get('task_cate')[$k]['child_task_cate'][$i]['name'] !!}
                                                    </a>
                                                @endif
                                                @if(isset(Theme::get('task_cate')[$k]['child_task_cate'][$i + 1]['id']))
                                                    <a href="/task?category={!! Theme::get('task_cate')[$k]['child_task_cate'][$i + 1]['id'] !!}">
                                                        {!! Theme::get('task_cate')[$k]['child_task_cate'][$i + 1]['name'] !!}
                                                    </a>
                                                @endif
                                                @if(isset(Theme::get('task_cate')[$k]['child_task_cate'][$i + 2]['id']))
                                                    <a href="/task?category={!! Theme::get('task_cate')[$k]['child_task_cate'][$i + 2]['id'] !!}">
                                                        {!! Theme::get('task_cate')[$k]['child_task_cate'][$i + 2]['name'] !!}
                                                    </a>
                                                @endif
                                            </li>
                                        @endfor
                                    @endif
                                </ul>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="div-nav pull-left">
                <div class="div-hover hidden-md hidden-sm hidden-xs"></div>
                <ul>
                    @if(!empty(Theme::get('nav_list')))
                        @if(count(Theme::get('nav_list')) > 4)
                            @for($i=1;$i<5;$i++)
                                <li @if(Theme::get('nav_list')[$i-1]['link_url'] == Theme::get('now_menu')) class="hActive" @endif>
                                    <a class="topborbtm" href="{!! Theme::get('nav_list')[$i-1]['link_url'] !!}" @if(Theme::get('nav_list')[$i-1]['is_new_window'] == 1)target="_blank" @endif >{!! Theme::get('nav_list')[$i-1]['title'] !!}</a>
                                </li>
                            @endfor

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    更多   <b class="fa fa-angle-down"></b>
                                </a>
                                <ul class="dropdown-menu dropdown-list">
                                    @for($i=5;$i<count(Theme::get('nav_list'))+1;$i++)
                                        <li @if(Theme::get('nav_list')[$i-1]['link_url'] == Theme::get('now_menu')) class="hActive" @endif>
                                            <a class="text-center" href="{!! Theme::get('nav_list')[$i-1]['link_url'] !!}"
                                               @if(Theme::get('nav_list')[$i-1]['is_new_window'] == 1)target="_blank" @endif >
                                                {!! Theme::get('nav_list')[$i-1]['title'] !!}
                                            </a>
                                        </li>
                                    @endfor
                                </ul>
                            </li>

                        @else
                            @for($i=1;$i<count(Theme::get('nav_list'));$i++)
                                <li @if(Theme::get('nav_list')[$i-1]['link_url'] == Theme::get('now_menu')) class="hActive" @endif>
                                    <a class="topborbtm" href="{!! Theme::get('nav_list')[$i-1]['link_url'] !!}" @if(Theme::get('nav_list')[$i-1]['is_new_window'] == 1)target="_blank" @endif >{!! Theme::get('nav_list')[$i-1]['title'] !!}</a>
                                </li>
                            @endfor

                        @endif
                    @else
                        <li @if(CommonClass::homePage() == Theme::get('now_menu')) class="hActive"@endif><a class="topborbtm" href="{!! CommonClass::homePage() !!}">首页</a></li>
                        <li @if('/task' == Theme::get('now_menu')) class="hActive" @endif><a class="topborbtm" href="/task">任务大厅</a></li>
                        <li @if('/bre/service' == Theme::get('now_menu')) class="hActive" @endif><a class="topborbtm" href="/bre/service">服务商</a></li>
                        <li @if('/task/successCase' == Theme::get('now_menu')) class="hActive" @endif><a class="topborbtm" href="/task/successCase">成功案例</a></li>
                        <li @if('/article' == Theme::get('now_menu')) class="hActive" @endif><a class="topborbtm" href="/article'">威客商城</a></li>
                    @endif
                </ul>
            </div>
            @if(!Auth::check())
                <a class="header-login pull-right" href="/login">登录</a>
            @else
                {{--登录后状态--}}
                <div class="state pull-right clearfix">
                    <a href="/user/index">
                        <i class="userIco"></i><i class="fa fa-angle-down"></i>
                    </a>
                    <div class="login login-end">
                        <div class="foc-ewm-arrow1"></div>
                        <div class="foc-ewm-arrow2"></div>
                        <div class="bd clearfix">
                            <div class="pull-left img">
                                <img class="img-responsive" src="{{url(Theme::get('avatar'))}}" onerror="onerrorImage('/themes/quietgreen/assets/images/default_avatar.png',$(this))">
                            </div>
                            <div class="ostate">
                                <a class="p-space" href="/user/index">{{Auth::user()->name}}</a>
                                <a class="p-space" href="/user/messageList/1">您有新的消息</a>
                                <a href="/logout">[退出]</a>
                            </div>
                        </div>
                        <div class="usercenter">
                            <a href="{{url('user/index')}}" class="">个人中心</a>
                        </div>
                    </div>
                </div>
            @endif
            <ul class="pull-right header-right">
                <li class="header-search">
                    <a href=""></a>
                    <form class="navbar-form navbar-left" action="/task" role="search" method="get">
                        <div class="form-group fom-search dropdown clearfix">
                            <input type="text" name="keywords" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                   class="form-control inputx" placeholder="输入搜索内容" @if(!empty(request('keywords'))){!! request('keywords') !!}@endif>
                            <input type="hidden" value="" id="type" name="">
                            <button class="ico-search fa fa-search" type="submit"> </button>
                            <ul class="dropdown-menu text-center search-right" role="menu" aria-labelledby="dLabel">
                                <li>
                                    <a href="javascript:void(0)" url="/task" onclick="switchSearch(this)">搜索任务</a>
                                </li>

                                <li>
                                    <a href="javascript:void(0)" url="/bre/service" onclick="switchSearch(this)">搜索服务商</a>
                                </li>
                            </ul>
                        </div>
                    </form>
                </li>
                <li class="header-adddrop">
                    <a class="header-addlink" href="">@if(Session::get('substation_name')){!! Session::get('substation_name') !!}@else 全国 @endif&nbsp;&nbsp;&nbsp;<i class="fa fa-angle-down"></i></a>
                    <div class="header-address">
                        <ul>
                            @if(Theme::get('substation'))
                                @for($i = 0; $i < count(Theme::get('substation')); $i += 3)
                                    <li>
                                        @if(isset(Theme::get('substation')[$i]))
                                            <a href="/substation/{!! Theme::get('substation')[$i]['district_id'] !!}">{!! Theme::get('substation')[$i]['name'] !!}</a>
                                        @endif
                                        @if(isset(Theme::get('substation')[$i + 1]))
                                            <a href="/substation/{!! Theme::get('substation')[$i + 1]['district_id'] !!}">{!! Theme::get('substation')[$i + 1]['name'] !!}</a>
                                        @endif
                                        @if(isset(Theme::get('substation')[$i + 2]))
                                            <a href="/substation/{!! Theme::get('substation')[$i + 2]['district_id'] !!}">{!! Theme::get('substation')[$i + 2]['name'] !!}</a>
                                        @endif
                                    </li>
                                @endfor
                            @endif
                        </ul>
                    </div>
                </li>
                <li><a href="/task/create">发布需求</a></li>
            </ul>
        </div>
    </div>
</div>