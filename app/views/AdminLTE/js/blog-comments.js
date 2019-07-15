(function(){
	$(document).on('click', '.reply-btn', function(e){
		e.preventDefault();
		if ($(".reply"+$(this).data('commentid')).is(":visible")) {
			$(".reply"+$(this).data('commentid')).hide();
		}else{
			$(".reply"+$(this).data('commentid')).show();
		}
		$("body").getNiceScroll().resize();
	});

	var hasError = 0;
	$(".has-error").each(function(){
	   hasError = 1;
	})

	if (hasError) {
		$('html, body').animate({
		    scrollTop: ($('.has-error').first().offset().top -175)
		},500);
	}
})();