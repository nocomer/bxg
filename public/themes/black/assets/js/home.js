$(function() {

    makeboxes = function() {
        var boxes = new Array;
        var randTxt = eval($("#goods").attr('data-shop'));
        for(i=0;i<randTxt.length;i++){
            div = $('<div></div>').addClass('index-item');
            if (randTxt[i].type == 1){
                type = '作品';
                var url = '/shop/buyGoods/' + randTxt[i].id;
            } else {
                type = '服务';
                var url = '/shop/buyservice/' + randTxt[i].id;
            }
            if (randTxt[i].comments_num == 0){
                goodRate = 100;
            } else {
                goodRate = parseInt(randTxt[i].good_comment/randTxt[i].comments_num);
            }
            if (randTxt[i].addr == undefined){
                randTxt[i].addr = '';
            }
            img = "<div class=''>" +
                "<a href='" + url + "' class='device-img'><p><span>" + type + "</span></p>" +
                "<img src='/"+randTxt[i].cover+"'></a>" +
                "</div>";
            p = "<div class='space-6'></div>" +
                "<div class='index-serimg'><a href='" + url + "' class='cor-gray33'>"+randTxt[i].title+"</a>" +
                "<div class='space-2'></div>" +
                "<p class='cor-gray99'>好评率：" + goodRate + "%  <span class='address-ico'>"+ randTxt[i].addr +"</span></p>" +
                "<div class='space-4'></div><p class='text-size14 cor-orange'>￥"+ randTxt[i].cash + "</p></div><div class='space-4'></div>";
            div.append(img);
            div.append(p);
            boxes.push(div);
        }
        return boxes;
    };
    var device = 0;
    $(window).scroll(function () {
        if(($(window).scrollTop() + $(window).height()) == $(document).height() && device < 1)
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
            duration: 400,
            complete: onComplete
        },
    });

    function onComplete(data) {

    }

    $.ajaxSettings.async = false;
    var data = eval($("#danmu").attr('data-danmu'));
    for (i = 0; i < data.length; i++){
        data[i] = {
            'info' : data[i].title + ' ' + '￥' + data[i].show_cash,
            'href' : '/task/' + data[i].id
        };

    };
    var looper_time=3*1000;
    var items=data;
    var total=data.length;
    var run_once=true;
    var indexs=0;
    barrager();
    function  barrager(){

        if(run_once){

            looper = setInterval(barrager, looper_time);
            run_once = false;

        }
        $('.index-barrage').barrager(items[indexs]);
    }
});

function autoScroll(obj){
    $(obj).find("ul").animate({
        marginTop : "-57px"
    },500,function(){
        $(this).css({marginTop : "0px"}).find("li:first").appendTo(this);
    })
}
$(function(){
    setInterval('autoScroll(".maquee")',5000);

});


