<?php

use App\Http\Controllers\BidController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\LiveePaymentController;
use App\Http\Controllers\MargController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoadieController;

Route::post('ajaxGetScheduleDateDetails', 'Front\CartController@ajaxGetScheduleDateDetails')->name('ajaxGetScheduleDateDetails');
Route::get('confirmation', 'Front\UserhomeController@confirmation')->name('confirmation');
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
Route::get('/sitemap.xml', 'HomeController@createSitmap')->name('sitemap.xml');
Route::get('auth/xero', 'Front\XeroController@index')->name('xero_auth');
Route::any('auth/callback/xero', 'Front\XeroController@xero_callback')->name('callback_xero');
Route::any('payment/paytab/callback', 'Front\PaytabController@callback')->name('payment.paytab.callback');
Route::match(['get', 'post'], 'payment/paytab/return', 'Front\PaytabController@returnBack')->name('payment.paytab.return');
Route::get('/sync-marg', [MargController::class, 'syncmarg'])->name('sync.marg');
Route::get('/order-marg', [MargController::class, 'makeInsertOrderMargApi']);
// Route::get('/margcmd', [MargController::class, 'margcmd'])->name('sync.marg');
Route::match(['get','post'],'payment/payByDataTrans','Front\DataTransController@payByDataTrans')->name('payment.payByDataTrans');
Route::get('/sync-marg', [MargController::class, 'syncmarg'])->name('sync.marg');
Route::get('/sync-marg/{vendor_id}', [MargController::class, 'syncmargVendor'])->name('sync.margVendor');
Route::get('/order-marg', [MargController::class, 'makeInsertOrderMargApi']);
Route::get('/debug-sentry', function () {
	echo \Hash::make('dispatcher@765');
	//throw new Exception('My first Sentry error!');
});

