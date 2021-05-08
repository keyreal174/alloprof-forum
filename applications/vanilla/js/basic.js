$(window).on("scroll", function () {
    if ($(window).scrollTop() > 72) {
        $("#SubHeader").addClass("fixed");
    } else {
        $("#SubHeader").removeClass("fixed");
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


    // function initHeader() {
    //     $("header").addClass("not-transparent");
    // }

    // if($('.Banner-content').length === 0)
    //     initHeader();

    // Select2 initialization

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
            var parent = $(obj.element).parent().parent().parent();

            if(data && data['img_src']){
                parent.find('.category-selected-img').html('<img src="'+data['img_src']+'"/>');
            }

            if(data && data['img_src'] === '') {
                parent.find('.category-selected-img').html('');
            }
        }
    }

    $('.select2-grade select').select2({
        minimumResultsForSearch: -1,
        placeholder: "Niveau",
    });

    $('.select2-category select').select2({
        placeholder: "Matière",
        minimumResultsForSearch: -1,
        templateResult: formatState
    }).on('select2:select', function (e) {
        var data = e.params.data;
        selectCategoryImg(data);
    });

    selectCategoryImg({element: $('.FilterMenu .select2-category option:selected')});
    selectCategoryImg({element: $('.EditDiscussionDetail .select2-category option:selected')});

    $('.english-btn').click(function() {
        gdn.informMessage(
            `<div class="toast-container" style="max-width: 380px;">
                <div class="toast-text">
                    Feel free to submit or ask questions in English, we answer in both languages!
                </div>
            </div>`,
        'Dismissable');
    })

    //
    $(window).click(function() {
        $('.Overlay').last().remove();
    });

    $(document).on('click', '.Popup', function(event) {
        event.stopPropagation();
    });

    $(document).on('click', '.mobileFlyoutOverlay', function(event) {
        $(this).remove();
    });

    $(document).on('click', '.Flyout.MenuItems', function(event) {
        event.stopPropagation();
    });

    $('.ToggleFlyout.OptionsMenu').click(function() {
        const menu = $(this).find('.mobileFlyoutOverlay').html();
        $(this).removeClass('Open');

        $('.ToggleFlyout.Outside').remove();
        $("body").append(`<div class="ToggleFlyout Outside">
            <div class="mobileFlyoutOverlay">${menu}</div></div>`);
    })

    $(document).on('click', '.ToggleFlyout.Outside a.EditComment', function(event) {
        $('.ToggleFlyout.Open a.EditComment').trigger('click');
        event.preventDefault();
    })
});