<section>
    <div class="container">
        <div class="consult-nav">
            @if(!empty($category->toArray()))
                @foreach($category as $v)
                    <a href="{!! URL('article?catID='.$v->id) !!}" class="@if($catID == $v->id  ) active @endif" >{{ $v['cate_name'] }}</a>
                @endforeach
            @endif
        </div>
        <!--内容-->
        <div class="consult-main">
            <div class="consult-list">

                @if(!empty($list['data']))
                <ul class=" clearfix">
                    @foreach($list['data'] as $v)
                        <li class="consult-list-item clearfix">
                            <div class="pull-left consult-item-l">
                                <div class="item-radius">
                                    <p class="date-month">{{ date('Y',strtotime($v['created_at'])) }}年{{ date('m',strtotime($v['created_at'])) }}月</p>
                                    <p class="date-num">{{ date('d',strtotime($v['created_at'])) }}</p>
                                </div>
                            </div>
                            <div class="pull-left consult-item-r">
                                <div class="arrow">
                                    <div class="arrow-sanjiao"></div>
                                    <div class="arrow-sanjiao-big"></div>
                                </div>
                                <div class="content">
                                    <h4 class="title"><a href="{!! URL('article/'.$v['id']) !!}">{{  $v['title'] }}</a></h4>
                                    <p class="p-space details"><a href="{!! URL('article/'.$v['id']) !!}">{{ $v['summary'] }}</a></p>
                                    <p class="p-space time">
                                        <span class="date-timer"><i class="fa fa-clock-o"></i> 发表时间：{{ $v['created_at'] }} </span>
                                        <span class="attention">
                                            <i class="fa fa-star-o"></i> 关注度： @if(!empty( $v['view_times'])){{ $v['view_times'] }}@else 0 @endif
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                @endif
                {{--分页--}}
                <div class="service-thumbs">
                    <div class=" paging_bootstrap text-left col-xs-offset-1">
                        <ul class="pagination case-page-list ">
                            {!! $list_obj->appends($merge)->render() !!}
                            {{--<ul class="pagination">
                                <li class="disabled"><span>Previous</span></li>
                                <li class="active"><span>1</span></li>
                                <li><a href="http://kppw31.io/bre/service?page=2">2</a></li>
                                <li><a href="http://kppw31.io/bre/service?page=3">3</a></li>
                                <li><a href="http://kppw31.io/bre/service?page=2" rel="next">Next</a></li>
                            </ul>--}}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


{!! Theme::asset()->container('custom-css')->usepath()->add('case-css', 'css/index/case.css') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('General-js', 'js/General.js') !!}
{!! Theme::asset()->container('custom-js')->usepath()->add('common-js', 'js/common.js') !!}