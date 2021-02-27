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
                console.log(data);
                $(this).closest(".Comment").prepend(data);
            },
            error: function (e) {
                console.log(e);
            }
        });
    })
});
