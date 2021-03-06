jQuery(document).ready(function($) {
    $(document).on('change', '.FilterMenu #Form_SubjectDropdown', function() {
        filterDiscussion();
    });

    $(document).on('change', '.FilterMenu #Form_GradeDropdown', function() {
        filterDiscussion();
    });

    $(document).on('change', '.FilterMenu #Form_DiscussionSort', function() {
        filterDiscussion();
    });

    $(document).on('change', '.FilterMenu #Form_Explanation', function() {
        var explanation = $(this).is(":checked");
        if (explanation) {
            $(".FilterMenu #Form_OutExplanation").attr("checked", false);
        }
        filterDiscussion();
    });

    $(document).on('change', '.FilterMenu #Form_VerifiedBy', function() {
        var explanation = $(this).is(":checked");
        if (explanation) {
            $(".FilterMenu #Form_OutExplanation").attr("checked", false);
        }
        filterDiscussion();
    });

    $(document).on('change', '.FilterMenu #Form_OutExplanation', function() {
        var explanation = $(this).is(":checked");
        if (explanation) {
            $(".FilterMenu #Form_Explanation").attr("checked", false);
            $(".FilterMenu #Form_VerifiedBy").attr("checked", false);
        }
        filterDiscussion();
    });

    $(document).on('change', '.FilterMenu #Form_MobileExplanation', function() {
        var explanation = $(this).is(":checked");
        if (explanation) {
            $(".FilterMenu #Form_MobileOutExplanation").attr("checked", false);
        }
    });

    $(document).on('change', '.FilterMenu #Form_MobileVerifiedBy', function() {
        var explanation = $(this).is(":checked");
        if (explanation) {
            $(".FilterMenu #Form_MobileOutExplanation").attr("checked", false);
        }
    });

    $(document).on('change', '.FilterMenu #Form_MobileOutExplanation', function() {
        var explanation = $(this).is(":checked");
        if (explanation) {
            $(".FilterMenu #Form_MobileExplanation").attr("checked", false);
            $(".FilterMenu #Form_MobileVerifiedBy").attr("checked", false);
        }
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

    $(document).on('click', '.FilterPopup .apply-filter', function() {
        filterDiscussion(true);
    });

    $(document).on('click', '.FilterPopup .clean-filter', function() {
        $('.FilterPopup .mobile-grade .item').removeClass('selected');
        $(".FilterPopup input:radio").first().prop("checked", true);
        $(".FilterPopup input:checkbox").attr("checked", false);
    });

    $(document).on('click', '.mobile-grade .item', function() {
        $('.mobile-grade .item').removeClass('selected');
        $(this).addClass('selected');
    });

    $(document).on('change', '.FilterMenu #Form_Language', function() {
        filterDiscussion();
    });

    function filterDiscussion(isMobile=false) {
        var subject=-1,
            grade=-1,
            sort='desc',
            explanation=false,
            verifiedBy=false,
            outexplanation=false
            language=false;

        if(isMobile) {
            if($('.FilterMenu .mobile-grade .item.selected'))
                grade = $('.FilterMenu .mobile-grade .item.selected').attr('value');
            sort = $("input[type='radio'][name='sortRadio']:checked").val();
            explanation = $('.FilterMenu #Form_MobileExplanation').is(":checked");
            outexplanation = $('.FilterMenu #Form_MobileOutExplanation').is(":checked");
            verifiedBy = $('.FilterMenu #Form_MobileVerifiedBy').is(":checked");
            language = $('.FilterMenu #Form_Language').is(":checked");
        } else {
            subject = $('.FilterMenu #Form_SubjectDropdown').val();
            grade = $('.FilterMenu #Form_GradeDropdown').val();
            sort = $('.FilterMenu #Form_DiscussionSort').val();
            explanation = $('.FilterMenu #Form_Explanation').is(":checked");
            outexplanation = $('.FilterMenu #Form_OutExplanation').is(":checked");
            verifiedBy = $('.FilterMenu #Form_VerifiedBy').is(":checked");
            language = $('.FilterMenu #Form_Language').is(":checked");
        }

        grade = !grade ? -1 : grade;
        subject = !subject ? -1 : subject;
        var parameter = 'grade=' + parseInt(grade) + '&sort=' + sort + '&explanation=' + explanation + '&verifiedBy=' + verifiedBy + '&subject=' + subject + '&outexplanation=' + outexplanation + '&language=' + language;

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
