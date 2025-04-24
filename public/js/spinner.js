function add_spinner(id,text = ""){
    remove_spinner(id);
    var html = `<div id="overlay">
            <div class="cv-spinner">
            <span class="spinner d-block"></span>
            <span>${text}</span>
            </div>
        </div>`;
    $(id).prepend(html);
}
function remove_spinner(id){
    $(id + ' > #overlay' ).remove();
}