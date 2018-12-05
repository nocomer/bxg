$(function() {
    //倒计时
    function timer() {
        var delivery_deadline = $('.timer-check').attr('delivery_deadline');

        var timestamp2 = Date.parse(new Date(delivery_deadline));
        var ts = timestamp2 - (new Date());//计算剩余的毫秒数
        var dd = parseInt(ts / 1000 / 60 / 60 / 24, 10);//计算剩余的天数
        var hh = parseInt(ts / 1000 / 60 / 60 % 24, 10);//计算剩余的小时数
        var mm = parseInt(ts / 1000 / 60 % 60, 10);//计算剩余的分钟数
        var ss = parseInt(ts / 1000 % 60, 10);//计算剩余的秒数
        var timer = dd + "天" + hh + "时" + mm + "分" + ss + "秒";
        $('.timer-check').html(timer);
    }

    if ($('.timer-check').length) {
        setInterval(timer, 1000);
    }
    //top
    $(window).on('scroll', function () {
        var st = $(document).scrollTop();
        if (st > 0) {
            if ($('#main-container').length != 0) {
                var w = $(window).width(), mw = $('#main-container').width();
                if ((w - mw) / 2 > 70)
                    $('#go-top').css({'left': (w - mw) / 2 + mw + 20});
                else {
                    $('#go-top').css({'left': 'auto'});
                }
            }
            $('#go-top').fadeIn(function () {
                $(this).removeClass('dn');
            });
        } else {
            $('#go-top').fadeOut(function () {
                $(this).addClass('dn');
            });
        }
    });
    $('#go-top .go').on('click', function () {
        $('html,body').animate({'scrollTop': 0}, 500);
    });
    $("#go-top .uc-2vm ").bind('mouseenter', function () {
        $('#go-top .u-pop').show();
    });
    $("#go-top .uc-2vm ").bind('mouseleave', function () {
        $('#go-top .u-pop').hide();
    });
    $("#go-top .feedback ").bind('mouseenter', function () {
        $('#go-top .dnd').show();
    });
    $("#go-top .feedback ").bind('mouseleave', function () {
        $('#go-top .dnd').hide();
    });


    //top nav
    var divHoverLeft = 0;
    var aWidth = 0;

    $(document).ready(function () {
        var hWidth;
        var hLeft;
        if ($('.div-hover').length > 0) {
            if ($('.header-show').length > 0) {
                $('.header-show').show();
                hWidth = $('.hActive .topborbtm').width();
                hLeft = GetthisLeft($(".hActive")) + 12;
                $('.header-show').hide();
            } else {
                hWidth = $('.hActive .topborbtm').width();
                hLeft = GetthisLeft($(".hActive")) + 12;
            }
            $('.div-hover').css('width', hWidth);
            $('.div-hover').css('left', hLeft);
        }
        $(".topborbtm").on({
            'mouseenter': function () {
                SetDivHoverWidthAndLeft(this);
                $(".div-hover").stop().animate({width: aWidth - 24, left: divHoverLeft + 12}, 150);
            }
        });
        $(".topborbtm").on({
            'mouseleave': function (event) {
                $(".div-hover").stop().animate({width: hWidth, left: hLeft}, 150);
            }
        });
    });
    function SetDivHoverWidthAndLeft(element) {
        divHoverLeft = GetLeft(element);
        aWidth = GetWidth(element);
    }

    function GetWidth(ele) {
        return $(ele).parent().width();
    }

    function GetLeft(element) {
        var menuList = $(element).parent().prevAll();
        var left = 0;
        $.each(menuList, function (index, ele) {
            left += $(ele).width();
        });
        return left;
    }

    function GetthisLeft(element) {
        var menuList = $(element).prevAll();
        var left = 0;
        $.each(menuList, function (index, ele) {
            left += $(ele).width();
        });
        return left;
    }

    $(function () {
        $('.header-dropul').eq(0).show();
        $('.header-droptitle a').on('mouseover', function () {
            $('.header-dropul').hide();
            $('.header-dropul').eq($(this).index()).show();
        });

    });


    setTimeout("hovInputt()", 3000);

    function keyDownVal(elm) {
        $(elm).keydown(function (event) {
            $(this).siblings('.user-position').css({
                'top': '-20px'
            });
            $(this).parent().siblings('.user-position').css({
                'top': '-20px'
            });
            var sky = $('.inputLength').val();
        })
    }

    keyDownVal('.inputLength');

    function loginH(elm) {
        var loginH = $(elm);
        var h = loginH.height();

        loginH.css({
            'margin-top': '-' + h / 2 + 'px'
        })

    }

    loginH('.sign-main-bg .sign-main-content');


    function clickDownList(obj) {
        $(obj).click(function () {
            if ($(this).hasClass('fa-angle-down')) {

                $(this).addClass('fa-angle-up')
                    .removeClass('fa-angle-down');

                $(this).parent().parent().css({
                    'height': 'auto'
                });
            } else {

                $(this).addClass('fa-angle-down')
                    .removeClass('fa-angle-up');
                $(this).parent().parent().css({
                    'height': '70px'
                });
            }
        })
    }

    clickDownList('.show-next');

    function searchFlex(obj, add, input, width, attribute) {
        $(obj).on('click', function () {
            $(this).parent().toggleClass(add);
            var keys = $(this).siblings(input).val();
            if (keys.length > 0) {
                $(this).parent().width(width);
                $(this).css({
                    'background': 'none'
                });
                keys.val('');
                $(this).submit();
            } else {
                if ($(this).parent().hasClass(add)) {
                    $(this).css({
                        'background': 'none'
                    });
                } else {
                    $(this).css({
                        'background': attribute
                    });
                }
                return false;
            }
        })
    }

    searchFlex('.header-right .header-search .fom-search .fa-search', 'search_open', '.inputx', '166px', '#282828');


    function toggleUp(obj) {
        $(obj).on('click', function () {
            if ($(this).find('i').hasClass('glyphicon-arrow-down')) {
                $(this).find('i').addClass('glyphicon-arrow-up').removeClass('glyphicon-arrow-down')
            } else {
                $(this).find('i').addClass('glyphicon-arrow-down').removeClass('glyphicon-arrow-up')
            }
        })
    }

    toggleUp('.mshop-sort a');


    function hov(obj) {
        $(obj).hover(function () {
            $('.foc-ewm').toggle()
        })
    }

    hov('.ifooter-wx');

});

//onerror加载默认图片
function onerrorImage(url, obj) {
    obj.attr('src', url);
}

function hovInputt() {

    var sky = $('.inputLength').val();
    if (sky != '') {
        $('.user-position').css({
            'top': '-20px'
        })
    }

}

function switchSearch(obj) {
    var url = $(obj).attr('url');
    var name = $(obj).text();

    $(obj).closest('form').attr('action', url);
    $(obj).closest('ul').parents().find("a:firstChild").text(name);
}