$(document).ready(function(){

	// Sweet Alert Confirm Dialog
    $(document).on('click', '.swal-confirm', function(e) { 
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
});