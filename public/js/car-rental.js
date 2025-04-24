$(document).ready(function () {
  $(".filter_cta").click(function () {
    $("body").addClass("filter_open");
  });
  $(".close_filter").click(function () {
    $("body").removeClass("filter_open");
  });
});

$(window).scroll(function () {
  if ($(window).scrollTop() >= 150) {
    $('header').addClass('fixed-header');

  }
  else {
    $('header').removeClass('fixed-header');

  }
});

$(document).ready(function () {
  var newDiv = $('<div>').text('This is a new <div> element');
  $('#accounting_vendor_datatable').append(newDiv);
});
$(document).ready(function () {
  var newDiv = $('<div>').text('This is a new <div> element');
  $('.table-responsive').after(newDiv);
});

$(function () {
  AOS.init({
    duration: 1000
    ,
  });
});


$(".fleet_slider").slick({
  slidesToShow: 6
  , infinite: false
  , slidesToScroll: 1
  , autoplay: true
  , autoplaySpeed: 2000
  , dots: false
  , arrows: true
  , responsive: [{
    breakpoint: 1200
    , settings: {
      slidesToShow: 4
      ,
    }
  }
    , {
    breakpoint: 991
    , settings: {
      slidesToShow: 3
      ,
    }
  }
    , {
    breakpoint: 767
    , settings: {
      slidesToShow: 2
      ,
    }
  }
    , {
    breakpoint: 576
    , settings: {
      slidesToShow: 1
      ,
    }
  }
  ]
});

$(".menu_cta").click(function () {
  $("html").toggleClass("active");
});

// Show the first tab and hide the rest
$('#tabs-nav li:first-child').addClass('active');
$('.tab-content').hide();
$('.tab-content:first').show();

// Click function
$('#tabs-nav li').click(function () {
  $('#tabs-nav li').removeClass('active');
  $(this).addClass('active');
  $('.tab-content').hide();

  var activeTab = $(this).find('a').attr('href');
  $(activeTab).fadeIn();
  return false;
});

const pickup = document.getElementById('pickup_location');
const dropoff = document.getElementById('drop_location');
var pickupautocomplete = new google.maps.places.Autocomplete(pickup);
var dropoffautocomplete = new google.maps.places.Autocomplete(dropoff);
// Pickup Address
google.maps.event.addListener(pickupautocomplete, 'place_changed', function () {
  let place = pickupautocomplete.getPlace();
  document.getElementById('pickup_longitude').value = place.geometry.location.lng();
  document.getElementById('pickup_latitude').value = place.geometry.location.lat();
});

// Drop Off Address
google.maps.event.addListener(dropoffautocomplete, 'place_changed', function () {
  let place = dropoffautocomplete.getPlace();
  document.getElementById('drop_longitude').value = place.geometry.location.lng();
  document.getElementById('drop_latitude').value = place.geometry.location.lat();
});

$("input[name='service']").click(function () {
  if ($(this).val() == 'yacht') {
    $('#location').attr('required', false);
    $('#seats_div').show();
  } else if ($(this).val() == 'airport') {
    $('#location').attr('required', true);
    $('#seats_div').hide();
  } else {
    $('#location').attr('required', false);
    $('#seats_div').hide();
  }
})

$("#range-datepicker").flatpickr({
  mode: "range",
  enableTime: true,
  allowInput:true,
  
    minDate: "today",

  dateFormat: "d M Y H:i", //change format also 
  onClose: function(selectedDates, dateStr, instance) {
    readonlyElement($("#range-datepicker"), false)
    console.log(selectedDates)
  }
});

$(document).on('paste input focus', '#range-datepicker', function() {
  readonlyElement($(this));
});

function readonlyElement(element, action = true){
  element.prop('readonly', action);
}

$(document).ready(function(){
  $(".filter_cta").click(function(){
    $("body").addClass("filter_open");
  });
  $(".close_filter").click(function(){
    $("body").removeClass("filter_open");    
  });

  $(document).on('change' ,'#diff-location' , function(e){
    if($(this).is(':checked')){
      $('#dropoff-box').show();
      $('#dropoff-box').find('input').attr('required', true).val('')
    }else{
      $('#dropoff-box').hide();
      $('#dropoff-box').find('input').attr('required', false).val('')
    }
  })
});

$(document).on('change', 'input[name="service"]', function(e){
  switch($(this).val()){
    case 'yacht':
      $('#dropoff-box').hide();
      $("#diff-box").addClass('d-none');
    break;
    default:
      $("#diff-box").removeClass('d-none');
  }
});


$(document).on('change', 'input[name="booking_duration"]', function(e){
  $('#prod_variant_id').val($(this).val());
})