function phoneNumbervalidation(iti, input)
{
    $('<span id="phone-valid-msg" class="hide phone-error-msg"></span><span id="phone-error-msg" class="hide phone-error-msg"></span>').appendTo(input.parentElement);
    
    var input = input;
    //alert(input);
    var errorMsg = document.querySelector("#phone-error-msg");
    var validMsg = document.querySelector("#phone-valid-msg");
        
        // here, the index maps to the error code returned from getValidationError - see readme
        var errorMap = ["Invalid number", "Invalid country code", "Too short", "Too long", "Invalid number"];
        

        var reset = function() {
            input.classList.remove("is-invalid");
            errorMsg.innerHTML = "";
            errorMsg.classList.add("hide");
            validMsg.classList.add("hide");
        };

        // on blur: validate
        input.addEventListener('change', function() {
            reset();
            if (input.value.trim()) 
            {
                if (iti.isValidNumber()) {
                    validMsg.classList.remove("hide");
                } else {
                    input.classList.add("is-invalid");
                    var errorCode = iti.getValidationError();
                    errorMsg.innerHTML = errorMap[errorCode];
                    errorMsg.classList.remove("hide");
                }
            }
        });

        /* // on keyup / change flag: reset
        input.addEventListener('change', reset);
        input.addEventListener('keyup', reset); */
}