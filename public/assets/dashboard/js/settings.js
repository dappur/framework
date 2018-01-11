$(document).ready(function() {
    
    $('#groups_table').DataTable({
        responsive: true,
        stateSave: true

    });

    if ($('#page').val() == 1) {
        $('.ispage').show();
    }else{
        $('.ispage').hide();
    }
});

$(document).on('change', '#page', function(){
    if ($(this).val() == 1) {
        $('.ispage').show();
    }else{
        $('.ispage').hide();
    }
});

$(document).on('change', '#add_type', function(){
    var type_id = $(this).val();
    if (type_id == 6) {
        $('#value_input').html('<select name="add_value" id="add_value" class="form-control" required="required">' +
            '<option value="0">False</option>' +
            '<option value="1">True</option>' +
        '</select>');
    }else if (type_id == 5) {
        $('#value_input').html('<div class="input-group dm-input" data-name="add_value" data-value=""></div>');
        DappurMedia.refreshInputs();

    }else if (type_id == 4) {
        $('#value_input').html('<input type="text" name="add_value" id="add_value" class="form-control" value="" placeholder="Value">');
    }else if (type_id == 3) {
        $('#value_input').html('<input type="text" name="add_value" id="add_value" class="form-control" value="" placeholder="Value">');
    }else if (type_id == 2) {
        $('#value_input').html('<input type="text" name="add_value" id="add_value" class="form-control" value="" placeholder="Value">');
    }else if (type_id == 1) {
        $('#value_input').html('<input type="text" name="add_value" id="add_value" class="form-control" value="" placeholder="Value">');
    }else{
        $('#value_input').html('<input type="text" name="add_value" id="add_value" class="form-control" value="" placeholder="Value">');
    }

});