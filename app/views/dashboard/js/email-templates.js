$(document).ready(function() {
    

    var activeEditor;

    CKEDITOR.config.extraPlugins = 'justify,colordialog';

    CKEDITOR.on('instanceReady', function(evt) {
        var editor = evt.editor;
        editor.on('focus', function(e) {
            activeEditor = "html";
        });
    });

    CKEDITOR.replace('html', {
        skin: 'kama' 
    });

    $(".cke_editable").focus(function() {
        
    });
    $("#plain_text").focus(function() {
        activeEditor = "plain_text";
    });
    $("#subject").focus(function() {
        activeEditor = "subject";
    });

    $(document).on('click', '.placeholder-insert', function(){
        if (activeEditor == "html") {
            CKEDITOR.instances.html.insertHtml($(this).attr('data-insert'));
        }
        if (activeEditor == "plain_text") {
            var $txt = jQuery("#plain_text");
            var caretPos = $txt[0].selectionStart;
            var textAreaTxt = $txt.val();
            $txt.val(textAreaTxt.substring(0, caretPos) + $(this).attr('data-insert') + textAreaTxt.substring(caretPos) );
        }
        if (activeEditor == "subject") {
            var $txt = jQuery("#subject");
            var caretPos = $txt[0].selectionStart;
            var textAreaTxt = $txt.val();
            $txt.val(textAreaTxt.substring(0, caretPos) + $(this).attr('data-insert') + textAreaTxt.substring(caretPos) );
        }
    });

    $(document).on('click', '#add-placeholder-custom', function(){
        swal({
            title: 'Add Custom Data Field',
            input: 'text',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            showLoaderOnConfirm: true,
            preConfirm: function (name) {
                return new Promise(function (resolve, reject) {

                    if (name == name.match("^[a-z0-9_]*$")) {

                        if (!$("#custom-placeholder-row").is(':visible')) {
                            $("#custom-placeholder-row").show();
                        }

                        $("#custom-placeholder-row").append('<div style="float: left; margin: 5px;"><span class="btn btn-default placeholder-insert placeholder-custom" data-insert="\{\{ '+name+' \}\}">\{\{ '+name+' \}\}</span><span class="btn btn-danger placeholder-delete"><i class="fa fa-close"></i></span><input type="hidden" name="placeholders[]" value="'+name+'"></div>');
                        resolve()
                    }else{
                        reject('Data field be alphanumeric with underscores.')
                    }
                })
            },
            allowOutsideClick: false
        }).then(function (name) {
            swal({
                type: 'success',
                title: 'Custom Data Field Added!',
                html: '[[ '+name+' ]]'
            });
            $("body").getNiceScroll().resize();

        })
    });

    $(document).on('click', '.placeholder-delete', function(){
        $(this).parent().remove();
        if ($("#custom-placeholder-row").children().length == 1) {
            $("#custom-placeholder-row").hide();
            $("body").getNiceScroll().resize();
        }
    });
    
    $(document).on('mousedown', '#plain_text, #cke_1_resizer', function(e){
        $(document).mousemove(function(){
            $("body").getNiceScroll().resize();
        });
    });

    $(document).on('click', '#send-test', function(e){

        var subject = $("#subject").val();
        var html = CKEDITOR.instances['html'].getData();
        var plain_text = $("#plain_text").val();

        swal({
            title: 'Would you like to send a test email to yourself?',
            type: 'question',
            showCancelButton: true,
            confirmButtonText: 'Send',
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return new Promise(function (resolve, reject) {
                    DappurCSRF.csrfAjax( 
                        '/dashboard/email/test', 
                        {subject: subject, html: html, plain_text: plain_text}, 
                        function(result){
                            parsed = jQuery.parseJSON(result);
                            if (parsed.status == "error") {
                                reject("An error occurred sending your test email.");
                            }else{
                                resolve();
                            }
                        }
                    );
                })
            },
            allowOutsideClick: false
        }).then(function () {
            swal({
                type: 'success',
                title: 'Test mail has been successfully sent.'
            })
        });

        
    });
});