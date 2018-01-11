$(document).ready(function() {
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
	                icon: 'false',
	                onclick: function () {
	                    DappurCloudinary.loadCloudinary('blog', 'post_content');
	                }
	            });
        	}
            
        }
    });
});