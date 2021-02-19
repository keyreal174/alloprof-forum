jQuery(document).ready(function($) {
    $('.PanelCategories').slick({
        infinite: false,
        prevArrow: `<span class="slick-prev-arrow"><svg width="24" height="15" viewBox="0 0 24 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.4995 7.36377H1.49951" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7.88545 13.7277L1.52148 7.36377L7.88545 0.999809" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>`,
        nextArrow: `<span class="slick-next-arrow"><svg width="24" height="15" viewBox="0 0 24 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.50049 7.36377H22.5005" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16.1146 13.7277L22.4785 7.36377L16.1146 0.999809" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg></span>`,
        slidesToShow: 4,
        slidesToScroll: 4,
        responsive: [
            {
              breakpoint: 1300,
              settings: {
                slidesToShow: 3,
                slidesToScroll: 3,
              }
            },
            {
              breakpoint: 1024,
              settings: {
                slidesToShow: 2,
                slidesToScroll: 2
              }
            },
            {
              breakpoint: 900,
              settings: {
                slidesToShow: 1,
                slidesToScroll: 1
              }
            }
          ]
    });
})