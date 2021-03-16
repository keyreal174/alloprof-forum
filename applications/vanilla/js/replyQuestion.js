jQuery(document).ready(function($) {
    $('.ReplyQuestionButton').click(function() {
        $('.CommentPostForm').addClass('open');
        $('.information-block.newcomment').addClass('show');
    })

    $('.CommentPostForm .close-icon').click(function(){
        $('.CommentPostForm').removeClass('open');
        $('.information-block.newcomment').removeClass('show');
    })
});
