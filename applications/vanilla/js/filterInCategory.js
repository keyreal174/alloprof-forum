jQuery(document).ready(function($) {
    /* Catch Grade Filter Change Event */
    // $(document).on('change', '#GradeFilter', function() {
        // $.ajax({
        //     type: "POST",
        //     url: '/discussions/gradeFilter',
        //     data: postValues,
        //     dataType: 'json',
        //     error: function(XMLHttpRequest, textStatus, errorThrown) {
        //         alert("Error");
        //     },
        //     success: function(json) {
        //         alert("Success");
        //     }
        // });
        // return false;
    // });

    $(document).on('change', '.FilterMenu #Form_GradeDropdown', function() {
        filterDiscussion();
    });

    $(document).on('change', '.FilterMenu #Form_DiscussionSort', function() {
        filterDiscussion();
    });

    $(document).on('change', '.FilterMenu #Form_Explanation', function() {
        filterDiscussion();
    });

    $(document).on('change', '.FilterMenu #Form_VerifiedBy', function() {
        filterDiscussion();
    });

    function filterDiscussion() {
        var grade = $('.FilterMenu #Form_GradeDropdown').val();
        var sort = $('.FilterMenu #Form_DiscussionSort').val();
        var explanation = $('.FilterMenu #Form_Explanation').is(":checked");
        var verifiedBy = $('.FilterMenu #Form_VerifiedBy').is(":checked");

        grade = !grade ? -1 : grade;
        var parameter = 'grade=' + parseInt(grade) + '&sort=' + sort + '&explanation=' + explanation + '&verifiedBy=' + verifiedBy;

        $.ajax({
            type: "POST",
            url: '/categories/filterDiscussion',
            data: {
                parameter
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            },
            success: function(json) {
                document.location = json;
            }
        });
        return false;
    }

    function clean(obj) {
        for (var propName in obj) {
            if (obj[propName] === null || obj[propName] === undefined) {
                delete obj[propName];
            }
        }
        return obj
    }
});
