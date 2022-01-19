$(window).on("scroll", function () {
    // if ($(window).scrollTop() > 72) {
    //     $("#SubHeader").addClass("fixed");
    // } else {
    //     $("#SubHeader").removeClass("fixed");
    // }
});

jQuery(document).ready(function($) {
    window.dispatchEvent(new Event('resize'));
    
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


    $('.toggle-search').click(function(){
        if($(this).hasClass('opened')) {
            $(this).removeClass('opened')
            $('.header__search').removeClass('opened')
        }else {
            $(this).addClass('opened')
            $('.header__search').addClass('opened')
        }
    })

    $('.header__search .submit-search').click(function(){
        $('.header__search form').submit()
    })

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

    var selectGradePlaceholder = 'Niveaux';
    if (gdn.meta.siteSection.contentLocale == 'en') {
        selectGradePlaceholder = 'Levels';
    }

    $('.select2-grade select').select2({
        minimumResultsForSearch: -1,
        placeholder: selectGradePlaceholder,
    });

    var selectCategoryPlaceholder = 'Matières';
    if (gdn.meta.siteSection.contentLocale == 'en') {
        selectCategoryPlaceholder = 'Topics';
    }

    $('.select2-category select').select2({
        placeholder: selectCategoryPlaceholder,
        minimumResultsForSearch: -1,
        templateResult: formatState
    }).on('select2:select', function (e) {
        var data = e.params.data;
        selectCategoryImg(data);
    });

    var selectLanguagePlaceholder = 'La langue';
    if (gdn.meta.siteSection.contentLocale == 'en') {
        selectLanguagePlaceholder = 'Language';
    }

    $('.select2-language select').select2({
        minimumResultsForSearch: -1,
        placeholder: selectLanguagePlaceholder,
    }).on('select2:select', function (e) {
        $('.select2-category select').val(null);
        $('.select2-category select').trigger('change');
    });

    selectCategoryImg({element: $('.FilterMenu .select2-category option:selected')});
    selectCategoryImg({element: $('.EditDiscussionDetail .select2-category option:selected')});

    $(window).click(function() {
        $('.Overlay').last().remove();
    });

    $(document).on('click', '.Popup', function(event) {
        event.stopPropagation();
    });

    // $(document).on('click', '.ToggleFlyout.Outside .mobileFlyoutOverlay', function(event) {
    //     $(this).remove();
    // });

    // $(document).on('click', '.Flyout.MenuItems', function(event) {
    //     event.stopPropagation();
    // });

    // $('.ToggleFlyout.OptionsMenu').click(function() {
    //     const menu = $(this).find('.mobileFlyoutOverlay').html();
    //     $(this).removeClass('Open');

    //     $('.ToggleFlyout.Outside').remove();
    //     $("body").append(`<div class="ToggleFlyout Outside">
    //         <div class="mobileFlyoutOverlay">${menu}</div></div>`);
    // })

    // $(document).on('click', '.ToggleFlyout.Outside a.EditComment', function(event) {
    //     $('.ToggleFlyout.Open a.EditComment').trigger('click');
    //     event.preventDefault();
    // })

    firebase.auth().onAuthStateChanged(function(user) {
        if (user) {
            user.getIdToken().then(function(idToken) {  // <------ Check this line

                $.ajax({
                    type: "POST",
                    url: "https://us-central1-alloprof-stg.cloudfunctions.net/apiFunctionsApp/geo/probe",
                    headers: {
                        'authorization': 'Bearer ' + idToken
                    },
                    dataType: 'json',
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        console.log(XMLHttpRequest.responseText);
                    },
                    success: function(json) {
                        const { inZone } = json;
                        localStorage.setItem("inZone", inZone);
                        storePosition(inZone);
                    }
                });
            });
        } else {
            $.ajax({
                type: "POST",
                url: "https://us-central1-alloprof-stg.cloudfunctions.net/apiFunctionsApp/geo/probe",
                dataType: 'json',
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    console.log(XMLHttpRequest.responseText);
                },
                success: function(json) {
                    const { inZone } = json;
                    localStorage.setItem("inZone", inZone);
                    storePosition(inZone);
                }
            });
        }
    });
    function storePosition(inZone = false, email = null, idToken = null) {
        var data = {
            inZone: inZone,
            email: email,
            idToken: idToken
        }
        $.ajax({
            type: "POST",
            url: gdn.url('/discussions/checkPosition'),
            data: data,
            dataType: 'json',
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(XMLHttpRequest.responseText);
            },
            success: function(json) {
                console.log(json)
            }
        });
    }
});