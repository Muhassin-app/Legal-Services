// Synchronizes data with hubspot
$(function(){

   

})

/** Hub Spot API */
function syncHubspotData(){
    spinnerJS.showSpinner();
    axios.post(`/client/hubspot/create-contact`)
    .then(async response => {
        console.log(response.data.status)
        if(response.data.status){
            spinnerJS.hideSpinner();
            sweetAlert.success('Success',response.data.message)
        } else {
            spinnerJS.hideSpinner();
            sweetAlert.error('Oops...',response.data.message)
        }
    })
    .catch(e => {
        spinnerJS.hideSpinner();
        sweetAlert.error('Oops...','Something went wrong, try again later!')
    })
}