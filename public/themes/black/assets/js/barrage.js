$.ajaxSettings.async = false;
var data = [{'info' : '第一条弹幕',
    'href' : 'http://www.yaseng.org',},{'info' : '¥2300.00    微信手机...',
    'href' : 'http://www.yaseng.org',},{'info' : '¥5400.00    微信手机...',
    'href' : 'http://www.yaseng.org',},{'info' : '¥1100.00    微信手机...',
    'href' : 'http://www.yaseng.org',},{'info' : '¥300.00    微信手机...',
    'href' : 'http://www.yaseng.org',},{'info' : '¥5000.00    微信手机...',
    'href' : 'http://www.yaseng.org',},{'info' : '¥600.00    微信手机...',
    'href' : 'http://www.yaseng.org',},{'info' : '¥100.00    微信手机...',
    'href' : 'http://www.yaseng.org',},{'info' : '¥100.00    微信手机...',
    'href' : 'http://www.yaseng.org',}]
    //每条弹幕发送间隔
    var looper_time=3*1000;
    var items=data;
    //弹幕总数
    var total=data.length;
    //是否首次执行
    var run_once=true;
    //弹幕索引
    var index=0;
    //先执行一次
    barrager();
    function  barrager(){


        if(run_once){
            //如果是首次执行,则设置一个定时器,并且把首次执行置为false
            looper=setInterval(barrager,looper_time);
            run_once=false;
        }
        //发布一个弹幕
        $('.index-barrage').barrager(items[index]);
        //索引自增
        index++;
        //所有弹幕发布完毕，清除计时器。
        if(index == total){

            clearInterval(looper);
            return false;
        }




}