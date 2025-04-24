<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/auth', 'middleware' => ['ApiLocalization']], function () {
    Route::get('country-list', 'Api\v1\AuthController@countries');
    // Route::group(['middleware' => ['dbCheck', 'AppAuth', 'apilogger']], function() {
    Route::group(['middleware' => ['dbCheck', 'AppAuth']], function () { //, 'apilog
        Route::get('logout', 'Api\v1\AuthController@logout');
        Route::post('sendToken', 'Api\v1\AuthController@sendToken');
        Route::post('verifyAccount', 'Api\v1\AuthController@verifyToken');
        Route::get('deleteUser', 'Api\v1\AuthController@deleteUser');

        Route::post('upload/client-document', 'LawyerController@uploadClientDocuments');
        Route::post('upload/lawyer-document', 'LawyerController@uploadLawyerDocuments');
        // delete client document
        Route::post('delete/client-document', 'LawyerController@deleteClientDocument');
        // delete lawyer document
        Route::post('delete/lawyer-document', 'LawyerController@deleteLawyerDocument');
        Route::post('document/review-by-user', 'LawyerController@documentReviewdByUser');
        Route::get('client-document/download', 'LawyerController@downloadClientDocument');
        Route::get('lawyer-document/download', 'LawyerController@downloadLawyerDocument');
        Route::get('client-document/view', 'LawyerController@viewClientDocument');
        Route::get('/lawyer-document/view', 'LawyerController@viewLawyerDocument');
        Route::post('lawyerdocuments/accept-reject', 'LawyerController@documentStatus');

        Route::post('reject/re-assign/lawyer', 'LawyerController@reassignLawyer');
        Route::post('reject/order-by-lawyer', 'LawyerController@orderRejectByLawyer');
    });
    Route::group(['middleware' => ['dbCheck']], function () {
        Route::post('login', 'Api\v1\AuthController@login');
        Route::post('loginViaUsername', 'Api\v1\AuthController@loginViaUsername');
        Route::post('verify/phoneLoginOtp', 'Api\v1\AuthController@verifyPhoneLoginOtp');
        Route::post('register', 'Api\v1\AuthController@signup');
        Route::post('resetPassword', 'Api\v1\AuthController@resetPassword');
        Route::post('forgotPassword', 'Api\v1\AuthController@forgotPassword');
        Route::post('vendor-login', 'Api\v1\AuthController@vendorlogin');
    });
});



