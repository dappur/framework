$(document).ready(function() {
    $('#contentRow').gridEditor({
        new_row_layouts: [[12], [6,6], [4,4,4], [3,3,3,3], [2,2,2,2,2]],
        tinymce: {
            config: {
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
                            icon: 'false',
                            onclick: function () {
                                DappurCloudinary.loadCloudinary('blog', 'post_content');
                            }
                        });
                    }
                    
                }
            }
        }
    });
});
$(document).on('submit', 'form', function(e) {
    var html = $('#contentRow').gridEditor('getHtml');
    $('#content').val(html);
});