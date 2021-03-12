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

    $(document).on('change', '.FilterMenu #Form_CommentSort', function() {
        filterComment();
    });

    $(document).on('change', '.FilterMenu #Form_CommentVerifiedBy', function() {
        filterComment();
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
            url: gdn.url('/discussions/filterDiscussion'),
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

    function filterComment() {
        var sort = $('.FilterMenu #Form_CommentSort').val();
        var commentVerifiedBy = $('.FilterMenu #Form_CommentVerifiedBy').is(":checked");

        var parameter = 'commentverifiedby=' + commentVerifiedBy + '&sort=' + sort;

        var url = document.location.href;
        var newUrl = '';
        var anchor = url.split("#");
        if (url.includes('commentverifiedby')) {
            var base_url = url.split("commentverifiedby")[0];
            newUrl = base_url + parameter;
        } else {
            if (url.includes('?')) {
                newUrl = anchor[0] + '&' + parameter;
            } else {
                newUrl = anchor[0] + '?' + parameter;
            }
        }
        newUrl = anchor[1] ? newUrl + '#' + anchor[1] : newUrl;

        document.location = newUrl;
        return false;
    }
});
