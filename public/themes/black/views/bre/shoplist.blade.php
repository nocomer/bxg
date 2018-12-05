<div class="container">
    @if(count($ad))
        <div class="mshop-bar">
            <a href="{!! $ad[0]['ad_url'] !!}">
                <img src="{!! URL($ad[0]['ad_file']) !!}" alt=""
                     onerror="onerrorImage('{{ Theme::asset()->url('images/index/shop-bar.jpg')}}',$(this))">
            </a>
        </div>
    @endif

	<div class="mshop-filter service-filter task-filter clearfix">
		<div class="col-sm-12">
			<div class="classify clickDownList">
				<span class="title text-center">类型</span>
				<a data-values="0" class="{!! !isset($merge['type'])?'mactive':'' !!}" href="{!! URl('bre/shop').'?'.http_build_query(array_except($merge,['page','type'])) !!}">全部</a>
				<a data-values="2" class="{!! (isset($merge['type']) && $merge['type'] == '2')?'mactive':'' !!}" href="{!! URL('/bre/shop').'?'.http_build_query(array_merge($merge, ['type'=> 2])) !!}">服务</a>
				<a data-values="1" class="{!! (isset($merge['type']) && $merge['type'] == '1')?'mactive':'' !!}"  href="{!! URL('/bre/shop').'?'.http_build_query(array_merge($merge, ['type'=> 1])) !!}">作品</a>
			</div>
		   <div class="clickDownList">
				<span class="title text-center">分类</span>
			   <a class="{!! (!isset($merge['category']) || $merge['category']==$pid)?'mactive':'' !!}" href="{!! URl('bre/shop').'?'.http_build_query(array_except($merge,['page','category'])) !!}">全部</a>
				@foreach(array_slice($category,0,7) as $v)
					<a class="{!! (isset($merge['category']) && $merge['category']==$v['id'])?'mactive':'' !!}" href="{!! URL('/bre/shop').'?'.http_build_query(array_merge($merge, ['category'=> $v['id']])) !!}">{{ $v['name'] }}</a>
				@endforeach
				@if(count($category)>7)
					<div class="pull-right select-fa-angle-down">
						<i class="fa fa-angle-down text-size14 show-next"></i>
					</div>
				@endif
			</div>
		</div>
	</div>

	<section>
		<input type="hidden" name="type_hi" value="{!! isset($merge['type']) ? $merge['type']: 0 !!}">
		<input type="hidden" name="desc_hi" value="{!! isset($merge['desc']) ? $merge['desc']:'' !!}">
		<div class="">
			<div class="space-20"></div>
			<div class="text-size14 mshop-sort clearfix">
				<div class="pull-left">
					<a data-values="" href="{!! URL('bre/shop').'?'.http_build_query(array_except($merge,['page','desc'])) !!}">综合</a>
					<a data-values="cash" href="{!! URL('bre/shop').'?'.http_build_query(array_merge($merge, ['desc'=> 'cash'])) !!}">金额 <i class="glyphicon glyphicon-arrow-down"></i></a>
					<a data-values="sales_num" href="{!! URL('bre/shop').'?'.http_build_query(array_merge($merge, ['desc'=> 'sales_num'])) !!}">成交量 <i class="glyphicon glyphicon-arrow-down"></i></a>
					<a data-values="good_comment" href="{!! URL('bre/shop').'?'.http_build_query(array_merge($merge, ['desc'=> 'good_comment'])) !!}">好评数 <i class="glyphicon glyphicon-arrow-down"></i></a>
				</div>
				<div class="pull-right">
					<form method="get" action="/bre/shop">
						<button class="mshop-sortbtn" type="submit"><i class="fa fa-search text-size18"></i></button>
						<input class="mshop-sortinp" type="text" placeholder="搜索关键词" name="title" value="{!! isset($merge['title'])?$merge['title']:'' !!}">
					</form>
				</div>
			</div>
			<div class="space-10"></div>
			<div class="mshop-device">
				<div class="device gridalicious" id="device">
					@forelse($goodsInfo as $gv)
					<div class="item">
						<div class="index-item">
							<div class="">
								<a @if($gv->type == 1) href="{!! URL('/shop/buyGoods/'.$gv->id) !!}"
								   @elseif($gv->type == 2) href="{!! URL('/shop/buyservice/'.$gv->id) !!}" @endif class="device-img">
									<p>
										<span>@if($gv->type == 1) 作品 @elseif($gv->type == 2) 服务 @endif</span>
									</p>
									<img src="{!! $domain.'/'.$gv->cover !!}" alt=""
										 onerror="onerrorImage('{{ Theme::asset()->url('images/employ/bg2.jpg')}}',$(this))">
								</a>
							</div>
							<div class="space-6"></div>
							<div class="index-serimg"><a class="cor-gray33" @if($gv->type == 1) href="{!! URL('/shop/buyGoods/'.$gv->id) !!}"
														 @elseif($gv->type == 2) href="{!! URL('/shop/buyservice/'.$gv->id) !!}" @endif>
									{!! $gv->title !!}
								</a>

								<div class="space-2"></div>
								<p class="cor-gray99">好评数：@if($gv->good_comment) {!! $gv->good_comment !!} @else 0 @endif
									<span class="address-ico">{!! $gv->addr !!}</span></p>

								<div class="space-4"></div>
								<p class="text-size14 cor-orange">￥{!! $gv->cash !!}</p></div>
							<div class="space-4"></div>
						</div>
					</div>
					@empty
					@endforelse
				</div>
			</div>
			<div class="space-30"></div>
		</div>
	</section>
</div>

<div id="goods" data-values="{{$goods_arr}}"></div>
<input type="hidden" id="domain" value="{{$domain}}">

{!! Theme::asset()->container('custom-css')->usepath()->add('case','css/index/case.css') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('General-js', 'js/General.js') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('wikShop-js', 'js/wikShop.js') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('common-js', 'js/common.js') !!}
