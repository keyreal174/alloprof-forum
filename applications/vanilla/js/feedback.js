jQuery(document).ready(function($) {
    $(document).on("click", ".FeedbackPerfect", function() {
        var discussionID = document.location.href.split("/discussion/")[1].split("/")[0]
        $.ajax({
            type: "POST",
            url: gdn.url('/discussion/resolved/' + discussionID),
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            },
            success: function(json) {
                $('.BoxCheckAnswer.NotAnswered').addClass('Hidden');
                $('.BoxCheckAnswer.Answered').removeClass('Hidden');
            }
        });
    })
});
