<div class="container">
<div class="mshop-filter service-filter task-filter clearfix">
    <div class="col-xs-12">
	    <div class="classify clickDownList">
            <span class="title">任务模式</span>
            <a class="{!! (!isset($merge['category']) || $merge['category']==$pid)?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,['keywords','page']),['category'=>0])) !!}">全部</a>
            @foreach($task_type as $Vtt)						
                <a class="{!! (isset($merge['taskType']) && $merge['taskType']==$Vtt->id)?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,'page'), ['taskType'=>$Vtt->id])) !!}">{!!mb_substr($Vtt->name,0,2)!!}任务</a>
			@endforeach
        </div>
        <div class="classify clickDownList">
            <span class="title">任务分类</span>
            <a class="{!! (!isset($merge['category']) || $merge['category']==$pid)?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,['keywords','page']),['category'=>0])) !!}">全部</a>
            @foreach(array_slice($category,0,7) as $v)
                <a class="{!! (isset($merge['category']) && $merge['category']==$v['id'])?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,'page'), ['category'=>$v['id']])) !!}">{{ $v['name'] }}</a>
            @endforeach
            @if(count($category)>7)
                <div class="pull-right select-fa-angle-down">
                    <i class="fa fa-angle-down text-size14 show-next"></i>
                </div>

                <div class="col-xs-12 clearfix service-type">
                    <div class="">
                        <div class="col-md-offset-1 cor99">
                            <div class="row">
                                @foreach(array_slice($category,7,(count($category)-7)) as $v)
                                    <a class="{!! (isset($merge['category']) && $merge['category']==$v['id'])?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,'page'), ['category'=>$v['id']])) !!}">{{ $v['name'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="classify clickDownList">
            <span class="title">任务状态</span>
            <a class="{!! (!isset($merge['status']))?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_except(array_except($merge,['keywords','paeg']),'status')) !!}">全部</a>
            <a class="{!! (isset($merge['status']) && $merge['status']==1)?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,'page'), ['status'=>1])) !!}">
                <i class="task-ico task-ico1"></i>
                工作中
            </a>
			{{--  <a class="{!! (isset($merge['status']) && $merge['status']==2)?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,'page'), ['status'=>2])) !!}">
                <i class="task-ico task-ico2"></i>
                选稿中
            </a>
            <a class="{!! (isset($merge['status']) && $merge['status']==3)?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,'page'), ['status'=>3])) !!}">
                <i class="task-ico task-ico3"></i>
                交付中
            </a> --}}
            <a class="{!! (isset($merge['status']) && $merge['status']==12)?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,'page'), ['status'=>12])) !!}">
                <i class="task-ico task-ico4"></i>
                已完成
            </a>

        </div>
        <div class="clickDownList">
            <span class="title">地区限制</span>
            @if(count($area)>7)
                <div class="pull-right select-fa-angle-down">
                    <i class="fa fa-angle-down text-size14 show-next"></i>
                </div>
            @endif
            @if(isset($_GET['province']))
                <a class="{!! ( $merge['province']==$area_pid)?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except(array_except($merge,['keywords','page']),['area','city','province']))) !!}">全部</a>
                @foreach(array_slice($area,0,7) as $v)
                    <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,['page','province']), ['city'=>$v['id']])) !!}">{{ $v['name'] }}</a>
                @endforeach
            @elseif(isset($_GET['city']))
                <a class="{!! ($merge['city']==$area_pid)?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,['area','city','province','page']))) !!}">全部</a>
                @foreach(array_slice($area,0,7) as $v)
                    <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,['page','city']), ['area'=>$v['id']])) !!}">{{ $v['name'] }}</a>
                @endforeach
            @elseif(isset($_GET['area']))
                <a class="{!! (!isset($_GET['area']) && $merge['area']==$area_pid)?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,['area','city','province','keywords','page']))) !!}">全部</a>
                @foreach(array_slice($area,0,7) as $v)
                    <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,'page'), ['area'=>$v['id']])) !!}">{{ $v['name'] }}</a>
                @endforeach
            @else
                <a class="mactive" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,['area','city','keywords','page']),['province'=>0])) !!}">全部</a>
                @foreach(array_slice($area,0,7) as $v)
                    <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,'page'), ['province'=>$v['id']])) !!}">{{ $v['name'] }}</a>
                @endforeach
            @endif

            {{--地区限制筛选--}}
            @if(count($area)>7)
                <div class="col-xs-12 clearfix service-area">
                    <div class="row">
                        <div class="col-md-offset-1 cor99">
                            <div class="">
                                @if(isset($_GET['province']))
                                    @foreach(array_slice($area,7,(count($area)-7)) as $v)
                                        <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,['page','province']), ['city'=>$v['id']])) !!}">{{ $v['name'] }}</a>
                                    @endforeach
                                @elseif(isset($_GET['city']))
                                    @foreach(array_slice($area,7,(count($area)-7)) as $v)
                                        <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,['page','city']), ['area'=>$v['id']])) !!}">{{ $v['name'] }}</a>
                                    @endforeach
                                @elseif(isset($_GET['area']))
                                    @foreach(array_slice($area,7,(count($area)-7)) as $v)
                                        <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,'page'), ['area'=>$v['id']])) !!}">{{ $v['name'] }}</a>
                                    @endforeach
                                @else
                                    @foreach(array_slice($area,7,(count($area)-7)) as $v)
                                        <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}" href="{!! URL('task').'?'.http_build_query(array_merge(array_except($merge,'page'), ['province'=>$v['id']])) !!}">{{ $v['name'] }}</a>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

