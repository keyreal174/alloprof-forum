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
        filterDiscussion();
    });

    $(document).on('change', '.FilterMenu #Form_VerifiedBy', function() {
        filterDiscussion();
    });

    function filterDiscussion() {
        var subject = $('.FilterMenu #Form_SubjectDropdown').val();
        var grade = $('.FilterMenu #Form_GradeDropdown').val();
        var sort = $('.FilterMenu #Form_DiscussionSort').val();
        var explanation = $('.FilterMenu #Form_Explanation').is(":checked");
        var verifiedBy = $('.FilterMenu #Form_VerifiedBy').is(":checked");

        grade = !grade ? -1 : grade;
        subject = !subject ? -1 : subject;
        var parameter = 'grade=' + parseInt(grade) + '&sort=' + sort + '&explanation=' + explanation + '&verifiedBy=' + verifiedBy + '&subject=' + subject;
        var url = document.location.href;

        // get new url;
        var newUrl = '';
        if (url.includes('search?')) {
            var base_url = url.split("search?")[0];
            if (url.split("search?")[0].startsWith('grade')) {
                newUrl = base_url + 'search?' + parameter;
            } else {
                var searchParams = url.split("search?")[1].split('&grade')[0];
                newUrl = base_url + 'search?' + searchParams + '&' + parameter;
            }
        } else {
            newUrl = url + '?' + parameter;
        }

        document.location = newUrl;
        return false;
    }
});
