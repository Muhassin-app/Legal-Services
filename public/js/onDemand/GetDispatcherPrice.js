/**
 * Store all portal localstorage
 * @Author  Mr Harbans singh
 */

$(function(){
    OrderSessionStorage.removeStorageSingle('variant_id');
    OrderSessionStorage.removeStorageSingle('dispatcherAgent');
    OrderSessionStorage.removeStorageSingle('add_to_cart_url');
    OrderSessionStorage.removeStorageSingle('vendor_id');
    OrderSessionStorage.removeStorageSingle('product_id');
    OrderSessionStorage.removeStorageSingle('this');
    OrderSessionStorage.removeStorageSingle('onDemandBookingdate');
    OrderSessionStorage.removeStorageSingle('address_id');
    OrderSessionStorage.removeStorageSingle('slot');
})



$(document).on('click','.view_on_demand_price',async function(){
    var variant_id = $(this).data('variant_id');
    OrderSessionStorage.setStorageSingle('variant_id',variant_id);
    OrderSessionStorage.setStorageSingle('add_to_cart_url',$(this).data('add_to_cart_url'));
    OrderSessionStorage.setStorageSingle('vendor_id',$(this).data('vendor_id'));
    OrderSessionStorage.setStorageSingle('product_id',$(this).data('product_id'));
    OrderSessionStorage.setStorageSingle('this',JSON.stringify($(this)));
    document.getElementById('driver_product_variant_id').value = variant_id;
    $selectedAddress = OrderStorage.getStorage('cartAddressId');
    if ($selectedAddress !='' &&  $selectedAddress !=undefined){
        $("#productPrice_address_id").val($selectedAddress);
        document.getElementById("productPrice_address_id").disabled = true;
    }else{
        document.getElementById("productPrice_address_id").disabled = false;
    }
    var todayDate = document.getElementById('productPriceModel_todayDate').value
    var formData ={
        "date" : todayDate,
    }
    ///$('.select-2').select2();
    await getGerenalSlot(formData);
    $('#driver_sort_by').hide();
    $('#productPriceModel').modal('show');
    $(`#listofdrivers`).html('');
  
})

$(document).on('click','#search_Driver_fee',async function(e){
    e.preventDefault();
    var variant_id   = document.getElementById('driver_product_variant_id').value
    var onDemandBookingdate = document.getElementById('onDemandBookingdate').value
    var address_id = document.getElementById('productPrice_address_id').value
    var slot = document.getElementById('productPrice_slot').value

    OrderSessionStorage.setStorageSingle('onDemandBookingdate',onDemandBookingdate);
    OrderSessionStorage.setStorageSingle('address_id',address_id);
    OrderSessionStorage.setStorageSingle('slot',slot);
   
    if((variant_id =='' || variant_id == undefined )|| (onDemandBookingdate =='' || onDemandBookingdate == undefined) || (address_id =='' || address_id == undefined) || (slot  =='' || slot == undefined)){
        Swal.fire({
            icon: 'error',
            title:_language.getLanString('Oops...'),
            text: _language.getLanString('All fields Required !!'),
        })
        return false;
    }
    var Driver_price_formData ={
        "variant_id" : variant_id,
        "onDemandBookingdate" : onDemandBookingdate,
        "address_id" : address_id,
        "slot" : slot
    }
   await getDiverPrice(Driver_price_formData)
})
async function getDiverPrice(formData){
    formData._token =  $('meta[name="csrf-token"]').attr('content');
     axios.post(`/get_price_from_dispatcher`, formData)
        .then(async response => {
         console.log(response.data);
            if(response.data.status == "Success"){
                console.log('success');
              var dispatch_agent = response.data.data;
               OrderSessionStorage.setStorageSingle('dispatcherAgent',JSON.stringify(dispatch_agent));
               $('#driver_sort_by').show();
               await renderAgent();
              
            } else{
                Swal.fire({
                    icon: 'error',
                    title:_language.getLanString('Oops...'),
                    text: _language.getLanString('Something went wrong, try again later!'),
                })
            }
        })
        .catch(e => {
            console.log(e);
            Swal.fire({
                icon: 'error',
                title:_language.getLanString('Oops...'),
                text: _language.getLanString('Something went wrong, try again later!'),
            })
        })  

}
//filter 0 =  rendom ,1= by price , 2
async function renderAgent(filter=0){
    var html = '';
    var AgentData= JSON.parse(OrderSessionStorage.getStorage('dispatcherAgent'));
    console.log(AgentData);
    var product_variant_id = OrderSessionStorage.getStorage('variant_id');
   
    if(AgentData.length > 0){
        AgentData.forEach(function(data,index) {
            var dirvePrice = data?.product_prices[0]?.price || 0;
            let price = NumberFormatHelper.formatPrice(dirvePrice);
                html +=`<div class="card dispatcherAgent" data-agent_id="${data?.id}" data-agent_price="${dirvePrice}"  data-agent_rating="${data.rating}" data-product_variant_id="${product_variant_id}"  data-agent_averageTaskComplete="${data?.averageTaskComplete}">
                   <div class="card-body p-3 bg-light">
                     <div class="d-flex justify-content-between">
                         <div class="userDetails d-flex align-items-center">
                             <div class="userDetailsImage mr-2"> <img class="w-100" src="${data.image_url}" alt="${data.name}" title=""></div>
                             <ul class="userDetailsNameJob p-0 m-0">
                                 <li class="userDetailsName d-block">${data.name}</li>
                                 <li class="userDetailsJobDone  d-block">${_language.getLanString('Jobs Done ')} <b class="text-success">${data?.complete_order_count}</b></li>
                             </ul>
                         </div>
                         <div class="userDetailsRating">
                             <ul class="userDetailsNameJob p-0 m-0 text-right">
                                 <li class="userDetailsRating text-right d-block">  
                                   <label class="rating-star "  >
                                       <i class="fa fa-star${ (data.rating >= 1) ? ' checked' : '-o'}"></i>
                                       <i class="fa fa-star${ (data.rating >= 2) ? ' checked' : '-o'}"></i>
                                       <i class="fa fa-star${ (data.rating >= 3) ? ' checked' : '-o'}"></i>
                                       <i class="fa fa-star${ (data.rating >= 4) ? ' checked' : '-o'}"></i>
                                       <i class="fa fa-star${ (data.rating >= 5) ? ' checked' : '-o'}"></i>
                                   </label>
                                  </li>
                                  <li class="userDetailsJobDone  d-block"><span class="text-right text-success">${currencySymbol +' '+ price } </span> </li>
                                
                             </ul>
                         </div>
                     </div>
                   </div>
                 </div>`;
           });
    }else{
       html +=`<div class="empty_driver_price"><h2>No Results Found</h2>
       <p>We Couldn't Find what you searched.for <br/> Try Searching Again </p></div>`; 
    }

       $(`#listofdrivers`).html(html);
       console.log('renderAgent');
       await sortAgentBox()
}

