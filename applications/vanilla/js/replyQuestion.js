jQuery(document).ready(function($) {
    $('.ReplyQuestionButton').click(function() {
        $('.CommentPostForm').addClass('open');
    })

    $('.CommentPostForm .close-icon').click(function(){
        $('.CommentPostForm').removeClass('open');
    })
});
