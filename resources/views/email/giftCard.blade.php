{{-- <style>
    .box {
    position: relative;
    max-width: 450px;
    width: 90%;
    height: 262px;
    background: #fff;
    box-shadow: 0 0 15px rgba(0,0,0,.1);
    margin: 20px auto;
}

/* common */
.ribbon {
  width: 150px;
  height: 150px;
  overflow: hidden;
  position: absolute;
  z-index: 1;
}
.ribbon::before,
.ribbon::after {
  position: absolute;
  z-index: -1;
  content: '';
  display: block;
  
}
.ribbon span {
  position: absolute;
  display: block;
  width: 225px;
  padding: 15px 0;
  box-shadow: 0 5px 10px rgba(0,0,0,.1);
  color: #fff;
  font: 700 18px/1 'Lato', sans-serif;
  text-shadow: 0 1px 1px rgba(0,0,0,.2);
  text-transform: uppercase;
  text-align: center;
}

/* top left*/
.ribbon-top-left {
  top: -10px;
  left: -10px;
}
.ribbon-top-left::before,
.ribbon-top-left::after {
  border-top-color: transparent;
  border-left-color: transparent;
}
.ribbon-top-left::before {
  top: 0;
  right: 0;
}
.ribbon-top-left::after {
  bottom: 0;
  left: 0;
}
.ribbon-top-left span {
  right: -25px;
  top: 30px;
  transform: rotate(-45deg);
}


.gift-card__msg {
  font-size: 10px;
  display: block;
  margin-top: 10px;
}

.gift-card__details {
  margin-top: auto;
  align-items: center;
  line-height: 1;
}

.gift-card__code {
  display: inline-block;
  background: white;
  color: black;
  padding: 10px 13px;
  margin-top: 20px;
  font-size: 20px;
  border: 1px solid #e3e3e3;
}

.gift-card__amount {
  font-size: 70px;
}
.gift-card__amount-remaining {
  font-size: 14px;
  margin-top: 7px;
}
.gift-card__image {
  border-top-left-radius: 10px;
  border-bottom-left-radius: 10px;
  max-width: 150px;
  background-size: cover;
  background-image: url({{  $GiftCard->image['proxy_url'].'100/100'.$GiftCard->image['image_path'] }});
}
.green__ribbon span{
    background-color: #93C63C;
}
.green__ribbon:before,
.green__ribbon:after{
    border: 5px solid #4E7212;
}
.red__ribbon span{
    background-color: #EF1313;
}
.red__ribbon:before,
.red__ribbon:after{
    border: 5px solid #780C0C;
}
      </style>
<div class="wrapper ">
  
    <div class="box">
       <div class="ribbon ribbon-top-left green__ribbon"><span>{{ $GiftCard->title }}</span></div>
       <article class="gift-card row m-0">
          <div class="gift-card__image col-4 p-0">
          </div>
          <section class="gift-card__content col-8 text-right pt-4">
                <div class="gift-card__amount">{{$currSymbol. $GiftCard->amount }}</div>
                
                <div class="gift-card__code w-100 text-center bg-light">{{ $GiftCard->userCode }}</div>
                <div class="gift-card__msg mb-2 text-truncate">{{ $GiftCard->short_desc }}</div>
          </section>
          </article>
    </div>
 </div> --}}

 <head>
    <style type="text/css">
  .green__ribbon span{
    background-color: #93C63C;
  }
  .green__ribbon:before,
  .green__ribbon:after{
    border: 5px solid #4E7212;
  }
  .ribbon {
    width: 150px;
    height: 150px;
    overflow: hidden;
    position: absolute;
    z-index: 1;
  }
  .ribbon::before,
  .ribbon::after {
    position: absolute;
    z-index: -1;
    content: '';
    display: block;
    
  }
  .ribbon span {
    position: absolute;
    display: block;
    width: 225px;
    padding: 15px 0;
    box-shadow: 0 5px 10px rgba(0,0,0,.1);
    color: #fff;
    font: 700 18px/1 'Lato', sans-serif;
    text-shadow: 0 1px 1px rgba(0,0,0,.2);
    text-transform: uppercase;
    text-align: center;
  }
  
  /* top left*/
  .ribbon-top-left {
    top: -10px;
    left: -10px;
  }
  .ribbon-top-left::before,
  .ribbon-top-left::after {
    border-top-color: transparent;
    border-left-color: transparent;
  }
  .ribbon-top-left::before {
    top: 0;
    right: 0;
  }
  .ribbon-top-left::after {
    bottom: 0;
    left: 0;
  }
  .ribbon-top-left span {
    right: -25px;
    top: 30px;
    transform: rotate(-45deg);
  }
    </style>
  
  </head>
  
  <body bgcolor="#E1E1E1" leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
    <center style="background-color:#E1E1E1;">
      <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTbl" style="table-layout: fixed;max-width:100% !important;width: 100% !important;min-width: 100% !important;">
        <tr>
          <td align="center" valign="top" id="bodyCell" style="width: 500px;margin: 0 auto;border-radius: 15px;overflow: hidden;">          
  
            <table bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" width="500" id="emailBody" style="width: 500px;margin: 0 auto;border-radius: 15px;overflow: hidden;">
  
              <tr>
                <td align="center" valign="top">
                  <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#fff" style="width: 500px;margin: 0 auto;">
                    <tr>
                      <td align="center" valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="500" class="flexibleContainer" style="width: 500px;margin: 0 auto;">
                          <tr>
                            <td align="center" valign="top" width="500" class="flexibleContainerCell" style="width: 500px;margin: 0 auto;">
                              <table border="0" cellpadding="30" cellspacing="0" width="100%" style="width: 500px;margin: 0 auto;">
                                <tr>
                                  <td align="left" valign="top" class="" style="padding: 0;position: relative;">
                                    <div class="ribbon ribbon-top-left green__ribbon"><span>{{ $GiftCard->title }}</span></div>
                                    <div class="gift-card__image" style="background-color: #444; height: 200px;width: 150px;border-top-left-radius: 10px;border-bottom-left-radius: 10px;"><img height="200" width="150" src="{{ $GiftCard->image['proxy_url'].'100/100'.$GiftCard->image['image_path'] }}"></div>
                                  </td>
                                  <td align="right" valign="top" class="textContent" style="padding: 5px 20px 0 0">
                                    <h1 style="color:#000;margin: 0; line-height:100%;font-family:Helvetica,Arial,sans-serif;font-size:50px;font-weight:600;margin-bottom:5px;text-align:right;">{{  $currSymbol. $GiftCard->amount }}</h1>
                                    <h6 style="text-align:right;font-weight:normal;font-family:Helvetica,Arial,sans-serif;margin-top:20px;font-size:14px;margin-bottom:10px;color:#C9BC20;line-height:105%;"></h6>
                                    <div style="text-align:center;font-family:Helvetica,Arial,sans-serif;font-size:14px;margin-bottom:0;color:#000;line-height:135%;background-color: #f5f5f5; padding: 10px;border: 1px solid #777;">{{ $GiftCard->userCode }}</div>
                                    <div style="overflow: hidden;text-overflow: ellipsis;white-space: nowrap;text-align:right;font-family:Helvetica,Arial,sans-serif;font-size:12px;margin-bottom:0;color:#000;line-height:100%; margin-top: 10px;margin-bottom: 10px;max-width: 300px;">{{ $GiftCard->short_desc }}</div>
                                    
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
  
  
            </table>
  
          </td>
        </tr>
      </table>
    </center>
  </body>
  
  </html>