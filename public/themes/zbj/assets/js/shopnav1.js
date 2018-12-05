jQuery(function($){
	var shopId = $("#shopId").val();
	var url = '/shop/navList';
	var path = window.location.pathname;
	$.get(url,{shopId:shopId},function(data){
		if(data != false){
			$("div[class='shop-navlist clearfix']").html('');
			$("ul[class='nav navbar-nav']").html('');
			var count = data.length;

			for(var i= 0; i < count;i++){
				var content = "";
				var resContent = "";
				switch(data[i]['id']){
					case 1:
					if(data[i]['status']){
						content ='';
						content+="<a href="+data[i]['url']+" ";
						if(path == data[i]['url']){
							content+="class='shop-navact'>"+data[i]['name']+"</a>";
						}else{
							content+=">"+data[i]['name']+"</a>";
						}
						resContent = "<li><a href="+data[i]['url']+">"+data[i]['name']+"</a></li>";
					}
					break;
					case 2:
					if(data[i]['status']){
						content ='';
						content+="<a href="+data[i]['url']+" ";
						if(path == data[i]['url']){
							content+="class='shop-navact'>"+data[i]['name']+"</a>";
						}else{
							content+=">"+data[i]['name']+"</a>";
						}
						resContent = "<li><a href="+data[i]['url']+">"+data[i]['name']+"</a></li>";
					}
					break;
					case 3:
					if(data[i]['status']){
						content = "<a href='/shop/serviceAll/'+shopId @if($_SERVER['REQUEST_URI'] == '/shop/serviceAll/'.Theme::get('SHOPID')) class=\"shop-navact\" @endif>"+data[i]['name']+"</a>";
//						content = '<a href=/shop/serviceAll/'+shopId+'>'+data[i]['name']+'</a>'
						resContent = "<li><a href=\"/shop/serviceAll/{!! Theme::get('SHOPID') !!}\" >"+data[i]['name']+"</a></li>";
						content = "<a href=\"/shop/serviceAll/{!! Theme::get('SHOPID') !!}\" @if($_SERVER['REQUEST_URI'] == '/shop/serviceAll/'.Theme::get('SHOPID')) class=\"shop-navact\" @endif>"+data[i]['name']+"</a>";
						resContent = "<li><a href=\"/shop/serviceAll/{!! Theme::get('SHOPID') !!}\" >"+data[i]['name']+"</a></li>";
						content ='';
						content+="<a href="+data[i]['url']+" ";
						if(path == data[i]['url']){
							content+="class='shop-navact'>"+data[i]['name']+"</a>";
						}else{
							content+=">"+data[i]['name']+"</a>";
						}
						resContent = "<li><a href="+data[i]['url']+">"+data[i]['name']+"</a></li>";
>>>>>>> .r19497
					}
					break;
					case 4:
					if(data[i]['status']){
						content ='';
						content+="<a href="+data[i]['url']+" ";
						if(path == data[i]['url']){
							content+="class='shop-navact'>"+data[i]['name']+"</a>";
						}else{
							content+=">"+data[i]['name']+"</a>";
						}
						resContent = "<li><a href="+data[i]['url']+">"+data[i]['name']+"</a></li>";
					}
					break;
					case 5:
					if(data[i]['status']){
						content ='';
						content+="<a href="+data[i]['url']+" ";
						if(path == data[i]['url']){
							content+="class='shop-navact'>"+data[i]['name']+"</a>";
						}else{
							content+=">"+data[i]['name']+"</a>";
						}
						resContent = "<li><a href="+data[i]['url']+">"+data[i]['name']+"</a></li>";
					}
					break;
					case 6:
					if(data[i]['status']){
						content ='';
						content+="<a href="+data[i]['url']+" ";
						if(path == data[i]['url']){
							content+="class='shop-navact'>"+data[i]['name']+"</a>";
						}else{
							content+=">"+data[i]['name']+"</a>";
						}
						resContent = "<li><a href="+data[i]['url']+">"+data[i]['name']+"</a></li>";
					}
					break;
				}
				$("div[class='shop-navlist clearfix']").append(content);
				$("ul[class='nav navbar-nav']").append(resContent);
			}
		}else{
			alert("���Ͳ�������");
		}
		
	});
	
})