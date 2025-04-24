$(document).ready(function () {
    setMapHeight();
  
});
function setMapHeight(){
    var footer_height = $('#footer').height();
    var header_height = $('.site-header').height();
    var htmlHeight = $("body").height();
    var stepFormHeight = $("#booking-map").height();
    
    var mainWindowHeight = htmlHeight - (footer_height + header_height);
    console.log(mainWindowHeight);
    $(".alSpaceTopBar").css('margin-top', header_height+'px');
    $("#content-wrap").css('padding-bottom', footer_height);
   // $(".alFullMapForm").css('height', stepFormHeight);

    var result = parseInt(stepFormHeight) - parseInt(70);
    $(".alFullMapForm").css('height', result);
    //alert(result);
}