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
        <div class="classify showSideDown ">
            <span class="title">服务商分类</span>
            <a class="{!! (!isset($merge['category']) || $merge['category']==$pid)?'mactive':'' !!}"
               href="{!! URL('bre/service') !!}">全部
            </a>
            @forelse($category as $k => $v)
                @if($k < 7)
                <a class="{!! (isset($merge['category']) && $merge['category']==$v['id'])?'mactive':'' !!}"
                   href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except($merge,'page'), ['category'=>$v['id']])) !!}">
                    {{ $v['name'] }}
                </a>
                @endif
            @empty
            @endforelse
            @if(count($area)>7)
                <div class="pull-right select-fa-angle-down">
                    <i class="fa fa-angle-down text-size14 show-next"></i>
                </div>
            @endif

            {{--服务商筛选--}}
            <div class="col-md-12 col-xs-12 serivcelist-type">
                <div class="row">
                @if(count($category)>7)
                    <div class="col-md-offset-1 cor99">
                        <div class="">
                            @foreach(array_slice($category,7,(count($category)-7)) as $v)
                                <a class="{!! (isset($merge['category']) && $merge['category']==$v['id'])?'mactive':'' !!}"
                                   href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except($merge,'page'), ['category'=>$v['id']])) !!}">{{ $v['name'] }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
                </div>
            </div>
        </div>


        <div class="showSideDown">
            <span class="title">服务商地区</span>
            @if(isset($_GET['province']))
                <a class="{!! ( $merge['province']==$area_pid)?'mactive':'' !!}"
                   href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except(array_except($merge,['keywords','page']),['area','city','province']))) !!}">
                    全部
                </a>
                @foreach(array_slice($area,0,7) as $v)
                    <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}"
                       href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except($merge,['page','province']), ['city'=>$v['id']])) !!}">
                        {{ $v['name'] }}
                    </a>
                @endforeach
            @elseif(isset($_GET['city']))
                <a class="{!! ($merge['city']==$area_pid)?'mactive':'' !!}" href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except($merge,['area','city','province','page']))) !!}">
                    全部
                </a>
                @foreach(array_slice($area,0,7) as $v)
                    <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}"
                       href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except($merge,['page','city']), ['area'=>$v['id']])) !!}">
                        {{ $v['name'] }}
                    </a>
                @endforeach
            @elseif(isset($_GET['area']))
                <a class="{!! (!isset($_GET['area']) && $merge['area']==$area_pid)?'mactive':'' !!}"
                   href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except($merge,['area','city','province','keywords','page']))) !!}">
                    全部
                </a>
                @foreach(array_slice($area,0,7) as $v)
                    <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}"
                       href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except($merge,'page'), ['area'=>$v['id']])) !!}">
                        {{ $v['name'] }}
                    </a>
                @endforeach
            @else
                <a class="mactive"
                   href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except($merge,['area','city','keywords','page']),['province'=>0])) !!}">
                    全部
                </a>
                @foreach(array_slice($area,0,7) as $v)
                    <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}"
                       href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except($merge,'page'), ['province'=>$v['id']])) !!}">
                        {{ $v['name'] }}
                    </a>
                @endforeach
            @endif

            @if(count($area)>7)
                <div class="pull-right select-fa-angle-down">
                    <i class="fa fa-angle-down text-size14 show-next"></i>
                </div>
            {{--@endif


            @if(count($area)>7)--}}
                <div class="col-xs-12 clearfix service-area">
                    <div class="row">
                        <div class="col-md-offset-1 cor99">
                            <div class="row">
                                @if(isset($_GET['province']))
                                    @foreach(array_slice($area,7,(count($area)-7)) as $v)
                                        <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}"
                                           href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except($merge,['page','province']), ['city'=>$v['id']])) !!}">{{ $v['name'] }}</a>
                                    @endforeach
                                @elseif(isset($_GET['city']))
                                    @foreach(array_slice($area,7,(count($area)-7)) as $v)
                                        <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}"
                                           href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except($merge,['page','city']), ['area'=>$v['id']])) !!}">{{ $v['name'] }}</a>
                                    @endforeach
                                @elseif(isset($_GET['area']))
                                    @foreach(array_slice($area,7,(count($area)-7)) as $v)
                                        <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}"
                                           href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except($merge,'page'), ['area'=>$v['id']])) !!}">{{ $v['name'] }}</a>
                                    @endforeach
                                @else
                                    @foreach(array_slice($area,7,(count($area)-7)) as $v)
                                        <a class="{!! (isset($merge['area']) && $merge['area']==$v['id'])?'mactive':'' !!}"
                                           href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except($merge,'page'), ['province'=>$v['id']])) !!}">{{ $v['name'] }}</a>
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
    <div class="">
        <div class="space-20"></div>
        <div class="text-size14 mshop-sort clearfix">
            <div class="pull-left">
                <a href="/bre/service">综合</a>
                <a href="{!! URL('bre/service').'?'.http_build_query(array_merge(array_except($merge,'page'), ['employee_praise_rate'=>1]))!!}">好评数 <i class="glyphicon glyphicon-arrow-down"></i></a>
            </div>
            <form method="get" action="{!! URL('bre/service').'?'.http_build_query($merge)!!}">
                <div class="pull-right">
                    <button class="mshop-sortbtn" type="submit"><i class="fa fa-search text-size18"></i></button>
                    <input class="mshop-sortinp" name="service_name" type="text" placeholder="搜索服务商名称"
                           @if(!empty($merge['service_name']))value="{{$merge['service_name']}}"@endif>
                </div>
            </form>
        </div>
        <div class="space-10"></div>
        <div class="index-service">
            <ul id="da-thumbs" class="da-thumbs clearfix">
                @if(!empty($list))
                    @foreach($list as $item)
                        <li>
                            <div class="index-serwrap">
                                <div class="index-serimg">
                                    <a target="_blank" href="@if($item->shop_status == 1 && $item->shopId)
                                    {!! url('shop/'.$item->shopId) !!} @else{!! URL('bre/serviceEvaluateDetail/'.$item->id) !!}@endif">
                                        <img src="@if($item->avatar){!! URL($item->avatar) !!}
                                        @else {!! Theme::asset()->url('images/default_avatar.png') !!} @endif" alt=""
                                             onerror="onerrorImage('{{ Theme::asset()->url('images/employ/bg2.jpg')}}',$(this))">
                                        <div class="index-serimgp">
                                            <div class="space-6"></div>
                                            <p class="no-margin-bottom">服务：@if(empty($item->skill))
                                                    暂无标签
                                                @else
                                                    @foreach($item->skill as $value)
                                                        {!! $value !!}&nbsp;&nbsp;
                                                    @endforeach
                                                @endif</p>
                                            <div class="space-2"></div>
                                            @if($item->pre && $item->city)
                                                <p>{!! $item->pre.$item->city !!}</p>
                                            @endif

                                        </div></a>
                                </div>
                                <div class="index-serinfo">
                                    <div class="space-6"></div>
                                    <a class="cor-gray24 text-size14" target="_blank" href="@if($item->shop_status == 1 && $item->shopId)
                                    {!! url('shop/'.$item->shopId) !!} @else{!! URL('bre/serviceEvaluateDetail/'.$item->id) !!}@endif">
                                        {!! $item->name !!}
                                    </a>
                                    <div class="space-4"></div>
                                    <p class="no-margin-bottom clearfix">
                                        <span class="text-size12 pull-left">好评数
                                            <span class="cor-orange">{!! $item->employee_praise_rate !!}个</span>
                                        </span>
                                    <span class="pull-right">
                                        @if(isset($item->auth) && $item->auth['bank'] == true)
                                            <i class="bank-attestation"></i>
                                        @else
                                            <span class="s-servicericon bank-attestation-no"></span>
                                        @endif
                                        @if(isset($item->auth) && $item->auth['realname'] == true)
                                            <i class="cd-card-attestation"></i>
                                        @else
                                            <span class="s-servicericon cd-card-attestation-no"></span>
                                        @endif
                                        @if($item->email_status == 2)
                                            <i class="email-attestation"></i>
                                        @else
                                            <span class="s-servicericon email-attestation-no"></span>
                                        @endif
                                        @if(isset($item->auth) && $item->auth['alipay'] == true)
                                            <i class="alipay-attestation"></i>
                                        @else
                                            <span class="s-servicericon alipay-attestation-no"></span>
                                        @endif
                                        @if(isset($item->auth) && $item->auth['enterprise'] == true)
                                            <i class="com-attestation"></i>
                                        @else
                                            <span class="s-servicericon company-attestation-no"></span>
                                        @endif
                                    </span>
                                    </p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                @endif
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