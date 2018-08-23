var generateSignature = function(callback, params_to_sign){
    $.ajax({
        url     : "/dashboard/media/cloudinary-sign",
        type    : "GET",
        dataType: "text",
        data    : { data: params_to_sign},
        success : function(signature, textStatus, xhr) { callback(signature); },
        error   : function(xhr, status, error) { console.log(xhr, status, error); }
    });
}

$('.upload-featured-cloudinary').cloudinary_upload_widget(
    { 
        cloud_name: cloudinaryCloudName, 
        thumbnails: false,
        upload_signature: generateSignature,
        api_key: cloudinaryApiKey, 
        button_class: 'btn btn-info form-control',
        button_caption: '<span class="fa fa-upload"></span> Cloudinary',
        cropping: 'server',
        cropping_aspect_ratio: 1.90476190476, 
        cropping_validate_dimensions: true, 
        cropping_show_dimensions: true,
        cropping_coordinates_mode: 'custom',
        folder: 'blog/',
        min_image_width: 1200,
        min_image_height: 630, 
        show_powered_by: false,
        sources: [
            'local',
            'url',
            'camera',
        ],
        multiple: false,
        theme: 'white'
    },
    function(error, result) { 
        if (result) {

            var deleteToken = $("#featured_thumbnail").attr('delete-token');
            if (deleteToken != "") {
                $.cloudinary.delete_by_token(deleteToken);
            }

            $("#featured_image").val(result[0].secure_url);
            $("#featured_thumbnail").html('<img src="'+result[0].secure_url+'" class="img-responsive" alt="Featured Image" style="width: 100%;">');
            $("#featured_thumbnail").attr('delete-token', result[0].delete_token);
        }
    }
);