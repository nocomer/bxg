<div class="container">

    @if(count($ad))
        <div class="mshop-bar">
            <a href="{!! $ad[0]['ad_url'] !!}">
                <img src="{!! URL($ad[0]['ad_file']) !!}" alt=""
                     onerror="onerrorImage('{{ Theme::asset()->url('images/index/shop-bar.jpg')}}',$(this))">
            </a>
        </div>
    @endif
    <div class="mshop-filter service-filter clearfix">
        <div class="col-xs-12">
            <div class=" showSideDown ">
                <span class="title">任务分类</span>
                <a href="{!! URL('task/successCase').'?'.http_build_query(array_merge(array_except($merge,['searche']),['category'=>0])) !!}"
                   class="{!! (!isset($merge['category']) || $merge['category']==$pid)?'mactive':'' !!}" >
                    全部
                </a>
                @forelse($category as $k=>$v)
                    @if($k < 7)
                    <a class="{!! (isset($merge['category']) && $merge['category']==$v['id'])?'mactive':'' !!}"
                       href="{!! URL('task/successCase').'?'.http_build_query(array_merge(array_except($merge,'page'), ['category'=>$v['id']])) !!}">
                        {{ $v['name'] }}
                    </a>
                    @endif
                @empty
                @endforelse
                @if(count($category)>7)
                    <div class="pull-right select-fa-angle-down">
                        <i class="fa fa-angle-down text-size14 show-next"></i>
                    </div>

                    <div class="col-md-12 col-xs-12">
                        <div class="row">
                            <div class="col-md-offset-1 cor99">
                                <div class="row">
                                    @foreach(array_slice($category,7,(count($category)-7)) as $v)
                                        <span class="{!! (isset($merge['category']) && $merge['category']==$v['id'])?'mactive':'' !!}"><a href="{!! URL('task/successCase').'?'.http_build_query(array_merge(array_except($merge,'page'), ['category'=>$v['id']])) !!}" class="{!! (isset($merge['category']) && $merge['category']==$v['id'])?'list-on':'' !!}">{{ $v['name'] }}</a></span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="clearfix">
        <div class="space-20"></div>
        <div class="text-size14 mshop-sort clearfix">
            <div class="pull-left">
                <a class="{{ !isset($_GET['desc'])?'active':'' }}"
                   href="{!! URL('task/successCase').'?'.http_build_query(array_except($merge,['desc','searche'])) !!}">默认</a>
                <a class="{{ (isset($_GET['desc']) && $_GET['desc']=='created_at')?'active':''}}"
                   href="{!! URL('task/successCase').'?'.http_build_query(array_merge($merge,['desc'=>'created_at'])) !!}">按时间 <i class="glyphicon glyphicon-arrow-down"></i></a>
                <a class="{{ (isset($_GET['desc']) && $_GET['desc']=='view_count')?'active':''}}"
                   href="{!! URL('task/successCase').'?'.http_build_query(array_merge($merge,['desc'=>'view_count'])) !!}">按人气 <i class="glyphicon glyphicon-arrow-down"></i></a>
            </div>
            <form action="{{ URL('task/successCase') }}" method="get">
                <div class="pull-right">
                    <button class="mshop-sortbtn" type="submit"><i class="fa fa-search text-size18"></i></button>
                    <input class="mshop-sortinp" name="searche" type="text" placeholder="搜索成功案例标题">
                </div>
            </form>

        </div>
        <div class="space-10"></div>
        <div class="index-case clearfix">
            @if(!empty($list->toArray()['data'][0]))
            <div class="index-casebig pull-left">
                <a href="@if(Auth::check() && Auth::user()->id == $list->toArray()['data'][0]['uid'])
                {!! '/user/personevaluationdetail/'.$list->toArray()['data'][0]['id']  !!}
                @elseif( !empty($list->toArray()['data'][0]['url'])) {{  $list->toArray()['data'][0]['url'] }}
                @else {!! '/task/successDetail/'.$list->toArray()['data'][0]['id']  !!} @endif" target="_blank">
                    <img src="{!! url($list->toArray()['data'][0]['pic']) !!}" alt=""
                         onerror="onerrorImage('{{ Theme::asset()->url('images/index/banner-bg.png')}}',$(this))">
                    <p class="index-caseinfo clearfix">
                        <span class="pull-left">
                            <a href="@if(Auth::check() && Auth::user()->id == $list->toArray()['data'][0]['uid'])
                            {!! '/user/personevaluationdetail/'.$list->toArray()['data'][0]['id']  !!}
                            @elseif( !empty($list->toArray()['data'][0]['url'])) {{  $list->toArray()['data'][0]['url'] }}
                            @else {!! '/task/successDetail/'.$list->toArray()['data'][0]['id']  !!} @endif" target="_blank">
                                {{$list->toArray()['data'][0]['title']}}
                            </a>
                        </span>
                        <span class="pull-right index-casetag text-size12">{{$list->toArray()['data'][0]['cate_name']}}</span>
                    </p>
                </a>
            </div>
            @endif
            <div class="pull-left index-cardwrap">
                @forelse($list as $k => $v)
                    @if($k>0 && $k <4)
                        <div class="index-card">
                            <a class="index-cardimg" href="@if(Auth::check() && Auth::user()->id == $v['uid']) {!! '/user/personevaluationdetail/'.$v['id']  !!}
                            @elseif( !empty($v['url'])) {{  $v['url'] }}
                            @else {!! '/task/successDetail/'.$v['id']  !!} @endif" target="_blank">
                                <img class="index-cardimg1" src="{!! url($v['pic']) !!}" alt=""
                                     onerror="onerrorImage('{{ Theme::asset()->url('images/index/banner-bg.png')}}',$(this))">
                                <img class="index-cardimg2" src="{!! url($v['pic']) !!}" alt=""
                                     onerror="onerrorImage('{{ Theme::asset()->url('images/index/banner-bg.png')}}',$(this))">
                                <img class="index-cardimg3" src="{!! url($v['pic']) !!}" alt=""
                                     onerror="onerrorImage('{{ Theme::asset()->url('images/index/banner-bg.png')}}',$(this))">
                            </a>
                            <div class="index-cardinfo text-center">
                                <p class="text-size14 cor-gray24 p-space">
                                    <a href="@if(Auth::check() && Auth::user()->id == $v['uid']) {!! '/user/personevaluationdetail/'.$v['id']  !!}
                                    @elseif( !empty($v['url'])) {{  $v['url'] }}
                                    @else {!! '/task/successDetail/'.$v['id']  !!} @endif" target="_blank">{{$v['title']}}</a>
                                </p>
                                <span class="index-casetagray text-size12 cor-gray99">{{$v['cate_name']}}</span>
                            </div>
                        </div>
                    @endif
                @empty
                @endforelse
            </div>
        </div>
        <div class="space"></div>
        <div class="mcase-list">
            <ul class="clearfix">
                @forelse($list as $k => $v)
                    @if($k>3)
                        <li class="pull-left">
                            <div class="mcase-li">
                                <a href="@if(Auth::check() && Auth::user()->id == $v['uid']) {!! '/user/personevaluationdetail/'.$v['id']  !!}
                                @elseif( !empty($v['url'])) {{  $v['url'] }}
                                @else {!! '/task/successDetail/'.$v['id']  !!} @endif" target="_blank">
                                    <img src="{!! url($v['pic']) !!}" alt=""
                                         onerror="onerrorImage('{{ Theme::asset()->url('images/index/banner-bg.png')}}',$(this))">
                                </a>
                                <div class="index-cardinfo text-center">
                                    <p class="text-size14 cor-gray24 p-space">
                                        <a href="@if(Auth::check() && Auth::user()->id == $v['uid']) {!! '/user/personevaluationdetail/'.$v['id']  !!}
                                        @elseif( !empty($v['url'])) {{  $v['url'] }}
                                        @else {!! '/task/successDetail/'.$v['id']  !!} @endif" target="_blank">
                                            {{$v['title']}}
                                        </a>
                                    </p>
                                    <span class="index-casetagray text-size12 cor-gray99">{{$v['cate_name']}}</span>
                                </div>
                            </div>
                        </li>
                    @endif
                @empty
                @endforelse
            </ul>
        </div>
        {{--<div class="space-30"></div>--}}
        {{--分页--}}
        <div class="service-thumbs">
            <div class=" paging_bootstrap text-right col-xs-offset-1">
                <ul class="pagination case-page-list ">
                    {!! $list->appends($merge)->render() !!}
                </ul>
            </div>
        </div>
    </div>
</div>
{!! Theme::asset()->container('custom-css')->usepath()->add('case','css/index/case.css') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('General-js', 'js/General.js') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('common-js', 'js/common.js') !!}