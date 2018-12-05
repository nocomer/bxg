<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{!! Theme::get('title') !!}</title>
    <meta name="keywords" content="{!! Theme::get('keywords') !!}">
    <meta name="description" content="{!! Theme::get('description') !!}">
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
    <link rel="stylesheet" href="/themes/black/assets/plugins/bootstrap/css/bootstrap.min.css">
    {!! Theme::asset()->container('specific-css')->styles() !!}
    <link rel="stylesheet" href="/themes/black/assets/plugins/ace/css/ace.min.css">
    <link rel="stylesheet" href="/themes/black/assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="/themes/black/assets/css/index/index.css">
    {!! Theme::asset()->container('custom-css')->styles() !!}
</head>
<body>

<header>
    {!! Theme::partial('homeheader') !!}
</header>

    {!! Theme::content() !!}

<footer>
    {!! Theme::partial('footer') !!}
</footer>

<script src="/themes/black/assets/plugins/jquery/jquery.min.js"></script>
<script src="/themes/black/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/themes/black/assets/plugins/jquery/modernizr.custom.97074.js"></script>
<script type="text/javascript" src="/themes/black/assets/plugins/jquery/jquery.hoverdir.js"></script>
<script type="text/javascript" src="/themes/black/assets/plugins/jquery/jquery.grid-a-licious.min.js"></script>
<script type="text/javascript" src="/themes/black/assets/plugins/jquery/jquery.barrager.min.js"></script>
<script type="text/javascript" src="/themes/black/assets/js/index.js"></script>
<script type="text/javascript" src="/themes/black/assets/js/home.js"></script>
<script type="text/javascript" src="/themes/black/assets/js/common.js"></script>

{!! Theme::asset()->container('specific-js')->scripts() !!}

{!! Theme::asset()->container('custom-js')->scripts() !!}
</body>