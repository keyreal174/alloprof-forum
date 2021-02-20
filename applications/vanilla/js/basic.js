$(window).on("scroll", function () {
    if ($(this).scrollTop() > 450) {
        $("header").addClass("not-transparent");
    }
    else {
        $("header").removeClass("not-transparent");
    }
});