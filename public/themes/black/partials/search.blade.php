<div class="container search-pd">
    <div class="row">
        <div class="col-md-3 brand">
            <img src="{!! Theme::asset()->url('img/logo.png') !!}" alt="kppw">
        </div>
        <div class="col-md-9 hidden-xs">
            <form class="form-inline row">
                <div class=" col-md-12">
                    <div class="input-group search-bd col-md-9">
                        <div class="input-group-btn btn-wd">
                            <a type="button" class="btn  btn-color" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                找任务 <span class="fa fa-angle-down"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="#">找任务</a></li>
                                <li><a href="#">作品</a></li>
                                <li><a href="#">服务商</a></li>
                            </ul>
                        </div><!-- /btn-group -->
                        <input type="search" class="form-control search-input" placeholder="请输入关键词">
                                <span class="input-group-btn btn-wd">
                                  <button class="btn btn-search" type="button">搜索</button>
                               </span>
                    </div><!-- /input-group -->
                    <div class="form-group">
                        <span class="wd">或者<a href="{{ URL('task/create') }}"><button class="btn btn-primary btn-search btn-search-wd" type="button">发布需求 <span class="fa fa-caret-down"></span></button></a></span>
                    </div>
                </div>
            </form>
            <div class="search-words">
                热门搜索：
                <a href="javascript:;">宣传品设计</a> &nbsp;
                <a href="javascript:;">logo设计</a> &nbsp;
                <a href="javascript:;">微信推广</a> &nbsp;
                <a href="javascript:;">画册印刷</a> &nbsp;
                <a href="javascript:;">商标注册</a> &nbsp;
                <a href="javascript:;">企业形象设计</a> &nbsp;
                <a href="javascript:;">产品设计</a> &nbsp;
                <a href="javascript:;">包装设计</a> &nbsp;
            </div>
        </div>
    </div>
</div>