/**
 * custom spinner Js
 */
const spinnerJS = {
    showSpinner() {
        $('.nb-spinner-main').addClass('show-nb-spinner-main');
    },
    showSpinnerCustom() {
        $('.order_data_box').addClass('show-nb-spinner-main');
    },
    hideSpinner(){
        setTimeout(() => {
            $('.nb-spinner-main').removeClass('show-nb-spinner-main');
        },800);
    },
    hideSpinnerCustom(){
        setTimeout(() => {
            $('.order_data_box').removeClass('show-nb-spinner-main');
        },800);
    }
}

document.onreadystatechange = function () {
    var state = document.readyState
    if (state == 'interactive') {
        spinnerJS.showSpinner();
        //spinnerJS.showSpinnerCustom();
    } else if (state == 'complete') {
        spinnerJS.hideSpinner();
       // spinnerJS.hideSpinnerCustom();
    }
}