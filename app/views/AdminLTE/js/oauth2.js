$(document).on('change', '.login-toggle', function(){
    var provider_id = $(this).data("providerid");
    var is_checked = $(this).prop('checked');

    if (is_checked) {
        DappurCSRF.csrfAjax( 
            '/dashboard/oauth2/enable/login', 
            {provider_id: provider_id}, 
            function(result){
                parsed = jQuery.parseJSON(result);
                if (parsed.status) {
                    swal(
                      'Success!',
                      parsed.message,
                      'success'
                    )
                }else{
                    swal(
                      'Oops!',
                      parsed.message,
                      'success'
                    )
                }

            }
        );
    }else{
        DappurCSRF.csrfAjax( 
            '/dashboard/oauth2/disable/login', 
            {provider_id: provider_id}, 
            function(result){
                parsed = jQuery.parseJSON(result);
                if (parsed.status) {
                    swal(
                      'Success!',
                      parsed.message,
                      'success'
                    )
                }else{
                    swal(
                      'Oops!',
                      parsed.message,
                      'success'
                    )
                }

            }
        );
    }

});

$(document).on('change', '.status-toggle', function(){
    var provider_id = $(this).data("providerid");
    var is_checked = $(this).prop('checked');
    var parent = $(this).closest('tr');

    if (is_checked) {
        DappurCSRF.csrfAjax( 
            '/dashboard/oauth2/enable', 
            {provider_id: provider_id}, 
            function(result){
                parsed = jQuery.parseJSON(result);
                if (parsed.status) {
                    swal(
                      'Success!',
                      parsed.message,
                      'success'
                    );
                    parent.removeClass('danger');
                    parent.addClass('success');

                }else{
                    swal(
                      'Oops!',
                      parsed.message,
                      'success'
                    );
                    parent.removeClass('success');
                    parent.addClass('danger');
                }

            }
        );
    }else{
        DappurCSRF.csrfAjax( 
            '/dashboard/oauth2/disable', 
            {provider_id: provider_id}, 
            function(result){
                parsed = jQuery.parseJSON(result);
                if (parsed.status) {
                    swal(
                      'Success!',
                      parsed.message,
                      'success'
                    );
                    parent.removeClass('success');
                    parent.addClass('danger');
                }else{
                    swal(
                      'Oops!',
                      parsed.message,
                      'success'
                    );
                    parent.removeClass('success');
                    parent.addClass('danger');
                }

            }
        );
    }

});