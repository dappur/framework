$(document).ready(function() {
    $('#contact_table').DataTable({
        responsive: true,
        stateSave: true,
        "processing": true,
        "serverSide": true,
        "ajax":{
                 "url": "/dashboard/contact/datatables",
                 "dataType": "json",
                 "type": "GET"
               },
        "columns": [
            { "data": "created_at" },
            { "data": "name" },
            { "data": "email" },
            { "data": "phone" },
            { "data": "comment" }
        ]
    });
});