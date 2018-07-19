var video_id = false;

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
                    video_id = featuredVideo.id;
                    $("#video").val('https://www.youtube.com/embed/'+featuredVideo.id);
                    $("#video_preview").html('<div class="video-container"><iframe src="https://www.youtube.com/embed/'+featuredVideo.id+'" frameborder="0" allowfullscreen></iframe></div>');
                    $(".seo-del-video").show();
                    $(".youtube-image").show();
                } else if (featuredVideo.provider === "vimeo"){
                    $("#video").val('https://player.vimeo.com/video/'+featuredVideo.id);
                    $("#video_preview").html('<div class="video-container"><iframe src="https://player.vimeo.com/video/'+featuredVideo.id+'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe></div>');
                    $(".seo-del-video").show();
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

    if($("#video").val() !== ""){
        $(".seo-del-video").show();
        $(".youtube-image").show();
    }else{$(".youtube-image").show();
        $(".seo-del-video").hide();
        $(".youtube-image").hide();
    }
    
});

$(document).on('click', '.upload-featured-local', function(){
    DappurMedia.loadMedia('seo_featured', null);
});

$(document).on('click', '.youtube-image', function(){
    if (video_id) {
        $("#featured_image").val('https://i1.ytimg.com/vi/'+ video_id +'/maxresdefault.jpg');
        $("#featured_thumbnail").html('<img src="https://i1.ytimg.com/vi/'+ video_id +'/maxresdefault.jpg" class="img-responsive" alt="Featured Image" style="width: 100%;">');
        $("#featured_thumbnail").attr('delete-token', "");
    }
});

$(document).on('click', '.seo-del-video', function(){
    $("#video").val("");
    $("#video_preview").html("");
    $("#video_url").html("");
    $(".seo-del-video").hide();
    $(".youtube-image").hide();
});