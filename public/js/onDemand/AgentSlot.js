$(function(){
    initSlideDrag();
    showSelectedAgent()
    var slotValidater = 2;
  
})

function initSlideDrag(Id="",className="SlotItems"){
    const sliders = document.querySelectorAll(`.${className}`);
    let isDown = false;
    let startX;
    let scrollLeft;
    //sliders.each(function(slider){
    $( sliders ).each(function( index,slider ) {
      
       let SelectedElements = slider.getElementsByClassName('checked_item');
       if(SelectedElements.length > 0){
          let selectedoffsetLeft = SelectedElements[0].offsetLeft;
        //   console.log(selectedoffsetLeft);
        //   slider.scrollTo(slider.scrollLeft, selectedoffsetLeft);
        //     //slider.scrollTo(selectedoffsetLeft)
        //     slider.animate({scrollLeft: selectedoffsetLeft}, 400)
            slider.scrollLeft = (selectedoffsetLeft - 35);

        //slider.scrollTo(SelectedElements); 
       }else{
        slider.scrollTo(slider.scrollLeft + 1, 0);
       }
        // var selectedPosition = $(".checked_item").offset().left;
        // slider.scrollTo(selectedPosition); // without animation.

        //slider.animate({scrollLeft: selectedPosition}, 400) 
       //slider.scrollTo(slider.scrollLeft + 1, 0);
        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            slider.classList.add('active');
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
            });
        slider.addEventListener('mouseleave', () => {
            isDown = false;
            slider.classList.remove('active');
        });
        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.classList.remove('active');
        });
        slider.addEventListener('mousemove', (e) => {
            if(!isDown) return;
                e.preventDefault();
                let x = e.pageX - slider.offsetLeft;
                let walk = (x - startX) * 3; //scroll-fast
                slider.scrollLeft = scrollLeft - walk;
        });
    })
 
}

function showSelectedAgent(){
   var selectedSlotDiv =  $('input[type=radio][name=booking_time]:checked').parent('div');
   if(selectedSlotDiv.length>0){
        let selectedSpan =  selectedSlotDiv.find('.selected-time');
        let agent_ids = selectedSpan.data("agent_ids");
        let show_agent = selectedSpan.data("show_agent");
        let cart_product_id = selectedSpan.data("cart_product_id");
        let selected_agent = selectedSpan.data("selected_agnet_id");
        
        if((show_agent != undefined && show_agent ==1  ) && (agent_ids != undefined && agent_ids !='' )   ){
            showDispatchDriver(agent_ids,cart_product_id,selected_agent);
        }
   }

}

async  function showDispatchDriver(driver_ids,cart_product_id,selected_agent=''){
    var driverIdArray = driver_ids;
    var agentData = dispatch_agents.agent;
    var html=`<div class="grid-item main alCustomHomeServiceAgentRadio d-flex justify-content-center radios agentS_${cart_product_id}">`;
    var first = 0;
    agentData.forEach(function(data,index) {
        if( driverIdArray.includes(data.id)){
            let Selectedclass =  (first ==0 )? 'selected_agent': '';
            if(selected_agent !='' && selected_agent != undefined ){
                 Selectedclass = selected_agent === data.id  ? 'selected_agent' : '';
            }
            html +=`<div class="agent_slot">
                    <div>
                        <a class="agentInfo d-block dispatch_agent ${Selectedclass} black-box"  data-cart_product_id="${cart_product_id}" data-agent_id="${data.id}" href="javascript:void(0)">
                            <div class="brand-ing">
                                <img class="agentImg" src="${data.image_url}" alt="${data.name}" title="">
                            </div>
                            <h6>${data.name}</h6>
                        </a>
                    </div>
                </div>`
            first = 1;
        }
        
    });
   
    html +=`</div>`;
    $(`.agent_slots${cart_product_id}`).html(html);
}

//checkSlotTimeSelecedValidation for ondeman continue to cart on prees next-button-ondemand-3
async function checkSlotTimeSelecedValidation(){
     slotValidater = 2;
    const selected_booking_date = document.getElementsByClassName(`booking_date_section`); 
    $( selected_booking_date ).each(function( index,booking_date ) {
        // check booking date is selected or not
        var Dateinput =  booking_date.getElementsByClassName('booking_date');
        if(Dateinput.length > 0)
        {
            var inputName = Dateinput[0].name;
            var schedule_date = $(`input[name='${inputName}']:checked`).val();
            if(schedule_date == '' || schedule_date === undefined ){
                slotValidater =1;
                return slotValidater;
            }
        }
        // check booking time is selected or not
        var Timeinput =  booking_date.getElementsByClassName('booking_time');
        if(Timeinput.length > 0)
        {
            var inputTimeName = Timeinput[0].name;
            var schedule_time = $(`input[name='${inputTimeName}']:checked`).val();
            if(schedule_time == '' || schedule_time== undefined ){
                slotValidater =1;
                return slotValidater;
            }
        }
    }); 
    console.log(slotValidater);
    return slotValidater;
   
}


$(document).on('click','.dispatch_agent',function(){
    $('.dispatch_agent').removeClass('selected_agent');
    $(this).addClass('selected_agent');
    
    var agent_id = $(this).data('agent_id');
    var cart_product_id = $(this).data('cart_product_id');
    console.log(agent_id);
    console.log(cart_product_id);
    console.log(update_cart_product_schedule_agnet);
    var formData ={
        "dispatch_agent_id" : agent_id,
        "cart_product_id" : cart_product_id
    }
    axios.post(update_cart_product_schedule_agnet, formData)
        .then(async response => {
         console.log(response);
            if(response.data.status == "Success"){
                // Swal.fire({
                //     icon: 'success',
                //     title: 'Success',
                //     text: response.data.message,
                // })
            } else{
                // Swal.fire({
                //     icon: 'error',
                //     title: 'Oops',
                //     text: response.data.message,
                // })
            }
        })
        .catch(e => {
            console.log(e);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong, try again later!',
            })
        })  

})
