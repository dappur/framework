var DappurCSRF = new function() {
    
    this.csrfUrl = "/csrf";

    this.csrfAjax = function (postUrl, data, callback) {
        csrfUrl = this.csrfUrl;
    	new Promise(function(resolve, reject) {
            var xhr = new XMLHttpRequest();
            xhr.onload = function() {
                resolve(jQuery.parseJSON(this.responseText));
            };
            xhr.onerror = reject;
            xhr.open('GET', csrfUrl);
            xhr.send();
        }).then(function(csrf) {

            var postData = {};
            postData[csrf.name_key] = csrf.name;
            postData[csrf.value_key] = csrf.value;

            $.each( data, function( key, value ) {
                postData[key] = value;
            });

            $.ajax({
                url : postUrl,
                type: "POST",
                data : postData,
                success: callback,
                error: function (jqXHR, textStatus, errorThrown)
                {
                    //alert(errorThrown);
                }
            });

        });
    };
};
