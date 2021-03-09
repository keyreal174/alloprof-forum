jQuery(document).ready(function ($) {
    $(".Flyout.MenuItems .mark-verify").click(function (e) {
        e.preventDefault();
        var $button = $(this);

        var item = $button.closest('li');
        var url = window.location.origin + item.find("a").attr("id");

        $.ajax({
            url: gdn.url(url),
            data: { DeliveryType: 'VIEW' },
            success: (data) => {
                if (item.find("a").attr("id").indexOf("unverify") > -1) {
                    $(this).closest("li.ItemComment").find(".verfied-info").css("display", "none");
                } else {
                    $(this).closest("li.ItemComment").prepend(data);
                }
                location.reload();
            },
            error: (e) => {
                if (item.find("a").attr("id").indexOf("unverify") > -1) {
                    $(this).closest("li.ItemComment").find(".verfied-info").css("display", "none");
                    location.reload();
                } else {

                }
            }
        });
    })
});
