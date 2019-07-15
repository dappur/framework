$(document).ready(function(){
	/* Nice Scroll
	-------------------------------------------------- */
	$("body").niceScroll();

	/* Initialize Labels
	-------------------------------------------------- */
	$('input[type=text],input[type=password]').each(function(){
		if ($(this).val() !== "") {
			$(".floating-label-form-group").toggleClass("floating-label-form-group-with-value", !!$(this).val());
		}
		
	})

});

/* Floating Labels
/* https://github.com/fauxparse/bootstrap-floating-labels
-------------------------------------------------- */
$(function() {
	$("body").on("input propertychange", ".floating-label-form-group", function(e) {
	    $(this).toggleClass("floating-label-form-group-with-value", !!$(e.target).val());
	}).on("focus", ".floating-label-form-group", function() {
	    $(this).addClass("floating-label-form-group-with-focus");
	}).on("blur", ".floating-label-form-group", function() {
	    $(this).removeClass("floating-label-form-group-with-focus");
	});
});

window.addEventListener("load", function(){
window.cookieconsent.initialise({
  "palette": {
    "popup": {
      "background": "#eaf7f7",
      "text": "#5c7291"
    },
    "button": {
      "background": "transparent",
      "text": "#56cbdb",
      "border": "#56cbdb"
    }
  }
})});