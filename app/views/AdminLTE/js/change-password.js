$(document).on('click', '.change_password', function(){
    swal({
        title: 'Change Password',
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
        preConfirm: function (password) {
            return new Promise(function (resolve, reject) {
                DappurCSRF.csrfAjax( 
                    '/profile/password-check', 
                    {password: password}, 
                    function(result){
                        parsed = jQuery.parseJSON(result);
                        if (parsed.result == "error") {
                            var msg = "Your current password doesn't match. If you do not know your current password, logout and click 'Forgot Password' on the login page.";
                            reject(msg);
                        }else if(parsed.result == "success"){
                            resolve();
                        }else{
                            reject('An unknown error occured.');
                        }

                    }
                );
            })
        },
        allowOutsideClick: false
    }).then( function (password) {
        swal({
            title: 'Change Password',
            html:
                '<div>Please enter your new password.</div>' +
                '<div class="col-md-6"><input type="password" id="swal-input1" class="swal2-input" placeholder="New Password"></div>' +
                '<div class="col-md-6"><input type="password" id="swal-input2" class="swal2-input" placeholder="Confirm"></div>',
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Change Password',
            cancelButtonText: 'Cancel',
            preConfirm: function () {
                return new Promise(function (resolve, reject) {

                    var password = $('#swal-input1').val();
                    var confirm = $('#swal-input2').val();

                    if (password != confirm) {
                        reject('The passwords you entered do not match.');
                    }

                    DappurCSRF.csrfAjax( 
                        '/profile/change-password', 
                        {password: password, confirm: confirm}, 
                        function(result){
                            parsed = jQuery.parseJSON(result);
                            if (parsed.result == "error") {
                                reject(parsed.message);
                            }else if(parsed.result == "success"){
                                resolve();
                            }else{
                                reject('An unknown error occured.');
                            }

                        }
                    );
                })
            }
        }).then( function (password) {
            swal({
                type: 'success',
                title: 'Your password has been successfully changed.'
            })
        }).catch(swal.noop);
    }).catch(swal.noop);
});