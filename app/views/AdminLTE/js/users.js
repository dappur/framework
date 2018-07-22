$(document).on('click', '.change-user-password', function(){
    var userId = $(this).data('userid');
    swal({
        title: 'Change Password',
        html:
            '<div>Please enter a new password.</div>' +
            '<div class="col-md-6"><input type="password" id="swal-input1" class="swal2-input" placeholder="New Password"></div>' +
            '<div class="col-md-6"><input type="password" id="swal-input2" class="swal2-input" placeholder="Confirm"></div>',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Change Password',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        preConfirm: function () {
            return new Promise(function (resolve, reject) {

                var password = $('#swal-input1').val();
                var confirm = $('#swal-input2').val();

                if (password != confirm) {
                    reject('The passwords you entered do not match.');
                }

                DappurCSRF.csrfAjax( 
                    '/dashboard/users/change-password', 
                    {password: password, confirm: confirm, user_id: userId}, 
                    function(result){
                        if (result.status == "error") {
                            reject(result.message);
                        }else if(result.status == "success"){
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
            title: 'User password has been successfully changed.'
        })
    }).catch(swal.noop);
});

$(document).on('click', '.disable2fa', function(){
    var userId = $(this).data('userid');
    var button = $(this);
    swal({
        type: 'warning',
        title: 'Disable 2FA',
        text: 'Are you sure that you want to disable two factor authentication for this user?', 
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Disable 2FA',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        preConfirm: function () {
            return new Promise(function (resolve, reject) {

                DappurCSRF.csrfAjax( 
                '/dashboard/users/2fa/disable', 
                {user_id: userId}, 
                function(result){
                    if (result.status == "error") {
                        reject(result.message)
                    }else if(result.status == "success"){
                        resolve();
                    }else{
                        reject("An unknown error occurred.");
                    }
                }
            );
               
            })
        }
    }).then( function () {
        button.remove();
        swal({
            type: 'success',
            title: 'Two factor authentication has been disabled for this user.'
        });
    }).catch(swal.noop);
});