<section>
    <div class="taskGroup">
        <div class="space-20"></div>
        <div class="text-size14 mshop-sort clearfix">
            <div class="pull-left">
                <a href="{!! URL('task').'?'.http_build_query(array_except($merge,['desc','keywords'])) !!}">综合</a>
                <a href="{!! URL('task').'?'.http_build_query(array_merge($merge,['desc'=>'created_at'])) !!}">发布时间 <i class="glyphicon glyphicon-arrow-down"></i></a>
                <a href="{!! URL('task').'?'.http_build_query(array_merge($merge,['desc'=>'delivery_count'])) !!}">稿件数 <i class="glyphicon glyphicon-arrow-down"></i></a>
                <a href="{!! URL('task').'?'.http_build_query(array_merge($merge,['desc'=>'bounty'])) !!}">金额 <i class="glyphicon glyphicon-arrow-down"></i></a>
            </div>
            <form action="/task" method="get" />
            <div class="pull-right">
                <button type="submit" class="mshop-sortbtn"><i class="fa fa-search text-size18"></i></button>
                <input class="mshop-sortinp" name="keywords"type="text" placeholder="请输入关键字">
            </div>
            </form>
        </div>
        <div class="space-10"></div>
        <div class="index-service task-list">
            <ul id="da-thumbs" class="da-thumbs clearfix">
                @forelse($list as $v)
                    <li class="taskList-item">
                        <div class="index-serwrap taskList-item-serwrap">
                            <div class=" taskList-item-serimg">
                                <h4 class="text-center title p-space">
                                    <a href="{{ URL('task').'/'.$v['id'] }}" target="_blank">
                                        {{ $v['title'] }}
                                    </a>
                                </h4>
                                <p class="text-center state">
                                    @if(!empty($task_service[$v['id']]))
                                        @for($i=0;$i<count($task_service[$v['id']]);$i++)
                                            @if($i%2==1)
                                                <span class="top">{{ substr($task_service[$v['id']][$i]['title'],3,3) }}</span>
                                            @else
                                                <span class="worry">{{ substr($task_service[$v['id']][$i]['title'],3,3) }}</span>
                                            @endif
                                        @endfor
                                    @endif
                                    <span class="money">@if($v['type_alias'] == "zhaobiao" && $v['bounty_status'] == 0)<strong>可议价</strong>@else￥{{ $v['bounty'] }}@endif</span>

                                    @if(Auth::check() && !in_array($v['id'],$my_focus_task_ids))
                                        <span class="collect u-collect " data-values="1" data-toggle="tooltip" data-placement="top" title="收藏" data-id="{{$v['id']}}" ></span>
                                    @elseif(Auth::check())
                                        <span class="collect u-collect active" data-values="2" data-toggle="tooltip" data-placement="top" title="收藏" data-id="{{$v['id']}}"></span>
                                    @else
                                        <span class="collect u-collect" data-values="1" data-toggle="tooltip" data-placement="top" title="收藏" data-id="{{$v['id']}}" ></span>
                                        {{--<span class="fa fa-star u-collect" ></span>--}}
                                    @endif

                                </p>
                            </div>
                            <div class="taskList-item-serinfo">
                                <p class="content">
                                    <a href="{{ URL('task').'/'.$v['id'] }}" target="_blank">{!! strip_tags(htmlspecialchars_decode($v['desc'])) !!}</a>
                                </p>
                                <p class="text-center timer">
                                    @if(strtotime($v['delivery_deadline'])>time() && ($v['status']==3 || $v['status']==4))
                                        <span class="task-ico task-ico1"></span>
                                        {{ CommonClass::changeTimeType(strtotime($v['delivery_deadline'])-time())}} 后截止投标
                                    @elseif($v['status']==5)
                                        <span class="task-ico task-ico2"></span>
                                        任务选稿中
                                    @elseif($v['status']==6)
                                        <span class="task-ico task-ico3"></span>
                                        任务公示中
                                    @elseif($v['status']==7)
                                        <span class="task-ico task-ico3"></span>
                                        任务交付中
                                    @elseif($v['status']==8)
                                        <span class="task-ico task-ico4"></span>
                                        任务评价中
                                    @elseif($v['status']==9)
                                        <span class="task-ico task-ico4"></span>
                                        任务已完成
                                    @endif
                                </p>
                                <hr>
                                <div class="clearfix taskList-footer">
                                    <div class="col-xs-6">
                                        <div class="row">
                                            <i class="fa fa-eye"></i> {{ $v['view_count'] }}人浏览/{{ $v['delivery_count'] }}人投稿
                                        </div>
                                    </div>
                                    <div class="col-xs-6">
                                        <div class="row text-right">
                                            <i class="fa fa-clock-o"></i>
                                            {{ $v['show_publish']}}&nbsp;&nbsp;&nbsp;
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @empty
                @endforelse


            </ul>
        </div>
        <div class="service-thumbs">
            <div class=" paging_bootstrap text-right">
                <ul class="pagination case-page-list">
                    {!! $list->appends($_GET)->render() !!}

                </ul>
            </div>
        </div>
        <div class="space-30"></div>
    </div>
</section>
</div>
{!! Theme::asset()->container('custom-css')->usepath()->add('case-css', 'css/index/case.css') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('General-js', 'js/General.js') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('common-js', 'js/common.js') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('taskindex','js/doc/taskindex.js') !!}