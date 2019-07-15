$(document).on('change', '#2fa', function(){
    var checked = $(this).prop('checked');
    if (checked) {
        var title = "Enable Two Factor Auth";
        var status2fa = true;
    }else{
        var title = "Disable Two Factor Auth";
        var status2fa = false;
    }
    swal({
        title: title,
        text: "Please enter your current password to continue. If you do not know your current password, logout and click 'Forgot Password' on the login page.",
        input: 'password',
        inputAttributes: {
            'maxlength': 10,
            'autocapitalize': 'off',
            'autocorrect': 'off'
        },
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        confirmButtonClass: 'btn btn-success',
        cancelButtonClass: 'btn btn-danger',
        buttonsStyling: true,
        reverseButtons: true,
        preConfirm: function (password) {
            return new Promise(function (resolve, reject) {
                DappurCSRF.csrfAjax( 
                    '/profile/2fa', 
                    {password: password, status2fa: status2fa}, 
                    function(result){
                        parsed = jQuery.parseJSON(result);
                        if (parsed.result == "error") {
                            var msg = "Your current password doesn't match. If you do not know your current password, logout and click 'Forgot Password' on the login page.";
                            reject(msg);
                        }else if (parsed.result == "success"){
                            swal({
                                title: title,
                                html: 'Please scan the following QR code or enter the secret into your authenticator app and enter the code you receive to enable two factor authentication.'+
                                    '<br /><img src="'+ parsed.qr +'" style="max-width: 100%" /><br /><b>Secret: </b>'+parsed.secret+'<br />'+
                                    '<div class="row"><div class="col-md-6 col-md-offset-3"><input type="number" id="swal-input1" style="text-align: center;" class="swal2-input form-control" placeholder="Code"></div></div>',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Continue',
                                cancelButtonText: 'Cancel',
                                confirmButtonClass: 'btn btn-success',
                                cancelButtonClass: 'btn btn-danger',
                                buttonsStyling: true,
                                reverseButtons: true,
                                preConfirm: function () {
                                    return new Promise(function (resolve, reject) {
                                        var code = $('#swal-input1').val();
                                        DappurCSRF.csrfAjax( 
                                            '/profile/2fa/validate', 
                                            {code: code}, 
                                            function(result){
                                                parsed = jQuery.parseJSON(result);
                                                if (parsed.result == "error") {
                                                    reject("Code incorrect, please try again.");
                                                }else if(parsed.result == "success"){
                                                    resolve();
                                                }else{
                                                    reject('An unknown error occured.');
                                                }

                                            }
                                        );
                                    })
                                }
                            }).then( function () {
                                swal({
                                    type: 'success',
                                    title: 'Two factor authentication has been successfully set up on your account.'
                                })
                            }, function(dismiss){
                                var checkedText = "checked";
                                if (checked) {
                                    var checkedText = "";
                                }
                                $('#2fa-toggle').html('<input type="checkbox" '+checkedText+' data-toggle="toggle" data-on="Enabled" data-off="Disabled" data-onstyle="success" data-offstyle="danger" height="" id="2fa">');
                                $('#2fa').bootstrapToggle();
                            }).catch(swal.noop);
                        }else if (parsed.result == "disabled"){
                            resolve();
                        }
                    }
                );
            })
        },
        allowOutsideClick: false
    }).then(function(){}, function(dismiss){
        var checkedText = "checked";
        if (checked) {
            var checkedText = "";
        }
        $('#2fa-toggle').html('<input type="checkbox" '+checkedText+' data-toggle="toggle" data-on="Enabled" data-off="Disabled" data-onstyle="success" data-offstyle="danger" height="" id="2fa">');
        $('#2fa').bootstrapToggle();
    }).catch(swal.noop);
});