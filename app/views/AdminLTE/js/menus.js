var editor = new MenuEditor('myEditor', {listOptions: sortableListOptions, iconPicker: iconPickerOptions, labelEdit: 'Edit'});
editor.setActiveMenu(activeMenu);
editor.setForm($('#frmEdit'));
editor.setUpdateButton($('#btnUpdate'));
editor.setCancelButton($('#btnCancel'));
editor.setData(strjson);

$('#btnReload').on('click', function () {
    editor.setData(strjson);
    $("#btnSave").hide();
});
$('#btnSave').on('click', function () {
    var str = editor.getString();
    DappurCSRF.csrfAjax( 
        '/dashboard/menus/update', 
        {menu_id: activeMenu, json: str}, 
        function(data){
            if (data.result == "error") {
                swal("Error", "An error occurred retrieving that menu:<br />"+data.message, "error");
            }else if(data.result == "success"){
                swal("Success", "Your menu has been saved successfully.", "success");
                $("#out").text(editor.getString());
                editor.clearForm();
                $("#btnSave").hide();
            }else{
                swal("Error", "An unknown error occurred saving your menu.", "error");
            }

        }
    );
    $("#out").text(str);
});
$("#btnUpdate").click(function(){
    editor.update();
});

$("#btnCancel").click(function(){
    editor.clearForm();
});

$('#btnAdd').click(function(){
    editor.add();
});

$('#active, #roles, #page').select2({
    width: '100%'
});

$('#classes').select2({
    width: '100%',
    tags: true,
    createTag: function (params) {
        return {
            id: params.term,
            text: params.term,
            newOption: true
        }
    }
});

$('#btnNew').click(function(){
    swal({
        title: 'Enter a Menu Name',
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Add menu',
        showLoaderOnConfirm: true,
        reverseButtons: true,
        confirmButtonColor: 'green',
        cancelButtonColor: 'red',
        preConfirm: (name) => {
            return new Promise(function (resolve, reject) {
                DappurCSRF.csrfAjax( 
                    '/dashboard/menus/add', 
                    {name: name}, 
                    function(data){
                        if (data.result == "error") {
                            reject(data.message);
                        }else if(data.result == "success"){
                            console.log(data);
                            $("#frmBody")
                                .append('<tr><td data-menu="'+data.menu.id+'" class="menu-selector" style="cursor: pointer;">'+
                                    data.menu.name+
                                    '<div class="pull-right">'+
                                        '<a href="/dashboard/menus/export?menu_id='+data.menu.id+'" data-menu="'+data.menu.id+'" class="btn btn-xs btn-info export-menu">'+
                                            '<span class="fa fa-download"></span>'+
                                        '</a> '+
                                        '<button type="button" data-menu="'+data.menu.id+'" class="btn btn-xs btn-danger delete-menu">'+
                                            '<span class="fa fa-close"></span>'+
                                        '</button>'+
                                    '</div>'+
                                '</td></tr>'
                                );
                                $(".menu-selector[data-menu='"+data.menu.id+"']").trigger('click');
                            resolve();
                        }else{
                            reject('An unknown error occured.');
                        }

                    }
                );
            });
        },
        allowOutsideClick: () => !swal.isLoading()
    }).then((name) => {
        swal("", name+" has been successfully created.", "success");
    });
});

$('#auth, #guest').change(function() {
  $(this).val($(this).prop('checked'));
});

$(document).on('click', '.menu-selector', function(){
    var elem = $(this);
    $.get('/dashboard/menus/get', { menu_id: elem.data("menu") }, function(data) {
        if (data.result == "success") {
            if (!data.menu.json) {
                editor.setData([]);
                strjson = [];
            }else{
                editor.setData(data.menu.json);
                strjson = data.menu.json;
            }
            editor.clearForm();
            $("#out").text(editor.getString());
            $('.menu-selector').removeClass('success');
            elem.addClass('success');
            $("#btnSave").hide();
            activeMenu = data.menu.id;
            $("#output-title").html(data.menu.name);
        }else{
            swal("Error", "An error occurred retrieving that menu:<br />"+data.message, "error");
        }
    });
});

$(document).on('click', '.delete-menu', function(e){
    e.stopImmediatePropagation();
    var elem = $(this);
    swal({
        title: "Are you sure?",
        text: "This menu will be permanently deleted and will not be recoverable.",
        type: "warning",
        showCancelButton: true,
        cancelButtonText: 'No, cancel!',
        confirmButtonText: 'Yes, I am sure!',
        reverseButtons: true,
        cancelButtonColor: 'red',
        confirmButtonColor: 'green'
    }).then(function(isConfirm) {
        DappurCSRF.csrfAjax( 
            '/dashboard/menus/delete', 
            {menu_id: elem.data('menu')}, 
            function(data){
                if (data.result == "error") {
                    swal("", data.message, "error");
                }else if(data.result == "success"){
                    swal("", "Menu has been successfully deleted.", "success");
                    if (elem.data('menu') == activeMenu) {
                        $(".menu-selector").first().trigger('click');
                    }
                    elem.closest('tr').remove();
                }else{
                    swal("", "An unknown error occurred.", "error");
                }

            }
        );
    });
});
$("#frmEdit").on('change', 'select', function(){
    processCancel();
});
$("#frmEdit").on('keyup', 'input', function(){
    processCancel();
});
$("#frmEdit").on('change', '.bstoggle', function(){
    processCancel();
});
$('#myEditor').on("DOMSubtreeModified",function(){
    $("#btnSave").show();
});

$("#out").text(editor.getString());

function processCancel(){
    var hasVal = false;
    $.each($('#frmEdit input, select'), function(){
        if ($(this).val() != "" &&
            $(this).val() != "false" &&
            $(this).val() != "_self" &&
            $(this).val() != "fa-" &&
            $(this).val() != "empty") {
            hasVal = true;
        }
    });

    if (hasVal) {
        $("#btnCancel").attr("disabled", false);
        $("#btnCancel").show();
    }else{
        $("#btnCancel").attr("disabled", true);
        $("#btnCancel").hide();
    }
}

$('#import_file').fileupload({
    dataType: 'json',
    done: function (e, data) {
        if (data.result.status == "success") {
            window.location.replace(window.location.href);
        }else{
            swal({
                type: 'error',
                title: data.result.message
            });
        }
    }
});