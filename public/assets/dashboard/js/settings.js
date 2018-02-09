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
    }else if (type_id == 2) {
        $('#value_input').html('<input type="text" name="add_value" id="add_value" class="form-control" value="" placeholder="Value">');
    }else if (type_id == 1) {
        $('#value_input').html('<input type="text" name="add_value" id="add_value" class="form-control" value="" placeholder="Value">');
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
    $("#"+field).val(tinyMCE.activeEditor.getContent());
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
})

function resetHtmlModal(){
    $(".dappur-save-html").data("html", "");
    $(".html-modal-title").html("");
    $(".tinymce").html('');
    tinymce.remove()
}