jQuery(document).ready(function($) {
    /* Autosave functionality for comment & discussion drafts */
    $('.AskQuestionForm .clickToCreate').click(function() {
        $(this).hide();
        $('.AskQuestionForm .FormWrapper').show();
        $('.BoxNewDiscussion .user-info').show();
        $('.AskQuestionForm .close-icon').addClass('show');
        $(".AskQuestionForm .ql-editor").focus();
        $(".AskQuestionForm .ql-editor").focus();
        $('.information-block.newdiscussion').addClass('show');

    })

    $('.AskQuestionForm .close-icon').click(function(){
        $(this).removeClass('show');
        $('.AskQuestionForm .clickToCreate').show()
        $('.AskQuestionForm .FormWrapper').hide()
        $('.BoxNewDiscussion .user-info').hide();
        $('.information-block.newdiscussion').removeClass('show');
    })

    $(document).on('click', '.QuestionPopup .editor', function() {
        if($('.QuestionPopup .editor .richEditor-text').hasClass('focus-visible')) {
            $('.QuestionPopup .clickToCreate').hide();
        }
    })

    $(document).on('click', '.QuestionPopup .mobile-categories .category-item', function() {
        $('.QuestionPopup .mobile-categories .category-item').removeClass('selected');
        $(this).addClass('selected');
        $('.QuestionPopup #Form_CategoryID').val($(this).attr('id'));
    })

    // $('.scrollToAskQuestionForm').click(function(){
    //     $('.AskQuestionForm').css('display', 'block');
    //     $('.AskQuestionForm .clickToCreate').trigger('click');

    //     $(".AskQuestionForm .ql-editor").focus();
    //     if ($(".AskQuestionForm").offset()) {
    //         $([document.documentElement, document.body]).animate({
    //             scrollTop: $(".AskQuestionForm").offset().top - 220
    //         }, 500);
    //     }

    //     if ($("#MainContent").offset()) {
    //         $([document.documentElement, document.body]).animate({
    //             scrollTop: $("#MainContent").offset().top - 235
    //         }, 500);
    //     }
    // })
});
