$(function() {

    $(' .index-serimg ').each( function() { $(this).hoverdir({
        hoverDelay : 75
    }); } );

    var listhight = $('.banner-list').height();
    var listhide = $('.banner-listhide').height();
    $('.banner-listdown').on('click',function(){
        if($('.banner-listhide').height() == 170){
            $('.banner-list').animate({'height':'437'});
            $('.banner-listhide').animate({'height':'437'});
            $(this).find('.fa').removeClass('fa-angle-double-down').addClass('fa-angle-double-up');
            return;
        }else{
            $('.banner-list').animate({'height':listhight});
            $('.banner-listhide').animate({'height':listhide});
            $(this).find('.fa').removeClass('fa-angle-double-up').addClass('fa-angle-double-down');
            return;
        }
    });


    /*var device = 0;
    $(window).scroll(function () {
        if(($(window).scrollTop() + $(window).height()) == $(document).height() && device < 2)
        {
            $("#device").gridalicious('append', makeboxes());
            device++;
        }
    });
    $("#device").gridalicious({
        gutter: 20,
        width: 224,
        animate: true,
        animationOptions: {
            speed: 150,
            duration: 400
        },
    });
    if(($(window).scrollTop() + $(window).height()) == $(document).height() && device < 2)
    {
        $("#device").gridalicious('append', makeboxes());
    }*/

});