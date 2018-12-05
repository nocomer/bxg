jQuery(function(){
    function showErrorAlert (reason, detail) {
        var msg='';
        if (reason==='unsupported-file-type') { msg = "Unsupported format " +detail; }
        else {
            //console.log("error uploading file", reason, detail);
        }
        $('<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button>'+
            '<strong>File upload error</strong> '+msg+' </div>').prependTo('#alerts');
    }
    //$(window).on('click',function(){
    //    $('#discription-edit').val($("#editor1").html());
    //});
    //$('#editor1').ace_wysiwyg();//this will create the default editor will all buttons

    //but we want to change a few buttons colors for the third style
    $('#editor1').ace_wysiwyg({
        toolbar:plugins,
        'wysiwyg': {
            fileUploadError: showErrorAlert
        }
    }).prev().addClass('wysiwyg-style2');

     //make the editor have all the available height
     //$(window).on('resize.editor', function() {
	//	var offset = $('#editor1').parent().offset();
	//	var winHeight =  $(this).height();
    //
	//	$('#editor1').css({'height':winHeight - offset.top - 10, 'max-height': 'none'});
	//}).triggerHandler('resize.editor');

    $('[data-toggle="buttons"] .btn').on('click', function(e){
        var target = $(this).find('input[type=radio]');
        var which = parseInt(target.val());
        var toolbar = $('#editor1').prev().get(0);
        if(which >= 1 && which <= 4) {
            toolbar.className = toolbar.className.replace(/wysiwyg\-style(1|2)/g , '');
            if(which == 1) $(toolbar).addClass('wysiwyg-style1');
            else if(which == 2) $(toolbar).addClass('wysiwyg-style2');
            if(which == 4) {
                $(toolbar).find('.btn-group > .btn').addClass('btn-white btn-round');
            } else $(toolbar).find('.btn-group > .btn-white').removeClass('btn-white btn-round');
        }
    });

    $('#editor1').on('blur',function()
    {
        $('#discription-edit').val($("#editor1").html());
    });


    //RESIZE IMAGE

    //Add Image Resize Functionality to Chrome and Safari
    //webkit browsers don't have image resize functionality when content is editable
    //so let's add something using jQuery UI resizable
    //another option would be opening a dialog for user to enter dimensions.
    if ( typeof jQuery.ui !== 'undefined' && ace.vars['webkit'] ) {

        var lastResizableImg = null;
        function destroyResizable() {
            if(lastResizableImg == null) return;
            lastResizableImg.resizable( "destroy" );
            lastResizableImg.removeData('resizable');
            lastResizableImg = null;
        }

        var enableImageResize = function() {
            $('.wysiwyg-editor')
                .on('mousedown', function(e) {
                    var target = $(e.target);
                    if( e.target instanceof HTMLImageElement ) {
                        if( !target.data('resizable') ) {
                            target.resizable({
                                aspectRatio: e.target.width / e.target.height,
                            });
                            target.data('resizable', true);

                            if( lastResizableImg != null ) {
                                //disable previous resizable image
                                lastResizableImg.resizable( "destroy" );
                                lastResizableImg.removeData('resizable');
                            }
                            lastResizableImg = target;
                        }
                    }
                })
                .on('click', function(e) {
                    if( lastResizableImg != null && !(e.target instanceof HTMLImageElement) ) {
                        destroyResizable();
                    }
                })
                .on('keydown', function() {
                    destroyResizable();
                });
        }

        enableImageResize();


         //or we can load the jQuery UI dynamically only if needed
         if (typeof jQuery.ui !== 'undefined') enableImageResize();
         else {//load jQuery UI if not loaded
			$.getScript($path_assets+"/js/jquery-ui.custom.min.js", function(data, textStatus, jqxhr) {
				enableImageResize()
			});
		}
    }
});