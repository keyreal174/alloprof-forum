$(window).on("scroll", function () {
    if($('.Banner-content').length > 0) {
        if ($(this).scrollTop() > 70) {
            $("header").addClass("not-transparent");
        }
        else {
            $("header").removeClass("not-transparent");
        }
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

    function formatState (state) {
        var data = $(state.element).data();
        if (!state.id) { return state.text; }
        var icon = '<div class="category-img"></div>';

        if(data && data['img_src']){
            icon = '<div class="category-img"><img src="'+data['img_src'] + '"/></div>';
        }
        var $state = $(
          '<span class="image-option">'+ icon + state.text + '</span>'
       );
       return $state;
    };

    function selectCategoryImg (obj) {
        if(obj) {
            var data = $(obj.element).data();

            console.log($(obj.element).parent())

            if(data && data['img_src']){
                $('.category-selected-img').html('<img src="'+data['img_src']+'"/>');
            } else {
                $('.category-selected-img').html('');
            }
        }
    }

    $('.select2-grade select').select2({
        minimumResultsForSearch: -1,
        placeholder: "Select grade",
    });

    $('.select2-category select').select2({
        placeholder: "Select category",
        minimumResultsForSearch: -1,
        templateResult: formatState
    }).on('select2:select', function (e) {
        var data = e.params.data;
        selectCategoryImg(data);
    });

    selectCategoryImg($('.select2-category select').select2('data'));
});