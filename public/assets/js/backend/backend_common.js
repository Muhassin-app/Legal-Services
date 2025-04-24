$(function(){

       //For Select2 library fuction  
       $('.select2-single').select2();
       $('.select2-multiple').select2();
   

    $(document).on('change','.checkbox_change',function(){
        var This = this;
        checkobox_action(This,$(This).data('classname'));
    })

});
/**
 * checkbox attritbute change value
 * @param {*} val 
 * @param {*} id 
 */
function checkobox_action(This,id=""){
    var action_val = 0; 
     if($(This).is(":checked")){
        action_val = 1;
    } else {
        action_val = 0;
    }
    $(`#${id}`).val(action_val);
}