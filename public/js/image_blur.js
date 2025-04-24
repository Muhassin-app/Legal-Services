$(function(){
    let lazy = document.getElementsByClassName("blurload");
    for (let n = 0, len = lazy.length; n < len; n++) {
      lazy[n].setAttribute("src", lazy[n].getAttribute("data-src"));
      lazy[n].addEventListener("load", function(e) {
        e.target.classList.add("no-blur");
      });
    }
});