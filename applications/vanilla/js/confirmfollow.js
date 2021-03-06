
jQuery(document).ready(function ($) {
    $(".BoxConfirmFollow-options .followButton").click(function (e) {
        e.preventDefault();
        var $button = $(this);

        var api_url = $button.attr("data-url");
        var url = window.location.origin + api_url;

        $.ajax({
            url: gdn.url(url),
            success: (data) => {
                location.reload();
            },
            error: (e) => {
                console.log(e);
            }
        });
    })
});
