<link href="https://releases.transloadit.com/uppy/v3.7.0/uppy.min.css" rel="stylesheet">
<style>
.uppy-Dashboard-AddFiles-info{
    display: none !important;
}
.mediaSidenav {
  height:100vw;
  width: 320px;
	margin-top:0;
  position: fixed;
  z-index:9999;
  top: 0;
  right:0px;
  overflow-x: hidden;
  transition: 0.5s;
  padding-top: 0;
  transform: translateX(100%)
}
.push_to_side #mediaSidenav{
  transform: translateX(0%)
}
.push_to_side {
    overflow: hidden;
    position: relative;
}
.push_to_side:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #000;
    z-index: 9999;
    opacity: 0.2;
}
.uppy-Dashboard-inner{
  height: 100vh!important;
}


.mediaSidenav a {
  padding: 8px 8px 8px 32px;
  text-decoration: none;
  font-size: 25px;
  color: #818181;
  display: block;
  transition: 0.3s;
}
.chat-input-section i {
    font-size: 18px;
}

.mediaSidenav a:hover {
  color: #f1f1f1;
}

.mediaSidenav .closebtn {
    position: absolute;
    top: 2px;
    left: 4px;
    font-size: 22px;
    z-index: 2000;
    padding: 1px 7px 3px;
    background-color: #43bee1;
    border-radius: 5px;
    color: #fff;
    display: inline-block;
    line-height: 22px;
    transform: translateY(-2px);
}

body {
  transition: margin-right .5s;
  padding: 16px;
}

@media screen and (max-height: 450px) {
  .mediaSidenav {padding-top: 15px;}
  .mediaSidenav a {font-size: 18px;}
}
/* .push_to_side{
	margin-right: 250px !important;
} */
/* .push_to_side #wrapper{
	margin-right: 250px;
} */


</style>


<div id="mediaSidenav" class="mediaSidenav">
    <a href="javascript:void(0)" class="closebtn" onclick="openMediaNav()">&times;</a>
    <input  style="display: none;" type="file" id="uppy-select-files">
    <div id="uppy-progress"></div>
</div>

<script src="https://releases.transloadit.com/uppy/v3.7.0/uppy.min.js"></script>
<script src="{{asset('assets/js/chat/chatMedia.js')}}"></script>
<script>

function openMediaNav() {
	$('body').toggleClass('push_to_side');
    //   document.getElementById("mySidenav").style.width = "250px";
    //   document.getElementById("wrapper").style.marginRight = "250px";
    //   document.body.style.backgroundColor = "rgba(0,0,0,0.4)";
}
</script>