Route::group(['middleware' => ['domain']], function () {
	//easypay test
	Route::get('testpayment', 'Front\EasypaisaControllertest@testpayment')->name('testpayment');
	Route::get('response', 'Front\EasypaisaControllertest@response')->name('response_payment');
	Route::get('responseConf', 'Front\EasypaisaControllertest@responseConformation')->name('responseConformation');
	Route::any('webhook/quick-api', 'Front\QuickApiController@webhooks')->name('quick-api');
	Route::any('webhook/lalamove', 'Front\LalaMovesController@webhooks')->name('webhook');
	Route::any('webhook/ship-rocket', 'ShiprocketController@shiprocketWebhook')->name('webshiprocket');
	Route::any('webhook/dunzo', 'DunzoController@dunzoWebhook')->name('dunzoWebhook');
	Route::any('webhook/d4bdunzo','D4BDunzoController@d4bdunzoWebhook')->name('d4bdunzoWebhook');
	Route::any('webhook/ahoy', 'AhoyController@ahoyWebhook')->name('ahoyWebhook');
	Route::any('webhook/roadie', [RoadieController::class, 'roadieWebhook'])->name('roadieWebhook');
	Route::get('webhook/user_rating', 'Front\UserRatingController@userRatingWebhook')->name('user_rating_webhook');
    Route::any('livee/success','LiveePaymentController@afterPayment')->name('livee.payment');
    Route::any('webhook/success-page','Front\MpesaSafariController@successPage')->name('safari.payment');
    Route::any('webhook/borzoe', 'Client\BorzoeDeliveryController@borzoeWebhook')->name('borzoeWebhook');


	// order dispatcher order web hooks
	Route::get('dispatch-order-status-update/{id?}', 'Front\DispatcherController@dispatchOrderStatusUpdate')->name('dispatch-order-update'); // Order Status update Dispatch
	Route::get('dispatch-pickup-delivery/{id?}', 'Front\DispatcherController@dispatchPickupDeliveryUpdate')->name('dispatch-pickup-delivery'); // pickup delivery update from dispatch
	Route::get('dispatch-order-status-update-details/{id?}', 'Front\DispatcherController@dispatchOrderDetails')->name('dispatch-order-update-details'); // Order Status update Dispatch details
	Route::get('dispatch-order-product-update-details/{id?}', 'Front\DispatcherController@dispatchOrderProductDetails')->name('dispatch-order-product-update-details'); // Order Status update Dispatch details
	Route::get('dispatch-order-cancel-request/{id?}', 'Front\DispatcherController@dispatchOrderCancelRequest')->name('dispatch-order-cancel-request'); // Order Status update Dispatch details
	Route::post('dispatch/customer/distance/notification/{id?}', 'Front\DispatcherController@dispatchCustomerDetails')->name('dispatch-customer-details'); // send distance & co2 emission push notification from dispatch to customer

	Route::get('dispatch-order-product-status-update/{id?}', 'Front\DispatcherController@dispatchOrderSingleProductStatusUpdate')->name('dispatch-order-product-status-update'); // Order Status update Dispatch
	Route::get('dispatch-order-service-status-update/{id?}', 'Front\DispatcherController@dispatchOrderServiceProductStatusUpdate')->name('dispatch-order-service-status-update'); // Order Status update Dispatch
	Route::post('dispatch/driver/bids/update/{id?}', 'Front\DispatcherController@dispatchDriverBidUpdate')->name('dispatch-driver-bids'); // instant booking and Bid and Ride pickup delivery update from dispatch
	Route::post('dispatch/driver/bids/status/{id?}', 'Front\DispatcherController@dispatchDriverBidStatus')->name('dispatch-driver-bids-status'); // instant booking and Bid and Ride Bid Status pickup delivery update from dispatch

	//------routes for receive bids in bid and ride from agent (dispatcher)
	Route::post('dispatch/driver/bids/update/{id?}', 'Front\DispatcherController@dispatchDriverBidUpdate')->name('dispatch-driver-bids'); // instant booking / Bid and Ride pickup delivery update from dispatch
	Route::post('dispatch/driver/bids/status/{id?}', 'Front\DispatcherController@dispatchDriverBidStatus')->name('dispatch-driver-bids-status'); // instant booking / Bid and Ride Bid Status pickup delivery update from dispatch

	Route::match(['get', 'post'], 'square/inventory/event/update', 'Front\SquareInventoryController@squareInventoryEventUpdate')->name('square-inventory-event-update'); // webhook to receive inventory updates from square inventory update events

	Route::get('testsms', 'Front\FrontController@testsms');

	Route::get('demo', 'Front\CustomerAuthController@getTestHtmlPage');
	Route::get('cabbooking', 'Front\CustomerAuthController@getTestHtmlPage');
	Route::get('demo/cabBooking', 'Front\CustomerAuthController@getDemoCabBookingPage');
	Route::get('fcm', 'Front\CustomerAuthController@fcm');
	Route::get('send-notification', 'Front\CustomerAuthController@sendNotification');
    Route::get('vendor-notification', 'Front\DispatcherController@test');
	Route::get('test/email1', 'Front\FrontController@sendmailtest');

	// Start edit order routes
	Route::post('edit-order/search/vendor/products', 'Front\TempCartController@vendorProductsSearchResults');
	Route::match(['get', 'post'],'edit-order/search/Agent/products', 'Front\TempCartController@AgentProductsSearchResults');
	Route::match(['get', 'post'],'edit-order/vendor/products/getProductsInCart', 'Front\TempCartController@getProductsInCart');
	Route::post('edit-order/temp-cart/product/add', 'Front\TempCartController@postAddToTempCart');
	Route::post('edit-order/temp-cart/product/updateQuantity', 'Front\TempCartController@updateQuantity');
	Route::post('edit-order/temp-cart/product/detailWithAddons', 'Front\TempCartController@getCartProductDetailWithAddons');
	Route::post('edit-order/temp-cart/product/updateAddons', 'Front\TempCartController@updateProductAddonsAndQuantity');
	Route::post('edit-order/temp-cart/product/remove', 'Front\TempCartController@removeItem');
	Route::post('edit-order/temp-cart/remove', 'Front\TempCartController@emptyCartData');
	Route::post('edit-order/temp-cart/submit', 'Front\TempCartController@submitCart');
	Route::post('edit-order/vendor/product/{id}', 'Front\TempCartController@getProductById');
	// End edit order routes

	// Initial route for all type of gateways
	Route::post('post/payment/{gateway}', 'Front\PaymentController@postPayment')->name('payment.gateway.postPayment');
	Route::post('send/payment/otp/{gateway}', 'Front\PaymentController@sendPaymentOtp')->name('send.payment.otp');
	Route::post('verify/payment/otp/{gateway}', 'Front\PaymentController@verifyPaymentOtp')->name('verify.payment.otp');
	Route::post('verify/payment/otp/app/{gateway}', 'Front\PaymentController@verifyPaymentOtpApp')->name('verify.payment.otp.app');
	Route::post('verify/payment/otp/submit/{gateway}', 'Front\PaymentController@verifyPaymentOtpSubmit')->name('verify.payment.otp.submit');

	Route::get('payment/gateway/returnResponse', 'Front\PaymentController@getGatewayReturnResponse')->name('payment.gateway.return.response');

	//lalMoves Test Route
	Route::match(['get', 'post'], 'order/lalamoves/quotation', 'Front\LalaMovesController@quotation')->name('order.lalamoves.quotation');
	Route::match(['get','post'],'order/d4bdunzo/quotation','Front\D4BDunzoController@quotation')->name('order.d4bdunzo.quotation');

	Route::match(['get', 'post'], 'order/lalamoves/place-order', 'Front\LalaMovesController@placeOrder')->name('order.lalamoves.place_order');


	Route::match(['get','post'],'payment/payByCompany','Front\PayCompanyController@payByCompany')->name('payment.payByCompany');


	////check Shiprocket
	Route::get('carrier/test/shiprocket', 'ShiprocketController@checkShiprocket')->name('carrier.test.shiprocket');


	// Stripe
	Route::post('/check_stripe_security', 'Front\StripeGatewayController@checkStripeSecurity')->name('check_stripe_security');
	Route::post('payment/stripe', 'Front\StripeGatewayController@postPaymentViaStripe')->name('payment.stripe');
	Route::post('user/subscription/payment/stripe', 'Front\StripeGatewayController@subscriptionPaymentViaStripe')->name('user.subscription.payment.stripe');
	Route::get('/check_stripe_return_data', 'Front\StripeGatewayController@checkStripeReturnDataFrom3DAuth')->name('check_stripe_return_data');
	Route::get('/success-page', 'Front\DataTransController@successPage')->name('order.dataTransuccessPage');
	Route::get('/cancel-page', 'Front\DataTransController@cancelPage')->name('order.dataTransCancel');
	Route::post('/payment/payment_init', 'Front\StripeGatewayController@paymentInit')->name('payment_init');
	Route::post('payment/webhook/stripe', 'Front\StripeGatewayController@stripeWebhook')->name('payment.webhook.stripe')->middleware('stripeWebhookVerify');

	// Stripe FPX
	Route::post('payment/create/stripe_fpx', 'Front\StripeGatewayController@createStripeFPXPaymentIntent')->name('payment.create.stripe_fpx');
	Route::get('payment/retrieve/stripe_fpx', 'Front\StripeGatewayController@retrieveStripeFPXPaymentIntent')->name('payment.retrieve.stripe_fpx');
	Route::post('payment/webhook/stripe_fpx', 'Front\StripeGatewayController@stripeFPXWebhook')->name('payment.webhook.stripe_fpx');
	Route::get('payment/webview/stripe_fpx', 'Front\StripeGatewayController@paymentWebViewStripeFPX')->name('payment.webview.stripe_fpx');
	Route::get('payment/webview/response/stripe_fpx', 'Front\StripeGatewayController@webViewResponseStripeFPX')->name('payment.webview.response.stripe_fpx');


	// Stripe OXXO
	Route::post('payment/create/stripe_oxxo', 'Front\StripeGatewayController@createStripeOXXOPaymentIntent')->name('payment.create.stripe_oxxo');
	Route::get('payment/stripe_oxxo/clear', 'Front\StripeGatewayController@cartStripeOXXOClear')->name('payment.stripe_oxxo_clear');
	Route::post('payment/webhook/stripe_oxxo', 'Front\StripeGatewayController@stripeOXXOWebhook')->name('payment.webhook.stripe_oxxo');
	Route::get('payment/webview/stripe_oxxo', 'Front\StripeGatewayController@paymentWebViewStripeOXXO')->name('payment.webview.stripe_oxxo');
	Route::get('payment/webview/response/stripe_oxxo', 'Front\StripeGatewayController@webViewResponseStripeOXXO')->name('payment.webview.response.stripe_oxxo');


	// Stripe OXXO
	Route::post('payment/create/stripe_ideal', 'Front\StripeGatewayController@createStripeIdealPaymentIntent')->name('payment.create.stripe_ideal');
	Route::get('payment/retrieve/stripe_ideal', 'Front\StripeGatewayController@retrieveStripeIdealPaymentIntent')->name('payment.retrieve.stripe_ideal');
	Route::post('payment/webhook/stripe_ideal', 'Front\StripeGatewayController@stripeIdealWebhook')->name('payment.webhook.stripe_ideal');
	Route::get('payment/webview/stripe_ideal', 'Front\StripeGatewayController@paymentWebViewStripeIdeal')->name('payment.webview.stripe_ideal');
	Route::get('payment/webview/response/stripe_ideal', 'Front\StripeGatewayController@webViewResponseStripeIdeal')->name('payment.webview.response.stripe_ideal');


	// Paypal
	Route::post('payment/paypal', 'Front\PaypalGatewayController@paypalPurchase')->name('payment.paypalPurchase');
	Route::get('payment/paypal/CompletePurchase', 'Front\PaypalGatewayController@paypalCompletePurchase')->name('payment.paypalCompletePurchase');
	Route::post('payment/paypal-transaction/store', 'Front\PaypalGatewayController@paymentTransactionSave')->name('payment.paypal.transaction');
	# for App side paypal payment
	Route::get('payment/paypal/completeCheckout/{token?}/{action?}/{address?}', 'Front\PaymentController@paypalCompleteCheckout')->name('payment.paypalCompleteCheckout');
	Route::get('payment/checkoutSuccess/{id}', 'Front\PaymentController@getCheckoutSuccess')->name('payment.getCheckoutSuccess');

	// Paystack
	Route::post('payment/paystack', 'Front\PaystackGatewayController@paystackPurchase')->name('payment.paystackPurchase');
	Route::get('payment/paystack/completePurchase', 'Front\PaystackGatewayController@paystackCompletePurchase')->name('payment.paystackCompletePurchase');
	Route::get('payment/paystack/completePurchase/app', 'Front\PaystackGatewayController@paystackCompletePurchaseApp')->name('payment.paystackCompletePurchaseApp');
	Route::get('payment/paystack/cancelPurchase/app', 'Front\PaystackGatewayController@paystackCancelPurchaseApp')->name('payment.paystackCancelPurchaseApp');

	// Payfast
	Route::post('payment/payfast', 'Front\PayfastGatewayController@payfastPurchase')->name('payment.payfastPurchase');
	Route::post('payment/payfast/notify', 'Front\PayfastGatewayController@payfastNotify')->name('payment.payfastNotify');
	Route::post('payment/payfast/notify/app', 'Front\PayfastGatewayController@payfastNotifyApp')->name('payment.payfastNotifyApp');
	Route::post('payment/payfast/completePurchase', 'Front\PayfastGatewayController@payfastCompletePurchase')->name('payment.payfastCompletePurchase');

	// Mobbex
	Route::post('payment/mobbex', 'Front\MobbexGatewayController@mobbexPurchase')->name('payment.mobbexPurchase');
	Route::post('payment/mobbex/notify', 'Front\MobbexGatewayController@mobbexNotify')->name('payment.mobbexNotify');


	//icici payment routes
	Route::post('payment/gateway/icici', 'Front\IciciPaymentController@payByIcici')->name('payment.payByIcici');
	Route::post('payment/webhook/icici', 'Front\IciciPaymentController@successPage')->name('payment.icici.success');
	Route::post('payment/success/icici', 'Front\IciciPaymentController@successPage')->name('payment.icici.success');
	Route::post('payment/icici-transection-status', 'Front\IciciPaymentController@iciciTransactionStatus')->name('payment.iciciTransactionStatus');
	Route::post('payment/icici-success', 'Front\IciciPaymentController@iciciTransactionStatus')->name('transaction.icici.success');


	//Skip Cash

	Route::post('payment/skipcash', 'Front\SkipCashController@showSkipCashPage')->name('payment.skipcash');
	Route::get('success/skipcash', 'Front\SkipCashController@successPage')->name('payment.skipcash.success');
	Route::post('/skipcash/webhook', 'Front\SkipCashController@handleWebhook');

	Route::post('pesapal-payment', 'Front\PesapalPaymentController@payByPesapal')->name('pesapal.payment');
	Route::get('success/pesapal', 'Front\PesapalPaymentController@successPage')->name('payment.pesapal.success');

	// Route::post('payment/skipcashpay', 'Front\SkipCashController@checkPayment')->name('payment.skipcash.pay');

	Route::post('powertrans-payment', 'Front\PowerTransPaymentController@payByPowerTrans')->name('powertrans.payment');
	Route::get('success/powertrans', 'Front\PowerTransPaymentController@successPage')->name('payment.powertrans.success');

	//GCash
	Route::post('payment/gcash', 'Front\GCashController@beforePayment')->name('payment.gcash.beforePayment');
	Route::get('payment/gcash/view', 'Front\GCashController@webView')->name('payment.gcash.webView');

    //plugnpay
    Route::match(['get','post'],'payment/plugnpay','Front\PlugnpayController@beforePayment')->name('payment.plugnpay.beforePayment');

    //azulpay
    Route::match(['get','post'],'payment/nmi','Front\NmiPaymentController@beforePayment')->name('nmi.pay');

    //mpesasafari
    Route::match(['get','post'],'payment/mpesa','Front\MpesaSafariController@createPayment')->name('mpesasafari.pay');

    // obo-pay
    Route::post('before-payment/obo','Front\OboPaymentController@beforePayment')->name('obo.pay');
    Route::get('after-payment/obo','Front\OboPaymentController@afterPayment')->name('after.obo.payment');

    //livees
    Route::get('livees-pay',[LiveePaymentController::class,'livee']);
    Route::any('payment/livees/api', 'LiveePaymentController@payFormWeb')->name('livees.webview');
    Route::get('/livee','LiveePaymentController@index')->name('livee.pay');

	Route::post('checkVendorPincode','Front\PincodeController@checkVendorPincode')->name('pincode.checkVendorPincode');
	Route::get('getShippingMethod','Front\PincodeController@getShippingMethod')->name('pincode.getShippingMethod');

	//Simplify
	Route::match(['get', 'post'], 'payment/simplify/page', 'Front\SimplifyController@beforePayment')->name('payment.simplify.beforePayment');
	Route::post('payment/simplify', 'Front\SimplifyController@createPayment')->name('payment.simplify.createPayment');

	//azulpay
	Route::match(['get','post'],'payment/azulpay','Front\AzulPaymentController@beforePayment')->name('payment.azulpay.beforePayment');
	Route::match(['get','post'],'payment/get-cards','Front\AzulPaymentController@getUserCards')->name('payment.azulpay.getCards');
	Route::get('payment/get-user-cards','Front\AzulPaymentController@getCardList')->name('payment.user.cards');
	Route::get('payment/setDefaultCard/{id}','Front\AzulPaymentController@setDefaultCard')->name('setDefaultCard');
	Route::get('payment/deleteCard/{id}','Front\AzulPaymentController@deleteCard')->name('delete.azul.card');

	//Square
	Route::match(['get', 'post'], 'payment/square/page', 'Front\SquareController@beforePayment')->name('payment.square.beforePayment');
	Route::post('payment/square', 'Front\SquareController@createPayment')->name('payment.square.createPayment');

	//Braintree
	Route::match(['get', 'post'], 'payment/braintree/page', 'Front\BraintreeController@beforePayment')->name('payment.braintree.beforePayment');
	Route::post('payment/braintree', 'Front\BraintreeController@createPayment')->name('payment.braintree.createPayment');

	//Ozow
	Route::match(['get', 'post'], 'payment/ozow/page', 'Front\OzowController@beforePayment')->name('payment.ozow.beforePayment');
	Route::post('payment/ozow', 'Front\OzowController@createPayment')->name('payment.ozow.createPayment');

	//Pagarme
	Route::match(['get', 'post'], 'payment/pagarme/page', 'Front\PagarmeController@beforePayment')->name('payment.pagarme.beforePayment');
	Route::post('payment/pagarme', 'Front\PagarmeController@createPayment')->name('payment.pagarme.createPayment');
	Route::post('payment/pagarme/card', 'Front\PagarmeController@createPaymentCard')->name('payment.pagarme.createPaymentCard');

	//Authorize.Net
	Route::match(['get', 'post'], 'payment/authorize_net/page', 'Front\AuthorizeGatewayController@beforePayment')->name('payment.authorize.beforePayment');
	Route::post('payment/authorize', 'Front\AuthorizeGatewayController@createPayment')->name('payment.authorize.createPayment');

	//Paytab
	Route::match(['get', 'post'], 'payment/paytab/page', 'Front\PaytabController@beforePayment')->name('payment.paytab.beforePayment');
	Route::post('payment/paytab', 'Front\PaytabController@createPayment')->name('payment.paytab.createPayment');

	//UPay
	Route::match(['get', 'post'], 'payment/upay/page', 'Front\UPayController@beforePayment')->name('payment.upay.beforePayment');
	Route::match(['get', 'post'], 'payment/upay', 'Front\ConektaController@afterPayment')->name('payment.upay.afterPayment');
	//Conekta
	Route::match(['get', 'post'], 'payment/conekta/page', 'Front\ConektaController@beforePayment')->name('payment.conekta.beforePayment');
	Route::match(['get', 'post'], 'payment/conekta/{status}/{payment_from}/{come_from}/{amount}/{order_number?}', 'Front\ConektaController@afterPayment')->name('payment.conekta.afterPayment');
	//Telr
	Route::match(['get', 'post'], 'payment/telr/page', 'Front\TelrController@beforePayment')->name('payment.telr.beforePayment');
	Route::match(['get', 'post'], 'payment/telr/{status}/{payment_from}/{come_from}/{amount}/{order_number?}', 'Front\TelrController@afterPayment')->name('payment.telr.afterPayment');

	//Coinbase
	Route::match(['get', 'post'], 'payment/coinbase/page', 'Front\CoinbaseController@beforePayment')->name('payment.coinbase.beforePayment');
	Route::post('payment/coinbase', 'Front\CoinbaseController@createPayment')->name('payment.coinbase.createPayment');

	// toyyibpay
	Route::match(['get', 'post'], 'payment/toyyib', 'Front\ToyyibPayController@index')->name('payment.toyyibpay.index');
	//Route::post('payment/webhook/toyyib', 'Front\ToyyibPayController@webhook')->name('payment.webhook.toyyibpay');

	Route::post('payment/toyyib/callback', 'Front\ToyyibPayController@callback')->name('payment.toyyibpay.callback');

	Route::get('payment/toyyib/callback-success/{payment_form}', 'Front\ToyyibPayController@callbackSuccess')->name('payment.toyyibpay.callbackSuccess');

	// Checkout
	Route::post('payment/checkout', 'Front\CheckoutGatewayController@checkoutPurchase')->name('payment.checkoutPurchase');
	Route::post('payment/checkout/notify', 'Front\CheckoutGatewayController@checkoutNotify')->name('payment.checkoutNotify');

	//Passbase
	Route::get('passbase/page', 'Front\PassbaseController@index')->name('passbase.page');
	Route::get('passbase/store', 'Front\PassbaseController@storeAuthkey')->name('passbase.store');
	Route::any('passbase/webhook', 'Front\PassbaseController@webhook')->name('passbase.webhook');

     //totalpay
     Route::post('/make-payment','Front\TotalpayController@makePayment')->name('make.payment');
     Route::get('/success-totalpay', 'Front\TotalpayController@paymentSuccessTotalpay');
      //hitpay
    Route::post('/make-hitpay-payment', 'Front\HitpayController@makePayment')->name('make.hitpay.payment');
    Route::get('/success-hitpay', 'Front\HitpayController@responseAfterPayment')->name('success.hitpay');
    Route::any('payment/hitpay/webhook', 'Front\HitpayController@validateHitpayPayment')->name('hitpay.webhook');
	//thawani Payment Gateway
	Route::post('/pay-by-thawanipg', 'Front\ThawaniPaymentController@paybythawanipg')->name('pay-by-thawanipg');
    Route::get('/after-payment/{transaction_id}', 'Front\ThawaniPaymentController@afterpayment')->name('after.payment');

	//Cyberservice Pay Controller
	Route::get('cybersource/initiate-payment','Front\CyberSourcePaymentController@webPayment')->name('cybersource.initiate.payment');
	Route::get('cybersource/payment/api', 'Front\CyberSourcePaymentController@webMobilePayment')->name('cybersource.webMobileview');
	Route::any('cybersource/process-payment','Front\CyberSourcePaymentController@processPayment')->name('cybersource.processPayment');
	//Orange Pay Controller
	Route::get('orangepay/payment/api', 'Front\OrangePaymentController@webMobilePayment')->name('orangepay.webMobileview');
	Route::post('orangepay/initiate-payment','Front\OrangePaymentController@web_payment')->name('orangepay.initiate.payment');
	Route::any('success-orangepay', 'Front\OrangePaymentController@successPage')->name('success.orangepayment');
	Route::get('cancel-orangepay', 'Front\OrangePaymentController@cancelPage')->name('cancel.orangepayment');

    // Route::post('/pay-by-thawanipg', 'Api\v1\ThawaniPaymentController@paybythawanipg')->name('pay-by-thawanipg');
    // Route::get('/after-payment/{transaction_id}', 'Api\v1\ThawaniPaymentController@afterpayment')->name('after.payment');
	//Route::get('payment/yoco-webview', 'Api\v1\YocoGatewayController@yocoWebView')->name('payment.yoco-webview');
	Route::post('payment/yoco', 'Front\YocoGatewayController@yocoPurchase')->name('payment.yocoPurchase');

	//VivaWallet routes
	Route::match(['get', 'post'], 'payment/vivawallet/pay', 'Front\VivawalletController@createPayLink')->name('vivawallet.pay');


	Route::match(['get', 'post'], 'viva/result', 'Front\VivawalletController@successPage')->name('viva.success');
	Route::any('viva/webhook/success', 'Front\VivawalletController@verifyWebhookUrl')->name('viva.webhook');

	//ccavenue-pay
	Route::get('ccavenue/pay', 'Front\CcavenueController@payForm')->name('ccavenue.pay');
	Route::any('ccavenue/success', 'Front\CcavenueController@successForm')->name('ccavenue.success');
	Route::any('payment/ccavenue/api', 'Front\CcavenueController@payFormWebView')->name('ccavenue.webview');

	// EasypaisaController routes
	Route::get('easypaisa/pay', 'Front\EasypaisaController@create_token')->name('easypaisa.create.token');
	Route::any('easypaisa/success', 'Front\EasypaisaController@get_token_view_payment')->name('easypaisa.gettoken');

	//Mvodafone
	Route::post('payment/mvPay', 'Front\MvodafoneController@createPayLink')->name('mvodafone.pay');
	Route::get('payment/mvsuccess', 'Front\MvodafoneController@successPage')->name('mvodafone.success');

	//Flutterwave routes
	Route::post('payment/flutterwave', 'Front\FlutterWaveController@createHash')->name('flutterwave.createHash');
	Route::match(['get', 'post'], 'payment/flutter/success', 'Front\FlutterWaveController@successPage')->name('flutterwave.success');

	//Easypaisa routes
	Route::post('payment/easypaisa', 'Front\EasypaisaController@createHash')->name('easypaisa.createHash');
	Route::get('payment/easypaisa', 'Front\EasypaisaController@successPage')->name('easypaisa.success');

	//Windcave routes
	Route::post('payment/windcave', 'Front\WindcaveController@createHash')->name('windcave.createHash');
	Route::get('payment/windcave/success', 'Front\WindcaveController@successPage')->name('windcave.success');
	Route::get('payment/windcave/fail', 'Front\WindcaveController@failPage')->name('windcave.fail');

	//DPO routes
	Route::post('payment/dpo', 'Front\DpoController@createTocken')->name('dpo.createTocken');
	Route::get('payment/dpo/redirect', 'Front\DpoController@successPage')->name('dpo.redirect');
	Route::get('payment/dpo/success', 'Front\DpoController@successPage')->name('dpo.success');
	Route::get('payment/dpo/fail', 'Front\DpoController@failPage')->name('dpo.fail');

	//Paytech routes
	Route::post('payment/paytech', 'Front\PaytechController@createHash')->name('paytech.createHash');
	Route::get('payment/paytech/success', 'Front\PaytechController@successPage')->name('paytech.success');
	Route::get('payment/paytech/fail', 'Front\PaytechController@failPage')->name('paytech.fail');

	Route::post('payment/dpo/wallet', 'Front\DpoController@createTocken')->name('dpo.createTocken');
	Route::post('payment/dpo/subscription', 'Front\DpoController@createTocken')->name('dpo.subscription');

	//payPhone routes
	Route::post('payment/payphone', 'Front\PayphoneController@createHash')->name('payphone.createHash');
	Route::get('payment/payphone/success', 'Front\PayphoneController@successPage')->name('payphone.success');
	Route::any('payment/payphone/api/{url?}/{token?}', 'Front\PayphoneController@webViewPay')->name('payphone.webview');
	Route::any('payment/payphone/refundWalletAmount', 'Front\PayphoneController@refundWalletAmount')->name('payphone.refund');

	//KongaPay routes
	Route::post('payment/kongapay', 'Front\KongapayController@createHash')->name('kongapay.createHash');
	Route::any('payment/kongapay/api', 'Front\KongapayController@webViewPay')->name('kongapay.webview');
	Route::match(['get', 'post'], 'payment/kongapay/result/{from?}', 'Front\KongapayController@completeOrderCart')->name('kongapay.successCart');
	Route::match(['get', 'post'], 'payment/kongapay/walletResult', 'Front\KongapayController@completeOrderWallet')->name('kongapay.successWallet');
	Route::match(['get', 'post'], 'payment/kongapay/tipResult', 'Front\KongapayController@completeOrderTip')->name('kongapay.successTip');
	Route::match(['get', 'post'], 'payment/kongapay/subsResult', 'Front\KongapayController@completeOrderSubs')->name('kongapay.successSubs');


	Route::post('payment/yoco/app', 'Front\YocoGatewayController@yocoPurchaseApp')->name('payment.yocoPurchaseApp');
	Route::get('/payment/yoco-webview', function () {
		return View::make('frontend.yoco_webview');
	});

	//Khalti payment gateway
	Route::post('payment/khalti/verification', 'Front\KhaltiGatewayController@khaltiVerification')->name('payment.khaltiVerification');
	Route::post('payment/khalti/transactionstatus', 'Front\KhaltiGatewayController@khaltiCompletePurchase')->name('payment.khaltiCompletePurchase');
	Route::post('payment/khalti/completePurchase/app', 'Front\KhaltiGatewayController@khaltiCompletePurchaseApp')->name('payment.khaltiCompletePurchaseApp');
	Route::get('payment/webview/khalti', 'Front\KhaltiGatewayController@webView')->name('payment.khalti.webView');

	Route::post('payment/paylink', 'Front\PaylinkGatewayController@paylinkPurchase')->name('payment.paylinkPurchase');
	Route::get('payment/paylink/return', 'Front\PaylinkGatewayController@paylinkReturn')->name('payment.paylinkReturn');
	Route::get('payment/paylink/return/app', 'Front\PaylinkGatewayController@paylinkReturnApp')->name('payment.paylinkReturnApp');
	// Route::post('payment/paylink/notify', 'Front\PaylinkGatewayController@paylinkNotify')->name('payment.paylinkNotify');

	Route::post('payment/razorpay', 'Front\RazorpayGatewayController@razorpayPurchase')->name('payment.razorpayPurchase');

	Route::post('payment/razorpay/pay', 'Front\RazorpayGatewayController@razorpayCompletePurchase')->name('payment.razorpayCompletePurchase');
	Route::get('payment/razorpay/notify', 'Front\RazorpayGatewayController@razorpayNotify')->name('payment.razorpayNotify');
	Route::get('payment/razorpay/payout/notify', 'Front\RazorpayGatewayController@razorpayPayoutNotify')->name('payment.razorpay.payout.notify');

    Route::post('payment/mastercard/session-create', 'Front\MastercardPaymentController@createSession')->name('payment.mastercard.createSession');
    Route::get('payment/mastercard/return/{order_id}', 'Front\MastercardPaymentController@postPayment')->name('payment.mastercard.return');


	//Cashfree
	Route::get('payment/cashfree/return', 'Front\CashfreeGatewayController@cashfreeReturn')->name('payment.cashfree.return');
	Route::get('payment/cashfree/return/app', 'Front\CashfreeGatewayController@cashfreeReturnApp')->name('payment.cashfree.return.app');
	Route::post('payment/cashfree/notify', 'Front\CashfreeGatewayController@cashfreeNotify')->name('payment.cashfree.notify');

	// EasebuzzController payment test
	Route::get('/easebuzz-gateway', 'Front\EasebuzzController@easebuzz_gateway')->name('easebuzz-gateway');
	Route::post('payment/easebuzz/request', 'Front\EasebuzzController@order')->name('easebuzz.order');
	Route::match(['get', 'post'], 'easebuzz_respont', 'Front\EasebuzzController@easebuzz_respont')->name('easebuzz_respont');
	Route::any('payment/easebuzz/notify', 'Front\EasebuzzController@easybuzzNotify')->name('payment.easebuzz.easybuzzNotify');
	Route::any('payment/easebuzz/api', 'Front\EasebuzzController@easebuzz_respontAPP')->name('easebuzz.webview');

	// UseRedePaymentController payment test
	Route::match(['get', 'post'], 'payment/userede/page', 'Front\UseRedePaymentController@beforePayment')->name('payment.userede.beforePayment');
	Route::match(['get', 'post'], '/payment/userede/respons', 'Front\UseRedePaymentController@responsUs')->name('payment.userede.responsUs');
	Route::post('/payment/userede/payment_init', 'Front\UseRedePaymentController@paymentInit')->name('payment.userede.createPayment');
	Route::post('/payment/userede/payment_init_app', 'Front\UseRedePaymentController@paymentInitApp')->name('payment.userede.createPaymentApp');

	// OpenpayPaymentController payment test
	Route::match(['get', 'post'], 'payment/opnepay/page', 'Front\OpenpayPaymentController@beforePayment')->name('payment.opnepay.beforePayment');
	Route::post('/payment/opnepay/payment_init', 'Front\OpenpayPaymentController@paymentInit')->name('payment.opnepay.createPayment');
	Route::post('/payment/opnepay/payment_init_app', 'Front\OpenpayPaymentController@paymentInitApp')->name('payment.opnepay.createPaymentApp');
	Route::match(['get', 'post'], 'payment/webhook/opnepay', 'Front\OpenpayPaymentController@opnepayWebhook')->name('payment.webhook.opnepay');
	// test VNPAY payment gateway
	Route::get('/vnpay-gateway', 'Front\VnpayController@VnPay_gateway')->name('vnpay-gateway');
	Route::post('payment/vnpay/request', 'Front\VnpayController@order')->name('vnpay.order');
	Route::match(['get', 'post'], 'vnpay_respont', 'Front\VnpayController@vnpay_respont')->name('vnpay_respont');
	Route::any('payment/vnpay/notify', 'Front\VnpayController@VnpayNotify')->name('payment.vnpay.VnpayNotify');
	Route::any('payment/vnpay/api', 'Front\VnpayController@vnpay_respontAPP')->name('vnpay_respont_app');

	Route::post('payment/user/placeorder', 'Front\OrderController@postPaymentPlaceOrder')->name('user.postPaymentPlaceOrder');
	Route::post('payment/user/wallet/credit', 'Front\WalletController@postPaymentCreditWallet')->name('user.postPaymentCreditWallet');
    // Mtn Momo payment gateway

	Route::any('payment/webhook/mtn', 'Front\MtnMomoController@mtnCallback')->name('payment.webhook.mtn');

	Route::group(['prefix' => 'mtn'], function () {
		Route::post('payment', 'Front\MtnMomoController@createToken')->name('mtn.momo.createToken');
		Route::get('response/{id?}', 'Front\MtnMomoController@getResponse')->name('payment.response.mtn');
	});

	// Mtn Momo payment gateway

	// Route::get('payment/dpo/redirect', 'Front\DpoController@successPage')->name('dpo.redirect');
	// Route::get('payment/dpo/success', 'Front\DpoController@successPage')->name('dpo.success');
	// Route::get('payment/dpo/fail', 'Front\DpoController@failPage')->name('dpo.fail');

	Route::get('user/login', [
		'as' => 'customer.login',
		'uses' => 'Front\CustomerAuthController@loginForm'
	]);
	Route::get('user/register', [
		'as' => 'customer.register',
		'uses' => 'Front\CustomerAuthController@registerForm'
	]);
	Route::get('user/forgotPassword', [
		'as' => 'customer.forgotPassword',
		'uses' => 'Front\ForgotPasswordController@getForgotPasswordForm'
	]);
	Route::get('user/resetPassword', [
		'as' => 'customer.resetPassword',
		'uses' => 'Front\CustomerAuthController@resetPasswordForm'
	]);
	Route::get('/autocomplete-search', 'Front\SearchController@postAutocompleteSearch')->name('autocomplete');
	Route::get('/search-all/{keyword}', 'Front\SearchController@showSearchResults')->name('showSearchResults');
	Route::get('/', 'Front\UserhomeController@index')->name('userHome');

	// Route::get('/', 'Front\YachtController@yacht')->name('userHome');
	Route::any('products-searchResults', 'Front\YachtController@productsSearchResult')->name('productSearch');
	Route::get('/setSessionIndex', 'Front\UserhomeController@setSessionIndex')->name('setSessionIndex');

	Route::get('/updateLocation', 'Front\UserhomeController@setHyperlocalAddress')->name('updateLocation');
	Route::get('/ondemandPricing', 'Front\UserhomeController@setondemandPricingSession')->name('updateLocation');
	Route::get('/homeTemplateOne', 'Front\UserhomeController@indexTemplateOne')->name('indexTemplateOne');
	//Route::get('page/driver-registration', 'Front\UserhomeController@driverSignup')->name('page/driver-registration');
	Route::post('page/driverSignup', 'Front\OrderController@driverSignup')->name('page.driverSignup');
	Route::get('driver-documents', 'Front\UserhomeController@driverDocuments')->name('driver-documents');
	Route::get('page/{slug}', 'Front\UserhomeController@getExtraPage')->name('extrapage');

	Route::post('/homePageData', 'Front\UserhomeController@postHomePageData')->name('homePageData');
	Route::post('/postHomePageDataSingle', 'Front\UserhomeController@postHomePageDataSingle')->name('postHomePageDataSingle');
	Route::post('/postHomePageDataBanners', 'Front\UserhomeController@postHomePageDataBanners')->name('postHomePageDataBanners');
	Route::post('/homePageDataNew', 'Front\UserhomeController@postHomePageDataNew')->name('homePageDataNew');
	Route::post('/homePageDataCategoryMenu', 'Front\UserhomeController@homePageDataCategoryMenu')->name('homePageDataCategoryMenu');
	Route::post('/theme', 'Front\UserhomeController@setTheme')->name('config.update');
	Route::get('/getConfig', 'Front\UserhomeController@getConfig')->name('config.get');
	Route::post('getClientPreferences', 'Front\UserhomeController@getClientPreferences')->name('getClientPreferences');
	Route::post('validateEmail', 'Front\CustomerAuthController@validateEmail')->name('validateEmail');
	Route::post('user/loginData', 'Front\CustomerAuthController@login')->name('customer.loginData');
	Route::post('user/register', 'Front\CustomerAuthController@register')->name('customer.register');
	Route::post('user/loginViaUsername', 'Front\CustomerAuthController@loginViaUsername')->name('customer.loginViaUsername');
	Route::post('user/check-valid-email', 'Front\CustomerAuthController@checkValidEmail')->name('check-valid-email');

	Route::post('user/verifyPhoneLoginOtp', 'Front\CustomerAuthController@verifyPhoneLoginOtp')->name('customer.verifyPhoneLoginOtp');
	Route::post('vendor/register', 'Front\CustomerAuthController@postVendorregister')->name('vendor.register');
	Route::post('user/forgotPassword', 'Front\ForgotPasswordController@postForgotPassword')->name('customer.forgotPass');
	Route::post('user/resetPassword', 'Front\CustomerAuthController@resetPassword')->name('customer.resetPass');
	Route::get('reset-password/{token}', 'Front\ForgotPasswordController@getResetPasswordForm');
	Route::post('reset-password', 'Front\ForgotPasswordController@postUpdateResetPassword')->name('reset-password');

	Route::post('primaryData', 'Front\UserhomeController@changePrimaryData')->name('changePrimaryData');
	Route::post('paginateValue', 'Front\UserhomeController@changePaginate')->name('changePaginate');
	Route::get('{vendor?}/product/{id?}', 'Front\ProductController@index')->name('productDetail');
	Route::post('/product/variant/{id}', 'Front\ProductController@getVariantData')->name('productVariant');
	Route::post('product/compare', 'Front\ProductController@getProductCompare')->name('compare.product');
	Route::get('product/faq/{id}', 'Front\ProductController@getProductFaq')->name('getProductFaq');
	Route::post('cart/product/lastAdded', 'Front\CartController@getLastAddedProductVariant')->name('getLastAddedProductVariant');
	Route::post('cart/product/variant/different-addons', 'Front\CartController@getProductVariantWithDifferentAddons')->name('getProductVariantWithDifferentAddons');
	Route::post('add/product/cart', 'Front\CartController@postAddToCart')->name('addToCart');
	Route::post('post/estimate/cart/request', 'Front\CartController@postCartRequestFromEstimation')->name('postCartRequestFromEstimation');
	Route::post('add/estimate/product/cart', 'Front\EstimationController@addToEstimateCart')->name('addToEstimateCart');
	Route::post('remove/estimate/product/cart', 'Front\EstimationController@destroy')->name('removeEstimateCartProduct');
	Route::post('add/product/cart-addons', 'Front\CartController@postAddToCartAddons')->name('addToCartAddons');
	Route::post('add/wishlist/cart', 'Front\CartController@addWishlistToCart')->name('addWishlistToCart');
	Route::post('add/vendorTable/cart', 'Front\CartController@addVendorTableToCart')->name('addVendorTableToCart');
	Route::post('add/product/prescription', 'Front\CartController@uploadPrescription')->name('cart.uploadPrescription');
	Route::post('cart/schedule/update', 'Front\CartController@updateSchedule')->name('cart.updateSchedule');
	Route::post('cart/productfaq/update', 'Front\CartController@updateCartProductFaq')->name('cart.productfaq');
	Route::post('cart/schedule/slots', 'Front\CartController@checkScheduleSlots')->name('cart.check_schedule_slots');

	Route::post('cart/pickup/schedule/slots', 'Front\CartController@checkPickupScheduleSlots')->name('cart.check_pickup_schedule_slots'); // Added by Ovi
	Route::post('cart/dropoff/schedule/slots', 'Front\CartController@checkDropoffScheduleSlots')->name('cart.check_dropoff_schedule_slots'); // Added by Ovi

	Route::post('cart/product-schedule/update', 'Front\CartController@updateProductSchedule')->name('cart.updateProductSchedule');
	Route::post('cart/product-schedule/dispatch_agent_update', 'Front\CartController@updateDispatcherAgent')->name('cart.updateDispatcherAgent');
	Route::get('cartProducts', 'Front\CartController@getCartData')->name('getCartProducts');
	Route::get('cartDetails', 'Front\CartController@getCartProducts')->name('cartDetails');
	Route::post('get/product/prescription', 'Front\CartController@getProductPrescription')->name('getProductPrescription');
	Route::post('cartDelete', 'Front\CartController@emptyCartData')->name('emptyCartData');
	Route::post('repeatOrder', 'Front\CartController@repeatOrder')->name('web.repeatOrder');
	Route::post('/product/updateCartQuantity', 'Front\CartController@updateQuantity')->name('updateQuantity');
	Route::post('/product/updateCartProductStatus', 'Front\CartController@updateCartProductStatus')->name('updateCartProductStatus');
	Route::post('/product/deletecartproduct', 'Front\CartController@deleteCartProduct')->name('deleteCartProduct');
	Route::get('userAddress', 'Front\UserController@getUserAddress')->name('getUserAddress');


	//Route For company
	Route::get('company/{id}', 'Front\CategoryController@companyCategoryProduct')->name('companyWiseCategoryDetail');



	Route::get('category/{slug?}', 'Front\CategoryController@categoryProduct')->name('categoryDetail');
	Route::post('get-rental-view', 'Front\CategoryController@getRentalView')->name('get-rental-view');



	Route::get('category/{slug?}/{slug1?}', 'Front\CategoryController@categoryProduct')->name('categoryDetail');
	Route::get('category/{slug1}/{slug2}', 'Front\CategoryController@categoryVendorProducts')->name('categoryVendorProducts');
	Route::post('category/filters/{id}', 'Front\CategoryController@categoryFilters')->name('productFilters');
	Route::get('category_kycDocument', 'Front\CategoryController@getcategoryKycDocument')->name('getCategoryKycDocument');
	Route::get('vendor/all', 'Front\VendorController@viewAll')->name('vendor.all');
	Route::match(['get', 'post'], 'vendor/{id?}', 'Front\VendorController@vendorProducts')->name('vendorDetail');
	Route::get('vendor/{slug1}/{slug2}', 'Front\VendorController@vendorCategoryProducts')->name('vendorCategoryProducts');
	Route::post('vendor/filters/{id}', 'Front\VendorController@vendorFilters')->name('vendorProductFilters');
	Route::post('vendor/products/searchResults', 'Front\VendorController@vendorProductsSearchResults')->name('vendorProductsSearchResults');
	Route::post('search/estimated/products', 'Front\EstimationController@searchEstimatedProducts')->name('searchEstimatedProducts'); // Added By Ovi
	Route::post('vendor/product/addons', 'Front\VendorController@vendorProductAddons')->name('vendorProductAddons');
	Route::post('estimate/product/addons', 'Front\EstimationController@estimateProductAddons')->name('estimateProductAddons');
	Route::get('get-estimation', 'Front\EstimationController@index'); // Added by Ovi
	Route::get('estimation-list', 'Front\EstimationController@estimationList')->name('estimationList'); // Added by Ovi
	Route::get('brand/{id?}', 'Front\BrandController@brandProducts')->name('brandDetail');
	Route::post('brand/filters/{id}', 'Front\BrandController@brandFilters')->name('brandProductFilters');
	Route::get('brands/all', 'Front\BrandController@viewAll')->name('brand.all');
	Route::get('celebrity/{slug?}', 'Front\CelebrityController@celebrityProducts')->name('celebrityProducts');
	Route::get('auth/{driver}', 'Front\FacebookController@redirectToSocial');
	Route::get('auth/callback/{driver}', 'Front\FacebookController@handleSocialCallback');
	Route::get('UserCheck', 'Front\UserController@checkUserLogin')->name('checkUserLogin');
	Route::get('stripe/showForm/{token}', 'Front\PaymentController@showFormApp')->name('stripe.formApp');
	Route::post('stripe/make', 'Front\PaymentController@makePayment')->name('stripe.makePayment');
	Route::post('inquiryMode/store', 'Front\ProductInquiryController@store')->name('inquiryMode.store');
	Route::get('viewcart', 'Front\CartController@showCart')->name('showCart');
	Route::get('checkSlotOrders', 'Front\CartController@checkSlotOrders')->name('checkSlotOrders'); //Added by Ovi
	Route::post('/getTimeSlotsForOndemand', 'Front\CategoryController@getTimeSlotsForOndemand')->name('getTimeSlotsForOndemand');
	Route::post('checkIsolateSingleVendor', 'Front\CartController@checkIsolateSingleVendor')->name('checkIsolateSingleVendor');
	Route::get('category-products/{cat_id}/{vendor_id}', 'Front\VendorController@vendorAllProducts')->name('products');

	Route::post('/updateCartSlot', 'Front\CartController@updateCartSlot')->name('updateCartSlot');

	Route::post('/updateCartBookingSlot', 'Front\CartController@updateCartBookingSlot')->name('updateCartBookingSlot');

	Route::get('getShippingProductDeliverySlots', 'Front\ProductController@getShippingProductDeliverySlots')->name('product.getShippingProductDeliverySlots');

	Route::get('getShippingSlotsInterval', 'Front\ProductController@getShippingSlotsInterval')->name('product.getShippingSlotsInterval');

	Route::post('/getTimeSlotsForOndemand', 'Front\CategoryController@getTimeSlotsForOndemand')->name('getTimeSlotsForOndemand');
	Route::post('checkIsolateSingleVendor', 'Front\CartController@checkIsolateSingleVendor')->name('checkIsolateSingleVendor');
	Route::get('firebase-messaging-sw.js', 'Front\FirebaseController@service_worker');
	Route::post('category_kyc_submit', 'Front\CartController@updateCartCategoryKyc')->name('updateCartCategoryKyc');
	//User Rider Routes
	Route::post('rider/add', 'Front\RiderController@addRider')->name('rider.create');
	Route::get('rider/delete', 'Front\RiderController@removeRider')->name('rider.remove');

	//cities
	Route::get('cities/{slug}', 'Front\VendorCitiesController@getCities')->name('city.getCities');

	Route::post('getSlotFromDispatchDemand', 'Front\FrontController@getSlotFromDispatchDemand')->name('getSlotFromDispatchDemand');
	//chatNotification to all users from dispacther
	Route::any('sendNotificationToUserByDispatcher', 'Front\ChatDispatcherNotificationController@sendNotificationToUserByDispatcher')->name('sendNotificationToUserByDispatcher'); // Order Status update Dispatch

    // get recurring booking vendor time slots
    Route::post('vendor-time-slot', 'Front\CartController@VendorTimeSlot')->name('recurring.booking.vendor.slot');
	Route::post('get_price_from_dispatcher', 'Front\ProductController@getFreeLincerFromDispatcher')->name('product.get_price_from_dispatcher');
	Route::post('get_gerenal_slot', 'Front\ProductController@getGerenalSlot')->name('getGerenalSlot');
	Route::post('shipEngine-webhook', 'Front\ShipEngineController@webhook');
});
Route::group(['middleware' => ['domain', 'webAuth']], function () {
	 
	 
	Route::get('user/orders', 'Front\OrderController@orders')->name('user.orders');
	Route::get('user/order/status/detail/{order_id}', 'Front\OrderController@orderStatusDetail')->name('user.order.status.detail');
	Route::get('user/lander-orders', 'Front\OrderController@lenderOrders')->name('user.lander-orders');
	Route::get('user/borrower-orders', 'Front\OrderController@borrowerOrders')->name('user.borrower-orders');
	Route::post('user/orderVenderStatusUpdate', 'Front\OrderController@orderVenderStatusUpdate')->name('user.orderVenderStatusUpdate');

	Route::get('user/rental-orders', 'Front\RentalOrderController@rentalOrders')->name('user.rental-orders');
	Route::post('user/orders/tip-after-order', 'Front\OrderController@tipAfterOrder')->name('user.tip_after_order');
	Route::post('user/store', 'Front\AddressController@store')->name('address.store');
	Route::get('user/addAddress', 'Front\AddressController@add')->name('addNewAddress');
	Route::get('user/address/{id}', 'Front\AddressController@address')->name('user.address');
	Route::get('user/checkout', 'Front\UserController@checkout')->name('user.checkout');
	Route::get('user/profile', 'Front\ProfileController@profile')->name('user.profile');
	Route::get('user/my-ads', 'Front\ProfileController@getMyAds')->name('user.productList');
	Route::post('user/update-post-status', 'Front\ProfileController@updatePostStatus')->name('user.updatePostStatus');
	Route::get('user/notification', 'Front\ProfileController@getNotification')->name('user.notification');
	Route::get('user/allergic-items', 'Front\AllergicItemController@index')->name('list.allergicItems');
	Route::post('user/add-allergic-items', 'Front\AllergicItemController@addUpdateAllergicItems')->name('add.allergicItems');
	Route::get('user/removeItem/{id}', 'Front\AllergicItemController@destroy')->name('removeItem');
	Route::get('user/logout', 'Front\CustomerAuthController@logout')->name('user.logout');
	Route::get('verifyAccountProcess', 'Front\UserController@sendToken')->name('email.send');
	Route::get('user/editAddress/{id}', 'Front\AddressController@edit')->name('editAddress');
	Route::post('user/update/{id?}', 'Front\AddressController@update')->name('address.update');
	Route::get('user/wishlists', 'Front\WishlistController@wishlists')->name('user.wishlists');
	Route::post('verifyAccountProcess', 'Front\UserController@sendToken')->name('email.send');
	Route::post('sendToken/{id}', 'Front\UserController@sendToken')->name('verifyInformation');
	Route::post('user/placeorder', 'Front\OrderController@placeOrder')->name('user.placeorder');
	Route::post('user/rescheduleOrder', 'Front\OrderController@rescheduleOrder')->name('user.rescheduleOrder'); // Added by Ovi
	Route::get('user/newsLetter', 'Front\ProfileController@newsLetter')->name('user.newsLetter');
	Route::get('user/verify_account', 'Front\UserController@verifyAccount')->name('user.verify');
	Route::post('wishlist/update', 'Front\WishlistController@updateWishlist')->name('addWishlist');
	Route::post('verifTokenProcess', 'Front\UserController@verifyToken')->name('user.verifyToken');
	Route::get('user/addressBook', 'Front\AddressController@index')->name('user.addressBook');
	Route::get('user/wallet', 'Front\WalletController@index')->name('user.wallet');
	Route::get('user/wallet/refreshBalance/{id?}', 'Front\WalletController@refreshWalletbalance')->name('user.wallet.refreshBalance');
	Route::post('user/wallet/credit', 'Front\WalletController@creditWallet')->name('user.creditWallet');
	Route::post('wallet/transfer/user/verify', 'Front\WalletController@walletTransferUserVerify')->name('wallet.transfer.user.verify');
	Route::post('wallet/transfer/confirm', 'Front\WalletController@walletTransferConfirm')->name('wallet.transfer.confirm');
	Route::get('user/loyalty', 'Front\LoyaltyController@index')->name('user.loyalty');
	Route::post('wallet/payment/option/list', 'Front\WalletController@paymentOptions')->name('wallet.payment.option.list');
	Route::get('wallet/addMoney', 'Front\WalletController@addWalletAmount');
	Route::get('user/deleteAddress/{id}', 'Front\AddressController@delete')->name('deleteAddress');
	Route::post('user/updateAccount', 'Front\ProfileController@updateAccount')->name('user.updateAccount');
	Route::post('user/updateTimezone', 'Front\ProfileController@updateTimezone')->name('user.updateTimezone');
	Route::get('user/editAccount', 'Front\ProfileController@editAccount')->name('user.editAccount');
	Route::get('user/sendRefferal', 'Front\ProfileController@showRefferal')->name('user.sendRefferal');
	Route::get('wishlist/remove/{sku}', 'Front\WishlistController@removeWishlist')->name('removeWishlist');
	Route::get('user/changePassword', 'Front\ProfileController@changePassword')->name('user.changePassword');
	Route::post('user/placeorder/make', 'Front\OrderController@makePayment')->name('placeorder.makePayment');
	Route::post('user/sendRefferalCode', 'Front\ProfileController@sendRefferalCode')->name('user.sendEmail');
	Route::get('user/resetSuccess', 'Front\CustomerAuthController@resetSuccess')->name('customer.resetSuccess');
	Route::post('verify/promocode', 'Front\PromoCodeController@postVerifyPromoCode')->name('verify.promocode');
	Route::post('remove/promocode', 'Front\PromoCodeController@postRemovePromoCode')->name('remove.promocode');
	Route::get('order/success/{order_id}', 'Front\OrderController@getOrderSuccessPage')->name('order.success');
	Route::get('order/return/success', 'Front\OrderController@getOrderSuccessReturnPage')->name('order.return.success');

	Route::post('user/editorder', 'Front\OrderController@editOrderByUser')->name('user.editorder');
	Route::post('user/discardeditorder', 'Front\OrderController@discardEditOrderByUser')->name('user.discardeditorder');

	Route::post('promocode/list', 'Front\PromoCodeController@postPromoCodeList')->name('verify.promocode.list');
	Route::post('promocode/validate_code', 'Front\PromoCodeController@validate_code')->name('verify.promocode.validate_code');
	Route::post('payment/option/list', 'Front\PaymentController@index')->name('payment.option.list');
	Route::get('user/setPrimaryAddress/{id}', 'Front\AddressController@setPrimaryAddress')->name('setPrimaryAddress');
	Route::post('user/submitPassword', 'Front\ProfileController@submitChangePassword')->name('user.submitChangePassword');
	Route::get('user/wallet/history', 'Front\WalletController@index')->name('user.walletHistory');
	Route::get('user/subscription/plans', 'Front\UserSubscriptionController@getSubscriptionPlans')->name('user.subscription.plans');
	Route::get('user/subscription/select/{slug}', 'Front\UserSubscriptionController@selectSubscriptionPlan')->name('user.subscription.plan.select');
	Route::post('user/subscription/purchase/{slug}', 'Front\UserSubscriptionController@purchaseSubscriptionPlan')->name('user.subscription.plan.purchase');
	Route::post('user/subscription/cancel/{slug}', 'Front\UserSubscriptionController@cancelSubscriptionPlan')->name('user.subscription.plan.cancel');
	Route::get('user/subscription/checkActive/{slug}', 'Front\UserSubscriptionController@checkActiveSubscription')->name('user.subscription.plan.checkActive');
	Route::get('user/mealSubscription/{slug}', 'Front\UserSubscriptionController@mealSubscription')->name('user.mealSubscription');
	Route::get('user/subscription-credit', 'Front\UserSubscriptionController@subscriptionCredit')->name('user.mealSubscription.credit');

	// Refer and Earn Module
	Route::name('refer-earn.')->group(function () {
		Route::get('user/refer-earn', 'Front\InfluencerReferAndEarnController@index')->name('index');
		Route::get('user/get-refer-earn-form/{id}', 'Front\InfluencerReferAndEarnController@getReferEarnForm')->name('form');
		Route::post('user/save-refer-earn-form', 'Front\InfluencerReferAndEarnController@save')->name('save');
		Route::post('user/update-refer-code', 'Front\InfluencerReferAndEarnController@updateRefferalCode')->name('updateRefferalCode');
	});
	Route::post('user/save_fcm_token', 'Front\ProfileController@save_fcm')->name('user.save_fcm');
	// Rating & review
	Route::group(['prefix' => 'rating'], function () {
		Route::post('update-product-rating', 'Front\RatingController@updateProductRating')->name('update.order.rating');
		Route::get('get-product-rating', 'Front\RatingController@getProductRating')->name('get-product-rating-details');

		Route::post('update-driver-rating', 'Front\RatingController@updateDriverRating')->name('update.driver.rating');
		Route::get('get-driver-rating', 'Front\RatingController@getDriverRating')->name('get-driver-rating-details');
		Route::post('driver-agent-rating', 'Api\v1\RatingController@driverAgentRating')->name('driver-agent-rating');
	});
	// Return product
	Route::group(['prefix' => 'return-order'], function () {
		Route::get('get-order-data-in-model', 'Front\ReturnOrderController@getOrderDatainModel')->name('getOrderDatainModel');
		Route::get('get-replace-order-data-in-model', 'Front\ReturnOrderController@getReplaceOrderDatailModel')->name('getReplaceOrderDatailModel');
		Route::get('get-return-products', 'Front\ReturnOrderController@getReturnProducts')->name('get-return-products');
		Route::get('get-replace-products', 'Front\ReturnOrderController@getReplaceProducts')->name('get-replace-products');
		Route::post('update-product-return', 'Front\ReturnOrderController@updateProductReturn')->name('update.order.return');
		Route::post('update-product-replace', 'Front\ReturnOrderController@updateProductReplace')->name('update.order.replace');

		Route::get('get-vendor-order-for-cancel', 'Front\ReturnOrderController@getVendorOrderForCancel')->name('get-vendor-order-for-cancel');
		Route::post('vendor-order-for-cancel', 'Front\ReturnOrderController@vendorOrderForCancelSingleProduct')->name('order.cancel.customer');

		Route::post('vendor-order-for-cancel-req', 'Front\ReturnOrderController@vendorOrderForCancelReq')->name('order.cancel.req.customer');

		Route::get('get-order-rental-data-in-model', 'Front\ReturnOrderController@getOrderRentalDatainModel')->name('getOrderRentalDataInModel');
		Route::post('update-rental-product-return', 'Front\ReturnOrderController@updateRentalProductReturn')->name('update.rental.product.return');

		Route::get('get-replace-order-data-in-model', 'Front\ReturnOrderController@getReplaceOrderDatailModel')->name('getReplaceOrderDatailModel');
		Route::get('get-replace-products', 'Front\ReturnOrderController@getReplaceProducts')->name('get-replace-products');
		Route::post('update-product-replace', 'Front\ReturnOrderController@updateProductReplace')->name('update.order.replace');

	});

	// Rental Extend Routes
	Route::group(['prefix' => 'extend-durartion'], function () {
		Route::get('get-order-vendor-product-duration-data-in-model', 'Front\ExtendOrderController@getOrderProductDurationDatainModel')->name('getOrderProductDurationDatainModel');
	});
	// Return product
	Route::group(['prefix' => 'looking'], function () {
		Route::get('/', 'Front\BookingController@index')->name('bookingIndex');
		Route::get('details/{id?}', 'Front\BookingController@bookingDetails')->name('front.booking.details');
		Route::post('orderPlaceDetails/{id}', 'Front\BookingController@orderPlaceDetails')->name('front.booking.orderplacedetails');
		Route::post('updateRentalPrice', 'Front\BookingController@updateRentalPrice')->name('front.booking.updateRentalPrice');

		Route::get('payment/options', 'Front\PickupDeliveryController@getPaymentOptions');
		Route::post('create-order', 'Front\PickupDeliveryController@createOrder');
		Route::post('cart/updateQuantity', 'Front\CartController@updateQuantity');
		Route::post('promo-code/list', 'Front\PickupDeliveryController@postPromoCodeList');
		Route::post('promo-code/remove', 'Front\PickupDeliveryController@postRemovePromoCode');
		Route::post('product-detail/{id}', 'Front\PickupDeliveryController@postCabProductById');
		Route::post('get-list-of-vehicles-old/{id}', 'Front\PickupDeliveryController@getListOfVehicles');
		Route::post('vendor/list/{category_id}', 'Front\PickupDeliveryController@postVendorListByCategoryId')->name('pickup-delivery-route');
		Route::post('get-list-of-vehicles/{vid}/{cid?}', 'Front\PickupDeliveryController@productsByVendorInPickupDelivery');
		Route::post('get-list-of-rental-vehicles', 'Front\PickupDeliveryController@productsByRentalVendorInPickupDelivery')->name('get-list-of-rental-vehicles');
		Route::post('order-tracking-details', 'Front\PickupDeliveryController@getOrderTrackingDetails')->name('bookingIndex');
		Route::post('promo-code/verify', 'Front\PickupDeliveryController@postVerifyPromoCode')->name('verify.cab.booking.promo-code');
		Route::get('get-product-order-form', 'Front\PickupDeliveryController@getProductOrderForm')->name('get-product-order-form');

        Route::post('create/user/bid_ride_request', 'Front\PickupDeliveryController@createBidRideRequest')->name('createBid');
        Route::post('order-ride-bid-details', 'Front\PickupDeliveryController@getBidsRelatedToOrderRide')->name('getBidsRelatedToOrderRide');
        Route::post('accept-ride-bid', 'Front\PickupDeliveryController@acceptBidsRelatedToBidRideOrderRide')->name('acceptBidByCustomer');



	});
	Route::post('upload-file', 'Front\RatingController@uploadFile')->name('uploadfile');
	//Passbase
	Route::get('passbase/page', 'Front\PassbaseController@index')->name('passbase.page');
	Route::match(['get', 'post'], 'passbase/store', 'Front\PassbaseController@storeAuthkey')->name('passbase.store');
	Route::get('user/chat/userVendor/{room_id?}', 'Front\ChatController@UservendorChat')->name("userChat.UservendorChat");
	Route::get('user/chat/userAgent/{room_id?}', 'Front\ChatController@UserAgentChat')->name("userChat.UserAgentChat");
	Route::get('user/chat/userToUser/{room_id?}', 'Front\ChatController@UserToUserChat')->name("userChat.UserToUserChat");

	Route::post('user/chat/fetchOrderDetail', 'Front\ChatController@fetchOrderDetail')->name('userChat.fetchOrderDetail');
	Route::post('user/chat/startChat', 'Front\ChatController@startChat')->name('userChat.startChat');

	//Route::get('azulpay', 'Front\AzulPaymentController@beforePayment')->name('beforePayment');
    //bidding system
	Route::get('user/bidRequest', [BidController::class, 'index'])->name('user.bidRequest');
	Route::POST('user/bidUpdatePdf', [BidController::class, 'uploadPrescription'])->name('bid.update_pdf');
	Route::get('bidding/make', [BidController::class, 'index'])->name('bid.index');
	Route::get('bid/accept/{id?}/{vid?}', [BidController::class, 'bidAccept'])->name('bid.accept');
	Route::get('bid/reject/{id?}/{vid?}', [BidController::class, 'bidReject'])->name('bid.reject');
	Route::post('store', [BidController::class, 'store'])->name('bid.store');
	Route::get('bid/Details/{id?}', [BidController::class, 'bidDetails'])->name('bid.details');
	Route::post('add/bid/prescription', [BidController::class, 'uploadPrescription'])->name('bid.uploadPrescription');  //add bidding prescription
	Route::post('get/bid/prescription', [BidController::class, 'getPrescription'])->name('getPrescription'); //get bedding prescription
	Route::get('product-search', [BidController::class, 'search'])->name('searchProduct'); //vendor product search
	Route::get('bid/add/to/cart/{id}', [CartController::class, 'initCart'])->name('bidding-cart');

	/**
	 * booking routes
	 */
	Route::post('booking/checkProductAvailibility', 'Front\Booking\ProductBookingController@checkProductAvailibility')->name('product-booking.checkProductAvailibility');   # update all product actions

	// gift card
	Route::get('user/giftCard', 'Front\giftCard\GiftcardController@getGiftCard')->name("giftCard.index");
	Route::get('user/giftCard/payment/{id}', 'Front\giftCard\GiftcardController@selectGiftCardPayment')->name('giftCard.paymentList');
	Route::get('user/giftCard/list', 'Front\giftCard\GiftcardController@postGiftCardLisTCart')->name('giftCard.cart.list');
	Route::post('verify/giftCard', 'Front\giftCard\GiftcardController@postVerifyGiftCardCode')->name('verify.giftCard');
	Route::post('remove/giftCard', 'Front\giftCard\GiftcardController@RemoveGiftCardCode')->name('remove.giftCard');
	Route::get('user/giftCard/mailTest', 'Front\giftCard\GiftcardController@textGiftMail')->name('giftCard.mail');


	Route::resource('posts', 'Front\PostController');
	Route::get('get-attributes', 'Front\PostController@getCategoryAttributes')->name("category.attributes");
	Route::post('addProductWithAttribute', 'Front\PostController@addProductWithAttribute')->name("posts.addProductWithAttribute");
	/**
	 * booking routes
	 */
	// Route::post('booking/checkProductAvailibility', 'Front\Booking\ProductBookingController@checkProductAvailibility')->name('product-booking.checkProductAvailibility');   # update all product actions

});
