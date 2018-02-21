$(document).ready(function() {

    $('#fvButton').on('click', function (e) {
        e.preventDefault();
    })

    $('#video_url').on('keypress', function (e) {
        if(e.which === 13){
            e.preventDefault();
            $('#video_url').blur();
        }
    });

    $('#video_url').on('focusout', function (e) {
        if ($(this).val() != "") {
            $("#video_url").attr("disabled", true);

            var featuredVideo = urlParser.parse($("#video_url").val());

            if (featuredVideo) {
                // Start the loading spinner
                $("#video_preview").html('<span class="help-block">Processing Video... <i class="fa fa-spinner"></i></span>');

                // Remove the error class from the form.
                $("#fv_group").removeClass('has-error');

                if (featuredVideo.provider === "youtube") {
                    $("#video").val('https://www.youtube.com/embed/'+featuredVideo.id);
                    $("#video_preview").html('<div class="video-container"><iframe src="https://www.youtube.com/v/'+featuredVideo.id+'" frameborder="0" allowfullscreen></iframe></div>');

                } else if (featuredVideo.provider === "vimeo"){
                    $("#video").val('https://player.vimeo.com/video/'+featuredVideo.id);
                    $("#video_preview").html('<div class="video-container"><iframe src="https://player.vimeo.com/video/'+featuredVideo.id+'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe></div>');

                }else{
                    $("#video_preview").html('<span class="help-block">That is not a supported video provider.</span>');
                    $("#fv_group").addClass('has-error')
                }

            }else{
                $("#video_preview").html('<span class="help-block">That is not a valid video url.</span>');
                $("#fv_group").addClass('has-error');
            }
            $("#video_url").val("");
            $("#video_url").attr("disabled", false);
        }
    });
    
});

$(document).on('click', '.upload-featured-local', function(){
    DappurMedia.loadMedia('seo_featured', null);
});