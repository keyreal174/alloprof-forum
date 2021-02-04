jQuery(document).ready(function($) {
    /* Autosave functionality for comment & discussion drafts */
    $('.ReplyQuestionButton').click(function() {
        $('.CommentPostForm').addClass('open');
    })

    $('.CommentPostForm .close-icon').click(function(){
        $(this).removeClass('show');
        $('.CommentPostForm .FormWrapper').removeClass('open');
        $('.CommentPostForm .placeholder').removeClass('close')
    })
});
