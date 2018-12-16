$(function () {
    $('#import_file').fileupload({
        dataType: 'json',
        done: function (e, data) {
            if (data.result.status == "success") {
                window.location.replace(window.location.href);
            }else{
                swal({
                    type: 'error',
                    title: data.result.message
                });
            }
        }
    });

    $(document).ready(function() {
        
        $('#groups_table').DataTable({
            responsive: true,
            stateSave: true

        });

        if ($('#page').val() == 1) {
            $('.ispage').show();
        }else{
            $('.ispage').hide();
        }
    });

    $(document).on('change', '#page', function(){
        if ($(this).val() == 1) {
            $('.ispage').show();
        }else{
            $('.ispage').hide();
        }
    });

    $(document).on('change', '#add_type', function(){
        var type_id = $(this).val();
        if (type_id == 6) {
            $('#value_input').html('<select name="add_value" id="add_value" class="form-control" required="required">' +
                '<option value="0">False</option>' +
                '<option value="1">True</option>' +
            '</select>');
        }else if (type_id == 5) {
            $('#value_input').html('<div class="input-group dm-input" data-name="add_value" data-value=""></div>');
            DappurMedia.refreshInputs();
        }else if (type_id == 4) {
            $('#value_input').html('<input type="text" name="add_value" id="add_value" class="form-control" value="" placeholder="Value">');
        }else if (type_id == 3) {
            $('#value_input').html('<input type="text" name="add_value" id="add_value" class="form-control" value="" placeholder="Value">');
        }else if (type_id == 8) {
            $('#value_input').html('<input type="text" name="add_value" id="add_value" class="form-control jscolor" value="#FFFFFF" placeholder="Value">');
            var input = document.getElementById('add_value');
            window['pickeradd'] = new jscolor(input, {width:243, height:150, borderColor:'#FFF', insetColor:'#FFF', backgroundColor:'#666', hash:true});
        }else if (type_id == 2) {
            $('#value_input').html('<input type="text" name="add_value" id="add_value" class="form-control" value="" placeholder="Value">');
        }else if (type_id == 1) {
            $('#value_input').html('<input type="text" name="add_value" id="add_value" class="form-control" value="" placeholder="Value">');
        }else if( type_id == 7){
            $('#value_input').html('<div class="row">'+
                        '<div class="col-md-6">'+
                            '<button type="button" class="btn btn-info dappur-html-preview form-control" data-html="add_value">Preview</button>'+
                            '<input type="hidden" name="add_value" id="add_value" value="">'+
                        '</div>'+
                        '<div class="col-md-6">'+
                            '<button type="button" class="btn btn-warning dappur-html-edit form-control" data-html="add_value">Edit HTML</button>'+
                        '</div>'+
                    '</div>')
        }else{
            $('#value_input').html('<input type="text" name="add_value" id="add_value" class="form-control" value="" placeholder="Value">');
        }

    });

    $(document).on('click', '.dappur-html-preview', function(){
        var field = $(this).data('html');

        var html = $("#"+field).val();

        $(".html-modal-title").html(field);
        $(".dappur-save-html").hide();

        $(".tinymce").html(html);

        $("#html-modal").modal('show');
    });

    $(document).on('click', '.dappur-save-html', function(){
        var field = $(this).data('html');
        $("#"+field).val(tinyMCE.activeEditor.getContent()).trigger('change');
        resetHtmlModal();
        $("#html-modal").modal('hide');
    });

    $(document).on('click', '.dappur-html-edit', function(){
        var field = $(this).data('html');

        var html = $("#"+field).val();

        $(".html-modal-title").html(field);
        $(".dappur-save-html").data("html", field);
        $(".dappur-save-html").show();
        $(".tinymce").html(html);

        tinymce.init({
            selector: ".tinymce",
            theme: "modern",
            height: "300", 
            paste_data_images: true,
            plugins: [
              "advlist autolink lists link charmap print preview hr anchor pagebreak image",
              "searchreplace wordcount visualblocks visualchars code fullscreen",
              "insertdatetime nonbreaking save table contextmenu directionality",
              "emoticons template paste textcolor colorpicker textpattern"
            ],
            toolbar1: "undo redo | styleselect forecolor backcolor | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | code",
            toolbar2: "media_local cloudinary",
            bootstrapConfig: {
                'imagesPath': '/uploads/' // replace with your images folder path
            },
            branding: false,
            convert_urls: false,
            setup: function (editor) {
                editor.addButton('media_local', {
                    text: 'Local Media',
                    icon: false,
                    onclick: function () {
                        DappurMedia.loadMedia('blog', 'post_content');
                    }
                });
                if(hasCloudinary){
                    editor.addButton('cloudinary', {
                        text: 'Cloudinary',
                        icon: false,
                        onclick: function () {
                            DappurCloudinary.loadCloudinary('blog', 'post_content');
                        }
                    });
                }
                
            }
        });

        $("#html-modal").modal('show');

    });

    $('#html-modal').on('hidden.bs.modal', function () {
        resetHtmlModal();
        $(".tinymce").html('');
    });

    function resetHtmlModal(){
        $(".dappur-save-html").data("html", "");
        $(".html-modal-title").html("");
        $(".tinymce").html('');
        tinymce.remove()
    }

    var editDisplay = '<span class="config-save"><i class="fa fa-check"></i> Save</span> <span class="config-cancel"><i class="fa fa-close"></i> Cancel</span>';
    var cancelDisplay = '<span class="config-edit"><i class="fa fa-edit"></i> Edit</span>';
    var clearDisplay = '';

    $(document).on('click', '.config-edit', function(){
        var configButton = $(this).parents('.config-button');
        if ($(this).parents('label').siblings('input').length) {
            $(this).parents('label').siblings('input').attr('disabled', false);
            configButton.html(editDisplay);
        }

        if ($(this).parents('label').siblings('select').length) {
            $(this).parents('label').siblings('select').attr('disabled', false);
            configButton.html(editDisplay);
        } 

        if ($(this).parents('label').siblings('.dm-input').children('input').length) {
            $(this).parents('label').siblings('.dm-input').children('input').attr('readonly', false);
            configButton.html(editDisplay);
        }
        
    });

    $(document).on('click', '.config-cancel', function(){
        var configButton = $(this).parents('.config-button');
        var tempInput = $(this).parents('label').siblings('input');
        if (tempInput.length) {
            tempInput.attr('disabled', true);
            tempInput.val(configButton.data('default'));
            
            if (tempInput.hasClass('jscolor') || tempInput.hasClass('htmlinput')) {
                if (tempInput.hasClass('jscolor')) {
                    tempInput.css('background-color', configButton.data('default'));
                }
                if (tempInput.hasClass('htmlinput')) {
                    tempInput.siblings('.row').children('button').data('html', configButton.data('default'));
                }
                configButton.html(cancelDisplay);
            }else{
                configButton.html(cancelDisplay);
            }
        }

        var tempSelect = $(this).parents('label').siblings('select');
        if (tempSelect.length) {
            tempSelect.prop('disabled', true);
            tempSelect.val(configButton.data('default'));
            configButton.html(cancelDisplay);
        }

        var tempMedia = $(this).parents('label').siblings('.dm-input').children('input');
        if (tempMedia.length) {
            tempMedia.prop('readonly', true);
            tempMedia.val(configButton.data('default'));
            configButton.html(cancelDisplay);
        }
        
    });

    $(document).on('click', '.config-save', function(){

        var configButton = $(this).parents('.config-button');

        if ($(this).parents('label').siblings('input').length) {

            var tempInput = $(this).parents('label').siblings('input');

            var formData = {};
            formData[tempInput.attr('name')] = tempInput.val();
            tempInput.attr('disabled', true);
        }

        if ($(this).parents('label').siblings('select').length) {
            var tempInput = $(this).parents('label').siblings('select');
            var formData = {};
            formData[tempInput.attr('name')] = tempInput.val();
            tempInput.prop('disabled', true);
        }

        if ($(this).parents('label').siblings('.dm-input').children('input').length) {
            var tempInput = $(this).parents('label').siblings('.dm-input').children('input');
            var formData = {};
            formData[tempInput.attr('name')] = tempInput.val();
            tempInput.prop('disabled', true);
        }

        configButton.html(' <i class="fa fa-refresh fa-spin"></i> Saving');

        DappurCSRF.csrfAjax( 
            '/dashboard/settings/save', 
            formData, 
            function(result){
                parsed = jQuery.parseJSON(result);
                if (parsed.status == "success") {
                    configButton.html('<span class="config-saved"><i class="fa fa-check"></i> Saved</span>');

                    configButton.children(".config-saved").fadeOut(2000, function() { 
                        $(this).remove();
                        if (tempInput.hasClass('jscolor') || tempInput.hasClass('htmlinput')) {
                            configButton.html(cancelDisplay);
                        }else{
                            configButton.html(cancelDisplay);
                        }
                    });
                    configButton.data('default', tempInput.val());
                }else {
                    swal({
                        type: 'error',
                        title: parsed.message
                    });
                    if (tempInput.hasClass('jscolor') || tempInput.hasClass('htmlinput')) {
                        configButton.html(cancelDisplay);
                    }else{
                        configButton.html(cancelDisplay);
                    }
                    tempInput.val(configButton.data('default'));
                }
            }
        );
    });

    $(document).on('change', '.dm-image', function(){

        var configButton = $(this).parent().siblings('label').children('.config-button');
        var tempInput = $(this);
        var formData = {};
        formData[tempInput.attr('name')] = tempInput.val();
        tempInput.attr('disabled', true);
        configButton.html(editDisplay);
    });

    $(document).on('change', '.jscolor-config', function(){
        var configButton = $(this).siblings('label').children('.config-button');
        var tempInput = $(this);
        var formData = {};
        formData[tempInput.attr('name')] = tempInput.val();
        configButton.html(editDisplay);
    });

    $(document).on('change', '.htmlinput', function(){
        var configButton = $(this).siblings('label').children('.config-button');
        var tempInput = $(this);
        var formData = {};
        formData[tempInput.attr('name')] = tempInput.val();
        tempInput.attr('disabled', true);
        configButton.html(editDisplay);
    });

    $(document).on( "click", '[data-toggle="collapse"]', function () {
        $(this).children('i').toggleClass('fa-minus fa-plus');
    } );
});