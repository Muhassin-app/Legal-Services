$(document).ready(function() {

    $(".alHamBurgerIcon").click(function(){
        $(".alSpaMenuCard").addClass("active");
        $("body").addClass("aloverFlow");
    });
    $(".alMenuClose").click(function(){
        $(".alSpaMenuCard").removeClass("active");
        $("body").removeClass("aloverFlow");
    });

    $(".regular").slick({
      dots: true,
      infinite: true,
      slidesToShow: 1,
      slidesToScroll: 1,
      fade: true,
      asNavFor: '.regular-nav'
    });

    $('.regular-nav').slick({
       slidesToShow: 5,
       slidesToScroll: 1,
       asNavFor: '.regular',
       dots: true,
       autoplay: false,
       focusOnSelect: true
    });

    $('.alSpaListSlider').slick({
      arrows: false,
      dots: false,
      slidesToShow: 5,
      infinite: false,
      responsive: [{
        breakpoint: 1024,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
        }

      }, {
        breakpoint: 800,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 2,
          infinite: true,

        }
      },  {
        breakpoint: 480,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          infinite: true,
          autoplay: true,
          autoplaySpeed: 2000,
          centerMode: true,
        }
      }]
    });

    $('.spaMenu-slider').slick({
      arrows: true,
      dots: true,
      slidesToShow: 6,
      slidesToScroll: 1,
      centerMode: true,
      centerPadding: '100px',
      responsive: [
      {
        breakpoint: 1400,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
        }

      },{
        breakpoint: 480,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1,
        }
      }]
    });
    $('.Spasslider').slick({
      arrows: true,
      dots: false,
      slidesToShow: 5,
      slidesToScroll: 1,
      infinite: true,
      autoplay: true,
      autoplaySpeed: 2000,
      responsive: [
      {
        breakpoint: 1400,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
        }

      },{
        breakpoint: 480,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1,
        }
      }]
    });

    $('.clientsFeedBackSlider').slick({
      arrows: true,
      dots: true,
      slidesToShow: 1,
      slidesToScroll: 1,
      infinite: true,
      autoplay: true,
      autoplaySpeed: 2000,
    });

    $('.alSLslider').slick({
            dots: false,
            infinite: true,
            speed: 500,
            slidesToShow: 8,
            slidesToScroll: 1,
            autoplay: false,
            autoplaySpeed: 2000,
            arrows: true,
            responsive: [{
            breakpoint: 1400,
            settings: {
               slidesToShow: 6,
            }
            },
            {
               breakpoint: 1199,
               settings: {
                  slidesToShow: 4,
               }
            }]
      });
      $('#inputDate').datepicker({
      });
});