$(document).on('change','#driver_sort_by',function(e){
    e.preventDefault();
    sortAgentBox();
})
$(document).on('change','#onDemandBookingdate',function(e){
    var date = document.getElementById('onDemandBookingdate').value
    var formData ={
        "date" : date,
    }
    getGerenalSlot(formData);
})

async function getGerenalSlot(formData){
    formData._token =  $('meta[name="csrf-token"]').attr('content');
    axios.post(`/get_gerenal_slot`, formData)
    .then(async response => {
        if(response.data.status == "Success"){
          var html = response.data.html;
            $('#productPrice_slot').html(html);
           // $('#productPrice_slot').select2();
        } else{
                Swal.fire({
                    icon: 'error',
                    title:_language.getLanString('Oops...'),
                    text: _language.getLanString('Something went wrong, try again later!'),
                })
        }
    })
    .catch(e => {
        console.log(e);
        Swal.fire({
            icon: 'error',
            title:_language.getLanString('Oops...'),
            text: _language.getLanString('Something went wrong, try again later!'),
        })
    })  
}

$(document).on('click','.dispatcherAgent',function(e){
    e.preventDefault();
   
    var agent_price = $(this).data('agent_price');
    var agent_id = $(this).data('agent_id');
    var ajaxCall = 'ToCancelPrevReq';

    var variant_id      = OrderSessionStorage.getStorage('variant_id');
    var add_to_cart_url = OrderSessionStorage.getStorage('add_to_cart_url');
    var vendor_id       = OrderSessionStorage.getStorage('vendor_id');
    var product_id      = OrderSessionStorage.getStorage('product_id');
    var address_id      = OrderSessionStorage.getStorage('address_id');
    var slot            = OrderSessionStorage.getStorage('slot');
    let that            = JSON.parse(OrderSessionStorage.getStorage('this'));
    var cart_agent_id    = OrderSessionStorage.getStorage('dispatcher_agent_id');
    console.log(agent_id);
    console.log(cart_agent_id);
    if(cart_agent_id && (agent_id != cart_agent_id)){
        Swal.fire({
            title: _language.getLanString('Warning!'),
            text: _language.getLanString('Please select the service of same provider'),
            icon: 'warning',
          });
          return false;
    }
    
    var dispatcherAgentData ={
        "agent_price"     : agent_price,
        "agent_id"        : agent_id,
        "address_id"      : address_id,
        "slot"            : slot,
        "onDemandBookingdate" : OrderSessionStorage.getStorage('onDemandBookingdate')
    }
    var show_plus_minus = "#show_plus_minus" + product_id;
     Swal.fire({
        title: _language.getLanString('Warning!'),
        text: _language.getLanString('You want to add this price to cart?'),
        icon: 'warning',
      }).then(({value}) => {
        console.log(value);
            if (value === true) {
                $(`#listofdrivers`).html('');
                $('#productPriceModel').modal('hide');
                addToCartOnDemand(ajaxCall, vendor_id, product_id, addonids, addonoptids, add_to_cart_url, variant_id, show_plus_minus, that,dispatcherAgentData);
            } 
      });
   
})

async function sortAgentBox(){
    $sortBy = $('#driver_sort_by').val();
    var $wrap = $('#listofdrivers');
    $wrap.find('.dispatcherAgent').sort(function(a, b) 
    {
        if($sortBy ==2){
            console.log(' sortAgentBox by agent_rating');
            return +b.dataset.agent_rating -
            +a.dataset.agent_rating;
        }else if($sortBy == "low_to_high"){
            console.log('sortAgentBox by agent_price low_to_high');
            return +a.dataset.agent_price - +b.dataset.agent_price;
        }
        else if($sortBy == "high_to_low"){  
            console.log('sortAgentBox by agent_price high_to_low');
            return +b.dataset.agent_price - +a.dataset.agent_price;
        }else{
            console.log('sortAgentBox by averageTaskComplete');
            return +b.dataset.agent_rating -
            +a.dataset.agent_rating;
        }
       
    })
    .appendTo($wrap);

}

