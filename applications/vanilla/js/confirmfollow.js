
jQuery(document).ready(function ($) {
    $(document).on('click', ".BoxConfirmFollow-options .followButton", function(e) {
        e.preventDefault();
        var $button = $(this);

        var api_url = $button.attr("data-url");
        var url = window.location.origin + api_url;

        $.ajax({
            url: gdn.url(url),
            success: (data) => {
                $('.BoxConfirmFollow .Close').trigger('click');
                $('#followButton'+$(this).attr('id')).addClass('isFollowing');
                $('#followButton'+$(this).attr('id')).removeClass('Popup');
                $('#followButton'+$(this).attr('id')).attr('href', api_url);
                $('#followButton'+$(this).attr('id')).attr('title', $(this).attr('after-title'));
            },
            error: (e) => {
                console.log(e);
            }
        });
    })
});
