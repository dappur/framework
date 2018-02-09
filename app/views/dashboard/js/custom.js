$(document).ready(function() {
	// Sweet Alert Confirm Dialog
    $('.swal-confirm').click(function(e) { 
    	
    	e.preventDefault();
    	var swtitle = $(this).data('swtitle');
    	var swmessage = $(this).data('swmessage');
    	var form = $(this).parents(form);

	    swal({
	        title: swtitle,
	        text: swmessage,
	        type: "warning",
	        showCancelButton: true,
	        confirmButtonColor: '#DD6B55',
	        confirmButtonText: 'Yes, I am sure!',
	        cancelButtonText: "No, cancel!"
	    }).then(
		   	function(result) {
		   		form.submit();
		  	},
		  	function(dismiss){
		  		return false;
		  	}
	    ).catch(swal.noop);
	    
    });

    $("body").niceScroll();
    $("#sidebar-wrapper").niceScroll();
    $("#media-modal-info").niceScroll();

    // Navbar Toggle
    $("#navbar-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });

    // Set page margin to header height
    var headerHeight = $(".navbar.navbar-inverse.navbar-fixed-top").height();
    $("#wrapper").css("margin-top", headerHeight);
    
});

$(document).on("resize", "body", function(){
	$("body").getNiceScroll().resize();
	$("#sidebar-wrapper").getNiceScroll().resize();
});

$('body').on('transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd', function() {
    $("body").getNiceScroll().resize();
    $("#sidebar-wrapper").getNiceScroll().resize();
});