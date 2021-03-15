jQuery(document).ready(function ($) {
    $(document).on('click', ".beta-banner .close-icon", function(e) {
        e.preventDefault();
        var $button = $(this);

        $('.beta-banner').css('display', 'none');

        var api_url = $button.attr("data-url");
        var url = window.location.origin + api_url;

        $.ajax({
            url: gdn.url(url),
            success: (data) => {
                $('.beta-banner').css('display', 'none');
            },
            error: (e) => {
                console.log(e);
            }
        });
    })
});