Route::group(['prefix' => 'v1', 'middleware' => ['ApiLocalization']], function () {

    Route::group(['middleware' => ['dbCheck']], function () {
        Route::get('inapp-notification-list', 'Api\v1\OrderController@inappNotificationList');
        Route::post('social/info', 'Api\v1\SocialController@getKeys');
        Route::post('social/login/{driver}', 'Api\v1\SocialController@login');
        Route::post('get_product_price_from_dispatcher',   'Api\v1\ProductController@getFreeLincerFromDispatcher');
        Route::post('product/search',   'Api\v1\YachtController@productsSearchResult');
        Route::post('check-product-availability/{id}', 'Api\v1\YachtController@checkProductAvailability');
    });

    Route::group(['middleware' => ['dbCheck', 'AppAuth']], function () {
        Route::prefix('/meeting')->group(function () {
            Route::post('/create', 'Api\v1\JitsiMeetingController@create');
            Route::get('/cancel/{meetingId}', 'Api\v1\JitsiMeetingController@cancel');
        });

        /**Chat resourses */
        //Route::resource('chat', 'Client\ChatController');
        Route::get('chat/all/{room_id?}', 'Api\v1\ChatController@index');
        Route::get('chat/user/{room_id?}', 'Api\v1\ChatController@VendorUserChat');
        Route::post('chat/startChat',      'Api\v1\ChatController@startChat');
        Route::get('chat/vendor/{room_id?}', 'Api\v1\ChatController@UservendorChat');
        Route::post('chat/joinChatRoom', 'Api\v1\ChatController@JoinRoom');
        Route::post('chat/sendMessage', 'Api\v1\ChatController@sendMessage');
        Route::post('sendAdminNotification', 'Api\v1\StoreController@send_notification');
        Route::post('chat/fetchOrderDetail', 'Api\v1\ChatController@fetchOrderDetail');
        Route::post('chat/userVendorChatRoom', 'Api\v1\ChatController@userVendorChatRoom');
        Route::post('chat/vendorUserChatRoom', 'Api\v1\ChatController@vendorUserChatRoom');
        Route::post('chat/userAgentChatRoom', 'Api\v1\ChatController@userAgentChatRoom');
        Route::post('chat/sendNotificationToUser', 'Api\v1\ChatController@sendNotificationToUser');
        Route::get('chat/s3-sign', 'Api\v1\ChatController@signAws');

        Route::post('addvendorwishlist', 'Api\v1\HomeController@addVendorWishList');
        Route::post('removevendorwishlist', 'Api\v1\HomeController@removevendorwishlist');
        Route::post('viewvendorwishlist', 'Api\v1\HomeController@viewVendorWishList');

        // Route::post('category-product-sync-dispatcher', 'Api\v1\DispatcherController@categoryProductSyncDispatcher')->middleware('ConnectDbFromDispatcher');
        Route::post('get-order-panel-detail', 'Api\v1\BaseController@getPanelDetail')->middleware('ConnectDbFromDispatcher');

        Route::post('get-blockchain-address', 'Api\v1\BlockchainController@getBlockchainAddress');
        Route::get('get-hourly-base-price', 'Api\v1\CategoryController@getHourlyBasePrice');

        Route::get('profile', 'Api\v1\ProfileController@profile');
        Route::get('getProfile', 'Api\v1\ProfileController@getProfile');
        Route::post('get/agents', 'Api\v1\PickupDeliveryController@getAgents');
        Route::get('account', 'Api\v1\ProfileController@account');
        Route::post('orders/get-order-status', 'Api\v1\OrderController@getOrderStatus');
        Route::get('orders/status-check', 'Api\v1\OrderController@OrderStatus');
        Route::get('orders/change-order-status', 'Api\v1\OrderController@changeOrderStatus');
        Route::get('orders', 'Api\v1\OrderController@getOrdersList');
        Route::get('orders-all', 'Api\v1\OrderController@getOrdersListLenderBorrower');
        Route::get('orders_upcoming_ongoing', 'Api\v1\OrderController@getOrdersLenderBorrower');
        Route::get('RejectedOrderProduct', 'Api\v1\OrderController@getRejectedOrdersList');
        Route::post('orders/tip-after-order', 'Api\v1\OrderController@tipAfterOrder');
        Route::get('wishlists', 'Api\v1\ProfileController@wishlists');
        Route::get('newsLetter', 'Api\v1\ProfileController@newsLetter');
        Route::get('mystore', 'Api\v1\StoreController@getMyStoreDetails');
        Route::get('my_pending_orders', 'Api\v1\StoreController@my_pending_orders');
        Route::post('place/order', 'Api\v1\OrderController@postPlaceOrder');
        Route::post('update/image', 'Api\v1\ProfileController@updateAvatar');
        Route::post('user/getAddress', 'Api\v1\ProfileController@getAddress');
        Route::post('order-detail', 'Api\v1\OrderController@postOrderDetail');
        Route::post('order-detail_p2p', 'Api\v1\OrderController@postOrderDetailP2p');
        Route::post('order-update', 'Api\v1\OrderController@orderUpdate');
        Route::post('order-ride-bid-details', 'Api\v1\PickupDeliveryController@getBidsRelatedToOrderRide');
        Route::post('accept-ride-bid', 'Api\v1\PickupDeliveryController@acceptBidsRelatedToOrderRide');
        Route::post('decline-ride-bid', 'Api\v1\PickupDeliveryController@declineBidsRelatedToOrderRide');

        Route::post('create-payment-intent', 'Api\v1\PaymentResourceController@createPaymentIntent');
        Route::post('confirm-payment-intent', 'Api\v1\PaymentResourceController@confirmPaymentIntent');

        Route::post('update/profile', 'Api\v1\ProfileController@updateProfile');
        Route::get('myWallet', 'Api\v1\WalletController@getFindMyWalletDetails');
        Route::post('myWallet/credit', 'Api\v1\WalletController@creditMyWallet');
        Route::post('wallet/transfer/user/verify', 'Api\v1\WalletController@walletTransferUserVerify');
        Route::post('wallet/transfer/confirm', 'Api\v1\WalletController@walletTransferConfirm');
        Route::post('store/revenue', 'Api\v1\StoreController@getMyStoreRevenueDetails');
        Route::post('changePassword', 'Api\v1\ProfileController@changePassword');
        Route::get('addressBook/{id?}', 'Api\v1\AddressController@getAddressList');
        Route::get('revenue-details', 'Api\v1\RevenueController@getRevenueDetails');
        Route::post('user/address/{id?}', 'Api\v1\AddressController@postSaveAddress');
        Route::get('delete/address/{id}', 'Api\v1\AddressController@postDeleteAddress');
        Route::get('wishlist/update/{pid?}', 'Api\v1\ProfileController@updateWishlist');
        Route::post('send/referralcode', 'Api\v1\ProfileController@postSendReffralCode');
        Route::get('mystore/product/list', 'Api\v1\StoreController@getMyStoreProductList');
        Route::get('primary/address/{id}', 'Api\v1\AddressController@postUpdatePrimaryAddress');
        Route::post('update/order/status', 'Api\v1\OrderController@postVendorOrderStatusUpdate');
        Route::get('payment/options/{page}', 'Api\v1\PaymentOptionController@getPaymentOptions');
        Route::get('mystore/vendors', 'Api\v1\StoreController@getMyStoreVendors');
        Route::get('mystore/vendor/dashboard/{id}', 'Api\v1\StoreController@getMyStoreVendorDashboard');
        Route::get('mystore/vendor/orders/{id}', 'Api\v1\StoreController@getMyStoreVendorOrders');
        Route::get('mystore/vendor/bagOrders/{qrcode?}', 'Api\v1\StoreController@getMyStoreVendorBagOrders');
        Route::get('mystore/vendor/clearBagOrders/{qrcode?}/{order_number?}', 'Api\v1\StoreController@clearBagOrders');
        Route::post('mystore/vendor/rescheduleOrder', 'Api\v1\StoreController@rescheduleOrder');
        Route::post('mystore/vendor/category', 'Api\v1\StoreController@VendorCategory');
        Route::post('mystore/product/add', 'Api\v1\StoreController@addProduct');
        Route::post('mystore/product/detail', 'Api\v1\StoreController@productDetail');
        Route::post('mystore/product/createvariant', 'Api\v1\StoreController@makeVariantRows');
        Route::post('mystore/product/update', 'Api\v1\StoreController@updateProduct');
        Route::post('mystore/product/delete', 'Api\v1\StoreController@deleteProduct');
        Route::post('mystore/product/deletevariant', 'Api\v1\StoreController@deleteProductVariant');
        Route::post('mystore/product/addProductImage', 'Api\v1\StoreController@productImages');
        # Attribute related api
        Route::post('mystore/product/addProductAttribute', 'Api\v1\StoreController@addProductAttribute');
        Route::get('mystore/product/getProductAttribute', 'Api\v1\StoreController@getProductAttribute');
        Route::get('mystore/product/availableListOfAttribute', 'Api\v1\StoreController@availableListOfAttribute');
        Route::post('mystore/product/addProductWithAttribute', 'Api\v1\StoreController@addProductWithAttribute');
        Route::post('mystore/product/deleteProductWithAttributes', 'Api\v1\StoreController@destroy');

        Route::post('mystore/product/getProductImages', 'Api\v1\StoreController@getProductImages');
        Route::post('mystore/product/deleteimage', 'Api\v1\StoreController@deleteProductImage');
        Route::post('mystore/vendor/product/list', 'Api\v1\StoreController@getVendorProductList');
        Route::get('mystore/vendor/product-category/list/{id}', 'Api\v1\StoreController@getVendorProductCategoryList');
        Route::get('mystore/vendor/products-with-category/list/{id}', 'Api\v1\StoreController@getVendorProductsWithCategoryList');
        Route::post('mystore/product/status-update', 'Api\v1\StoreController@updateProductStatus');

        Route::post('vendor-dasboard-data', 'Api\v1\RevenueController@getDashboardDetails');
        Route::post('get-vendor-profile', 'Api\v1\VendorController@getVendorDetails');

        Route::post('update-vendor-profile', 'Api\v1\VendorController@updateVendorDetails');
        Route::post('get-vendor-transactions', 'Api\v1\VendorController@getVendorTransactions');
        //Route::post('get-vendor-transactions', 'Api\v1\VendorController@getOrdersList');

        Route::match(['get', 'post'], 'payment/{gateway}', 'Api\v1\PaymentOptionController@postPayment');
        Route::post('paystack/cancelPurchase', 'Api\v1\PaymentOptionController@paystackCancelPurchase');
        //  Route::match(['get','post'],'payment/plugnpay','Api\v1\PlugnpayGatewayController@beforePayment');

        //azulpay
        Route::match(['get', 'post'], 'payment/azulpay', 'Api\v1\AzulPaymentController@beforePayment');

        //Route::get('payment/{gateway}', 'Api\v1\PaymentOptionController@postPayment');
        Route::post('payment/razorpay/pay/{amount}/{order}', 'Api\v1\RazorpayGatewayController@razorpayCompletePurchase')->name('payment.razorpayCompletePurchase');
        Route::post('payment/complete/paytab', 'Api\v1\PaytabController@completePayment');
        Route::post('payment/failed/paytab', 'Api\v1\PaytabController@failedPayment');

        Route::post('payment/sdk_complete/{gateway?}', 'Api\v1\PaymentOptionController@sdkResponsePayment');
        Route::post('payment/sdk_failed/{gateway?}', 'Api\v1\PaymentOptionController@sdkFailedPayment');
        //azulpay
        Route::match(['get', 'post'], 'payment/azulpay', 'Api\v1\AzulPaymentController@beforePayment');
        Route::get('user/get-user-cards', 'Api\v1\ProfileController@getUserCards');
        Route::get('user/setDefaultCard', 'Api\v1\ProfileController@setDefaultCard');
        Route::get('user/deleteCard', 'Api\v1\ProfileController@deleteCard');

        Route::post('payment/place/order', 'Api\v1\PaymentOptionController@postPlaceOrder');
        Route::get('user/loyalty/info', 'Api\v1\LoyaltyController@index');
        Route::post('add/vendorTable/cart', 'Api\v1\CartController@addVendorTableToCart');
        Route::post('cart/schedule/update', 'Api\v1\CartController@updateSchedule');
        Route::post('repeatOrder', 'Api\v1\CartController@repeatOrder');
        Route::get('order/orderDetails_for_notification/{order_id}', 'Api\v1\OrderController@orderDetails_for_notification');
        Route::post('cart/checkSlotOrders', 'Api\v1\CartController@checkSlotOrders'); //Added by Surendra
        Route::post('user/editorder', 'Api\v1\OrderController@editOrderByUser');
        Route::post('user/discardeditorder', 'Api\v1\OrderController@discardEditOrderByUser');
        Route::post('user/orderVenderStatusUpdate', 'Api\v1\OrderController@orderVenderStatusUpdate');
        Route::post('order/vendorReached', 'Api\v1\OrderController@sendVendorReachedLocation');

        Route::post('user/saveVenderBankDetails', 'Api\v1\VendorController@saveVenderBankDetails');

        // Notification Api
     
        Route::post('inapp-notification-delete', 'Api\v1\OrderController@inappNotificationDelete');
        Route::get('notification-list', 'Api\v1\OrderController@notificationList');
        Route::post('delete-notification', 'Api\v1\OrderController@deleteNotification');

        // payment card apis

        Route::post('add-card', 'Api\v1\CardController@addCard');
        Route::get('get-card-details', 'Api\v1\CardController@cardDetails');
        Route::post('delete-card', 'Api\v1\CardController@deleteCard');

        // Stripe Customer Card Saved Routes
        Route::post('save-card', 'Api\v1\StripeController@saveCardStripe');
        Route::post('payment-intent', 'Api\v1\StripeGatewayController@createPaymentIntent');


        Route::post('update-wishlist-vendor', 'Api\v1\ProfileController@updateWishlistVendor');
        Route::get('wishlist-vendors', 'Api\v1\ProfileController@wishlistVendors');
        // Rating & review
        Route::group(['prefix' => 'rating'], function () {
            Route::post('update-product-rating', 'Api\v1\RatingController@updateProductRating');
            Route::get('get-product-rating', 'Api\v1\RatingController@getProductRating');
            Route::get('product-rating', 'Api\v1\RatingController@getAllProductRating');
            Route::post('update-driver-rating', 'Api\v1\RatingController@updateDriverRating');
            Route::get('get-driver-rating', 'Api\v1\RatingController@getDriverRating');
            // Route::post('driver-agent-rating', 'Api\v1\RatingController@driverAgentRating');
            Route::post('get-multi-driver-rating', 'Api\v1\RatingController@getAgentRatingQues');
        });
        Route::post('upload-file', 'Api\v1\RatingController@uploadFile');
        // Return order
        Route::group(['prefix' => 'return-order'], function () {
            Route::get('get-order-data-in-model', 'Api\v1\ReturnOrderController@getOrderDatainModel');
            Route::get('get-return-products', 'Api\v1\ReturnOrderController@getReturnProducts');
            Route::post('update-product-return', 'Api\v1\ReturnOrderController@updateProductReturn');
            Route::post('vendor-order-for-cancel', 'Api\v1\ReturnOrderController@vendorOrderForCancel');
        });

        // Return order
        Route::group(['prefix' => 'replace-order'], function () {
            Route::get('get-replace-order-data-in-model', 'Api\v1\ReturnOrderController@getReplaceOrderDataInModel');
            Route::get('get-replace-products', 'Api\v1\ReturnOrderController@getReplaceProducts');
            Route::post('update-product-replace', 'Api\v1\ReturnOrderController@updateProductReplace');
        });

        // Cancel order
        Route::group(['prefix' => 'cancel-order'], function () {
            Route::get('get-cancel-order-reason', 'Api\v1\CancelOrderController@getCancelOrderReason');
        });

        // pickup & delivery
        Route::group(['prefix' => 'pickup-delivery'], function () {
            Route::post('get-list-of-vehicles-old/{id}', 'Api\v1\PickupDeliveryController@getListOfVehicles');
            Route::post('get-list-of-vehicles/{vid}/{cid?}', 'Api\v1\PickupDeliveryController@productsByVendorInPickupDelivery');
            Route::post('product-detail', 'Api\v1\PickupDeliveryController@postCabProductById');
            Route::post('create-order', 'Api\v1\PickupDeliveryController@createOrder');
            Route::post('create-order-notifications', 'Api\v1\PickupDeliveryController@createOrderNotification');
            Route::post('cart/updateQuantity', 'Api\v1\CartController@updateQuantity');
            Route::post('promo-code/list', 'Api\v1\PickupDeliveryController@postPromoCodeList');
            Route::post('promo-code/verify', 'Api\v1\PickupDeliveryController@postVerifyPromoCode');
            Route::post('promo-code/remove', 'Api\v1\PickupDeliveryController@postRemovePromoCode');
            Route::post('order-tracking-details', 'Api\v1\PickupDeliveryController@getOrderTrackingDetails');
            Route::match(['get', 'post'], 'add-rider', 'Api\v1\PickupDeliveryController@getAllRiders');

            Route::post('edit-order', 'Api\v1\PickupDeliveryController@updatePickupDeliveryOrderByCustomer');
        });

        // user subscription
        Route::group(['prefix' => 'subscription'], function () {
            Route::get('plans/user', 'Api\v1\SubscriptionPlansUserController@getSubscriptionPlans');
            Route::post('plan/save/user/{slug?}', 'Api\v1\SubscriptionPlansUserController@saveSubscriptionPlan');
            Route::get('plan/edit/user/{slug}', 'Api\v1\SubscriptionPlansUserController@editSubscriptionPlan');
            Route::get('plan/delete/user/{slug}', 'Api\v1\SubscriptionPlansUserController@deleteSubscriptionPlan');
            Route::post('plan/updateStatus/user/{slug}', 'Api\v1\SubscriptionPlansUserController@updateSubscriptionPlanStatus');
        });
        Route::group(['prefix' => 'user/subscription'], function () {
            Route::get('plans', 'Api\v1\UserSubscriptionController@getSubscriptionPlans');
            Route::get('selectPlan/{slug}', 'Api\v1\UserSubscriptionController@selectSubscriptionPlan');
            Route::post('purchase/{slug}', 'Api\v1\UserSubscriptionController@purchaseSubscriptionPlan');
            Route::post('cancel/{slug}', 'Api\v1\UserSubscriptionController@cancelSubscriptionPlan');
            Route::get('checkActivePlan/{slug}', 'Api\v1\UserSubscriptionController@checkActiveSubscriptionPlan');
        });

        // vendor subscription
        Route::group(['prefix' => 'vendor/subscription'], function () {
            Route::get('plans/{id}', 'Api\v1\VendorSubscriptionController@getSubscriptionPlans');
            Route::get('select/{slug}', 'Api\v1\VendorSubscriptionController@selectSubscriptionPlan');
            Route::post('purchase/{id}/{slug}', 'Api\v1\VendorSubscriptionController@purchaseSubscriptionPlan');
            Route::post('cancel/{id}/{slug}', 'Api\v1\VendorSubscriptionController@cancelSubscriptionPlan');
            Route::get('checkActive/{id}/{slug}', 'Api\v1\VendorSubscriptionController@checkActiveSubscriptionPlan');
            Route::any('filterData', 'Api\v1\VendorSubscriptionController@getSubscriptionsFilterData');
            Route::post('status/update/{slug}', 'Api\v1\VendorSubscriptionController@updateSubscriptionStatus');
        });
        Route::group(['prefix' => 'subscription'], function () {
            Route::get('plans/vendor', 'Api\v1\SubscriptionPlansVendorController@getSubscriptionPlans');
            Route::post('plan/save/vendor/{slug?}', 'Api\v1\SubscriptionPlansVendorController@saveSubscriptionPlan');
            Route::get('plan/edit/vendor/{slug}', 'Api\v1\SubscriptionPlansVendorController@editSubscriptionPlan');
            Route::get('plan/delete/vendor/{slug}', 'Api\v1\SubscriptionPlansVendorController@deleteSubscriptionPlan');
            Route::post('plan/updateStatus/vendor/{slug}', 'Api\v1\SubscriptionPlansVendorController@updateSubscriptionPlanStatus');
            Route::post('plan/updateOnRequest/vendor/{slug}', 'Api\v1\SubscriptionPlansVendorController@updateSubscriptionPlanOnRequest');
        });

        // Edit Order
        Route::group(['prefix' => 'edit-order'], function () {
            Route::post('approve/reject', 'Api\v1\OrderController@submitEditedOrder');
        });

        Route::post('/create-contact', 'Hubspot\HubspotApiController@create');

        //Bidding Controller
        Route::post('upload/bid/prescriptions',    'Api\v1\BiddingController@uploadBiddingPrescription');
        Route::get('get/vendor/bid/prescriptions/{vid?}', 'Api\v1\BiddingController@getVendorPrescription');
        Route::get('get/user/bid/prescriptions',   'Api\v1\BiddingController@getUserPrescription');
        Route::post('delete/bid/prescriptions',     'Api\v1\BiddingController@deleteProductPrescription');
        Route::get('get/vendor/product/search/{vid}/{key}',   'Api\v1\BiddingController@search');
        Route::get('get/user/bid/listing/{bid_id}',   'Api\v1\BiddingController@getbidList');
        Route::post('bid/add_bid_product_to_cart',   'Api\v1\BiddingController@addBidProductToCart');
        Route::post('bid/reject',   'Api\v1\BiddingController@bidReject');
        Route::post('bid/accept',   'Api\v1\BiddingController@bidAccept');
        Route::post('bid/placeBid',   'Api\v1\BiddingController@placeBid');


        // gift Card Order
        Route::group(['prefix' => 'giftCard'], function () {
            Route::get('list', 'Api\v1\GiftcardController@getGiftCard');
            Route::post('apply', 'Api\v1\GiftcardController@postVerifyGiftCardCode');
            Route::post('remove', 'Api\v1\GiftcardController@RemoveGiftCardCode');
        });
        Route::group(['prefix' => 'influencer'], function () {
            Route::get('refer-earn', 'Api\v1\InfluencerController@index');
            Route::get('get-influencer-form/{id}', 'Api\v1\InfluencerController@getInfluencerForm');
            Route::post('save-influencer-form', 'Api\v1\InfluencerController@save');
        });


        //-------routes for bid and ride------------------------------------
        Route::post('create/user/bid_ride_request', 'Api\v1\PickupDeliveryController@createBidRideRequest');
        Route::post('order-ride-bid-details', 'Api\v1\PickupDeliveryController@getBidsRelatedToOrderRide');
        Route::post('accept-ride-bid-request', 'Api\v1\PickupDeliveryController@acceptBidsRelatedToBidRideOrderRide');
        Route::post('decline-ride-bid', 'Api\v1\PickupDeliveryController@declineBidsRelatedToOrderRide');

        Route::group(['prefix' => 'mtn'], function () {
            Route::post('create-token', 'Api\v1\MtnMomoController@createToken')->name('mtn.createtoken');
            Route::get('response/{id?}', 'Api\v1\MtnMomoController@getResponse')->name('mtn.response');
        });

        Route::get('allergic-items', 'Api\v1\AllergicItemController@index');
        Route::get('user/allergic-items', 'Api\v1\AllergicItemController@userAllergicItems');
        Route::post('user/add-allergic-items', 'Api\v1\AllergicItemController@addUpdateAllergicItems');
        Route::post('user/remove-allergic-items/{id}', 'Api\v1\AllergicItemController@destroy');
    });
});
