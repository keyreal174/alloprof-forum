jQuery(document).ready(function($) {
    /* Autosave functionality for comment & discussion drafts */
    $('.AskQuestionForm .placeholder').click(function() {
        $(this).addClass('close');
        $('.AskQuestionForm .FormWrapper').addClass('open');
        $('.AskQuestionForm .close-icon').addClass('show')
    })

    $('.AskQuestionForm .close-icon').click(function(){
        $(this).removeClass('show');
        $('.AskQuestionForm .FormWrapper').removeClass('open');
        $('.AskQuestionForm .placeholder').removeClass('close')
    })

    $('.scrollToAskQuestionForm').click(function(){
        $('.AskQuestionForm').css('display', 'block');
        $('.AskQuestionForm .placeholder').trigger('click');
        console.log($(".AskQuestionForm .ql-editor"))
        $(".AskQuestionForm .ql-editor").focus();
        $([document.documentElement, document.body]).animate({
            scrollTop: $(".AskQuestionForm").offset().top - 220
        }, 500);
    })
});
