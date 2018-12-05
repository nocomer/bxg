
$(function(){
    var device = 0;
    var numPage = 2;

    /*if(($(window).scrollTop() + $(window).height()) == $(document).height() && device < 1)
    {
        $("#device").gridalicious('append', makeboxes())
    }*/
    makeboxes = function(str) {
        var boxes = new Array;
        var domain = $('#domain').val();
        var randTxt = eval($("#goods").attr('data-values'));

        if(str){
            var randTxt = str;
        }else{
            var randTxt = eval($("#goods").attr('data-values'));
        }
        var amount = 10 + Math.floor(Math.random()*10); if (amount == 0) amount = 1;
        for(i=0;i<randTxt.length;i++){
            num = Math.floor(Math.random()*randTxt.length);
            div = $('<div></div>').addClass('index-item');
            if (randTxt[i].type == 1){
                type = '作品';
                var url = '/shop/buyGoods/' + randTxt[i].id;
            } else {
                type = '服务';
                var url = '/shop/buyservice/' + randTxt[i].id;
            }
            if(randTxt[i].good_comment == null){
                good_comment = 0;
            }else{
                good_comment = randTxt[i].good_comment;
            }
            imgsrc = domain + "/" +randTxt[i].cover;
            img = "<div class=''><a href='"+ url +"' class='device-img'><p><span>" + type + "</span></p><img src='"+imgsrc+"'></a></div>";
            p = "<div class='space-6'></div>" +
                "<div class='index-serimg'>" +
                "<a class='cor-gray33' href='"+ url +"'>"+ randTxt[i].title+"</a>" +
                "<div class='space-2'></div>" +
                "<p class='cor-gray99'>好评数："+ good_comment +
                "<span class='address-ico'>"+ randTxt[i].addr +"</span></p>" +
                "<div class='space-4'></div>" +
                "<p class='text-size14 cor-orange'>￥"+ randTxt[i].cash +"</p></div>" +
                "<div class='space-4'></div>";
            div.append(img);
            div.append(p);
            boxes.push(div);
        }
        return boxes;
    };
    $(window).scroll(function () {
        if(($(window).scrollTop() + $(window).height()) == $(document).height())
        {
            //$("#device").gridalicious('append', makeboxes());
            //device++;

            var page = numPage++;
            var type = $('input[name="type_hi"]').val();
            var title = $('input[name="title"]').val();
            var desc = $('input[name="desc_hi"]').val();
            $.ajax({
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '/bre/ajaxGoodsList',
                data: {page:page,type:type,title:title,desc:desc},
                dataType:'json',
                success: function(data){
                    if(data.code == 1){
                        $("#device").gridalicious('append', makeboxes(eval(data.data)));
                    }
                }
            });
        }
    });
    $("#device").gridalicious({
        gutter: 20,
        width: 224,
        animate: true,
        animationOptions: {
            speed: 150,
            duration: 400,
            complete:function(data){
            }
        }
    });
});



