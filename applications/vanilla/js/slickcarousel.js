jQuery(document).ready(function($) {
    $('.PanelCategories').on('init', function(event, slick) {
        console.log("initialized");
        $('.PanelCategories').css({"opacity": 1});
    });

    $('.PanelCategories').slick({
        infinite: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        lazyLoad: 'ondemand',
        // responsive: [
        //     {
        //       breakpoint: 1300,
        //       settings: {
        //         slidesToShow: 3,
        //         slidesToScroll: 3,
        //       }
        //     },
        //     {
        //       breakpoint: 1024,
        //       settings: {
        //         slidesToShow: 2,
        //         slidesToScroll: 2
        //       }
        //     },
        //     {
        //       breakpoint: 900,
        //       settings: {
        //         slidesToShow: 1,
        //         slidesToScroll: 1
        //       }
        //     }
        //   ]
    });



    $('.PanelCategories .next').click(function() {
        $('.PanelCategories').slick('slickNext');
    });

    $('.PanelCategories .prev').click(function() {
        $('.PanelCategories').slick('slickPrev');
    });
})