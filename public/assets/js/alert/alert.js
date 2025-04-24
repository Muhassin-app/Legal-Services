/**
 * custom alert Js 
 * sweet alert
 * @dependancy = Sweet alert Js
 */
const sweetAlert = {
    success(title='Success',text='Successfully done!') {
        Swal.fire({
            icon: 'success',
            title: title,
            text: text,
            //footer: '<a href="">Why do I have this issue?</a>'
        })
    },
    error(title='Oops...',text='Something went wrong, try again later!') {
        Swal.fire({
            icon: 'error',
            title: title,
            text: text,
          })
    },
    conformation(title='Are you Sure?') {
        Swal.fire({
            icon: 'error',
            title: title,
            text: text,
          })
          Swal.fire({
            title: title,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ok',
         }).then((result) => {
            if(result.value)
            {
               
            }
         })
    }

   
    
}