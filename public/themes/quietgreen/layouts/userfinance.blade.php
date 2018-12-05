<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{!! Theme::get('title') !!}</title>
    <meta name="description" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    {{--<meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=0">--}}
    @if(Theme::get('basis_config')['css_adaptive'] == 1)
        <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=0">
    @else
        <meta name="viewport" content="initial-scale=0.1">
        @endif
    @if(!empty(Theme::get('site_config')['browser_logo']) && is_file(url(Theme::get('site_config')['browser_logo'])))
        <link rel="shortcut icon" href="{{ url(Theme::get('site_config')['browser_logo']) }}" />
    @else
        <link rel="shortcut icon" href="{{ Theme::asset()->url('images/favicon.ico') }}" />
        @endif
    <!-- Place favicon.ico in the root directory -->
    <link rel="stylesheet" href="/themes/quietgreen/assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/themes/quietgreen/assets/plugins/ace/css/ace.min.css">
    <link rel="stylesheet" href="/themes/quietgreen/assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="/themes/quietgreen/assets/css/main.css">
    <link rel="stylesheet" href="/themes/quietgreen/assets/css/header.css">
    <link rel="stylesheet" href="/themes/quietgreen/assets/css/index/common.css">
    <link rel="stylesheet" href="/themes/quietgreen/assets/css/usercenter/finance/finance-layout.css">
    <link rel="stylesheet" href="/themes/quietgreen/assets/css/usercenter/finance/finance-detail.css">
    <link rel="stylesheet" href="/themes/quietgreen/assets/css/{!! Theme::get('color') !!}/style.css">
    <link rel="stylesheet" href="/themes/quietgreen/assets/css/{!! Theme::get('color') !!}/user.css">
    {!! Theme::asset()->container('specific-css')->styles() !!}
    {!! Theme::asset()->container('custom-css')->styles() !!}
</head>
<body>
<header>
    <div class="header-top">
        <div class="container clearfix col-left">
                {!! Theme::partial('usernav') !!}
        </div>
    </div>
</header>

<section>
    <div class="container col-left">
            {!! Theme::content() !!}
    </div>
</section>

<footer>
    {!! Theme::partial('footer') !!}
</footer>


<script src="/themes/quietgreen/assets/plugins/jquery/jquery.min.js"></script>
<script src="/themes/quietgreen/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="/themes/quietgreen/assets/plugins/ace/js/ace.min.js"></script>
<script src="/themes/quietgreen/assets/plugins/ace/js/ace-elements.min.js"></script>
<script src="/themes/quietgreen/assets/js/common.js"></script>
{!! Theme::asset()->container('specific-js')->scripts() !!}


{!! Theme::asset()->container('custom-js')->scripts() !!}

</body>
</html>

