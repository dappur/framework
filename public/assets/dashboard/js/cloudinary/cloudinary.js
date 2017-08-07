var DappurCloudinary = new function() {
    
    this.cmsUrl = null;

    this.loadCloudinary = function (type, id) {
        $('#media-modal-body').html('');
        $('#media-modal').modal('show');
        var base = location.href.replace(/\/[^\/]+$/, '');
        socket = easyXDM.Socket({
            name: base + "/assets/dashboard/js/cloudinary/easyXDM.name.html",
            swf: base + "/assets/dashboard/js/cloudinary/easyxdm.swf",
            remote: this.cmsUrl,
            remoteHelper: "https://cloudinary.com/easyXDM.name.html",
            container: "media-modal-body",
            props: {style: {width: "100%", height: "490px"}},
            onMessage: function(message, origin){
                var json = JSON.parse(message);

                switch (type) {
                    case "menu":
                        switch (json.message) {
                            case "insert_into_post":
                                DappurCloudinary.copyToClipboard(json.src);
                                //DappurCloudinary.closeCloudinary();
                                swal({
                                    title: '<span style="color: green;">Image url copied to clipboard!</span>',
                                    html: '<img src="'+ json.src +'" style="width: 100%;">' + 
                                    '<input type="text" class="swal2-input" value="' + json.src + '" readonly />'
                                }).catch(swal.noop);
                                break;
                            case "done": 
                                DappurCloudinary.closeCloudinary();
                                break;
                        }
                        break;
                    case "input":
                        switch (json.message) {
                            case "insert_into_post":
                                $("#"+id).val(json.src);
                                $("#"+id+"-thumbnail").attr("src", json.src);
                                DappurCloudinary.closeCloudinary();
                                break;
                            case "done": 
                                DappurCloudinary.closeCloudinary();
                                break;
                        }
                        break;
                }
            },
            onReady: function() {

            }
        });
    };

    this.closeCloudinary = function() {
        $('#media-modal').modal('hide');
    };

    this.copyToClipboard = function(text) {
        var id = "mycustom-clipboard-textarea-hidden-id";
        var existsTextarea = document.getElementById(id);

        if(!existsTextarea){
            var textarea = document.createElement("textarea");
            textarea.id = id;
            // Place in top-left corner of screen regardless of scroll position.
            textarea.style.position = 'fixed';
            textarea.style.top = 0;
            textarea.style.left = 0;

            // Ensure it has a small width and height. Setting to 1px / 1em
            // doesn't work as this gives a negative w/h on some browsers.
            textarea.style.width = '1px';
            textarea.style.height = '1px';

            // We don't need padding, reducing the size if it does flash render.
            textarea.style.padding = 0;

            // Clean up any borders.
            textarea.style.border = 'none';
            textarea.style.outline = 'none';
            textarea.style.boxShadow = 'none';

            // Avoid flash of white box if rendered for any reason.
            textarea.style.background = 'transparent';
            document.querySelector("body").appendChild(textarea);
            //console.log("The textarea now exists :)");
            existsTextarea = document.getElementById(id);
        }

        existsTextarea.value = text;
        existsTextarea.select();

    };

    $("#cloudinary-menu").on('click', function(){
        DappurCloudinary.loadCloudinary('menu', null);
    });
}