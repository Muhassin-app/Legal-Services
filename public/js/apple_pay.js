var applePayButton = document.querySelector('#applepay-btn');


if (window.ApplePaySession) {
    var merchantIdentifier = 'merchant.com.app.sponge';
    var promise = ApplePaySession.canMakePaymentsWithActiveCard(merchantIdentifier);
    promise.then(function (canMakePayments) {
        if (canMakePayments){
            // Display Apple Pay Buttons here…
            applePayButton.style.display = 'block';
        }
    }); 
}

if(applePayButton){
    var pay = function(applePaymentToken, callback) {
        $.post('/pay', { "token" : applePaymentToken } , (response) =>{
            console.log("response", response);
            callback(response);
        });
    }

    var validateTheSession = function(validationURL , callback){

        // $.post('/merchantValidate', { "appleUrl" : validationURL }, function (response){
        //     callback(response);
        // });

        callback(merchantValidate(validationURL));
    }

    var merchantValidate = function(validationURL) {
        const { appleUrl } = validationURL;
        try{
            let httpsAgent = new https.Agent({
                rejectUnauthorized: false,
                // cert: await services.readFile(path.join(__dirname, '../certificates/certificate_sandbox.pem')),
                // key: await services.readFile(path.join(__dirname, '../certificates/certificate_sandbox.key')),
            });
            // let response = await axios.post(appleUrl, {
            //     merchantIdentifier: 'merchant.com.betakidzapp.test',
            //     domainName: 'sales.focushires.com',
            //     displayName: 'Sales',
            // },
            // {
            //     httpsAgent,
            // });
            // console.log("response.data", response.data)
            // return res.status(200).send(response.data);
        }catch(err){
            console.log(err)
        }
    }

    applePayButton.addEventListener("click", function(){
        var paymentRequest ={
            "countryCode": (countryCode == 'us' ) ? 'US' : 'IN',
            "currencyCode": 'USD',
            "supportedNetworks": ['visa', 'masterCard', 'amex', 'discover'],
            "merchantCapabilities": ['supports3DS'],
            "total": { label: 'TEST', amount:'1.00'},
        };

        const session = new ApplePaySession(6 , paymentRequest);

        session.begin();

        session.onvalidatemerchant = function(event){
            const validationURL = event.validationURL;
            console.log("validationURL", validationURL);
            validateTheSession(validationURL, function(merchantSession){
                console.log("merchantSession",merchantSession);
                session.completeMerchantValidation(merchantSession);
            });
        }

        session.onpaymentauthorized = function(event) {
            var applePaymentToken = event.payment.token;
            pay(applePaymentToken, function(outcome){
                if(outcome){
                    processPaymentCheckout(outcome.token , "token");
                    session.completePayment({"status": 0});
                }else{
                    session.completePayment({"status": 1});
                }

            });
        }
    });

    // router.post('/merchantValidate', async (req, res) => {
    // const { appleUrl } = req.body;
    // try{
    // let httpsAgent = new https.Agent({
    // rejectUnauthorized: false,
    // cert: await services.readFile(path.join(__dirname, '../certificates/certificate_sandbox.pem')),
    // key: await services.readFile(path.join(__dirname, '../certificates/certificate_sandbox.key')),
    // });

    // let response = await axios.post(
    // appleUrl,
    // {
    // merchantIdentifier: 'merchant.com.betakidzapp.test',
    // domainName: 'kidzapp.com',
    // displayName: 'Kidzapp',
    // },
    // {
    // httpsAgent,
    // }
    // );
    // console.log("response.data", response.data)
    // return res.status(200).send(response.data);
    // }catch(err){
    // console.log(err)
    // }
    // })
}