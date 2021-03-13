$(window).on("scroll", function () {
    if ($(this).scrollTop() > 400) {
        $("header").addClass("not-transparent");
    }
    else {
        $("header").removeClass("not-transparent");
    }
});

jQuery(document).ready(function($) {
    var DataListSelector = '#Content ul.DataList.Comments, ' +
        'main.page-content ul.DataList.Comments, ' +
        'main.Content ul.DataList.Comments, ' +
        '#Content ul.DataList.Discussions, ' +
        'main.page-content ul.DataList.Discussions, ' +
        'main.Content ul.DataList.Discussions, ' +
        '#Content table.DataTable.DiscussionsTable tbody, ' +
        'main.page-content table.DataTable.DiscussionsTable tbody ' +
        'main.Content table.DataTable.DiscussionsTable tbody',
    ContentSelector = '#Content, main.page-content, main.Content';

    $(ContentSelector).prepend("<div id='PagerBefore'></div>");
    $(ContentSelector).append("<div id='PagerAfter'></div>");
});