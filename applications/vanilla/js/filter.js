jQuery(document).ready(function($) {
    /* Catch Grade Filter Change Event */
    $(document).on('change', '#GradeFilter', function() {
        $.ajax({
            type: "POST",
            url: '/discussions/gradeFilter',
            data: postValues,
            dataType: 'json',
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert("Error");
            },
            success: function(json) {
                alert("Success");
            }
        });
        return false;
    });
});
