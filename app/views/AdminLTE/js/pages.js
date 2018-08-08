$(document).ready(function() {
    $('#roles').select2({
        width: '100%'
    });
});

var cssEditor = ace.edit("css", {
    mode: "ace/mode/css",
    selectionStyle: "text"
});
var cssTextarea = $('textarea[name="css"]').hide();
cssEditor.getSession().setValue(cssTextarea.val());
cssEditor.getSession().on('change', function(){
  	cssTextarea.val(cssEditor.getSession().getValue());
});

var jsEditor = ace.edit("js", {
    mode: "ace/mode/javascript",
    selectionStyle: "text"
});
var jsTextarea = $('textarea[name="js"]').hide();
jsEditor.getSession().setValue(jsTextarea.val());
jsEditor.getSession().on('change', function(){
  	jsTextarea.val(jsEditor.getSession().getValue());
});