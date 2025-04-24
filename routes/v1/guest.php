<?php
Route::group(['prefix' => 'v1', 'middleware' => ['ApiLocalization']], function () {

    Route::post('dispatcher/check-order-keys', 'Api\v1\BaseController@checkOrderPanelKeys')->middleware('ConnectDbFromDispatcher');

    Route::any('category-product-sync-dispatcher', 'Api\v1\DispatcherController@categoryProductSyncDispatcher')->middleware('ConnectDbFromDispatcher');

    Route::post('check-order-keys', 'Api\v1\BaseController@checkOrderPanelKeys')->middleware('ConnectDbFromInventory');

    Route::post('vendor-sync-inventory', 'Api\v1\VendorController@vendorSyncInventory')->middleware('ConnectDbFromInventory');

    Route::group(['middleware' => ['ConnectDbFromInventory']], function () { //inventory
        Route::group(['prefix' => 'inventory'], function () {
            Route::post('getUnAssignedOrderCategory', 'Api\v1\InventoryController@getUnAssignedOrderCategory');
            Route::post('getOrderVendorById', 'Api\v1\InventoryController@getOrderVendorById');
            Route::post('getOrderVendors', 'Api\v1\InventoryController@getOrderVendors');
            Route::post('getOrderCategories', 'Api\v1\InventoryController@getOrderCategories');
            Route::post('getOrderVendorCategories', 'Api\v1\InventoryController@getOrderVendorCategories');
            Route::post('syncVendorCategoryProducts', 'Api\v1\InventoryController@syncVendorCategoryProducts');
            Route::post('updateRoyoProductQuantity', 'Api\v1\InventoryController@updateRoyoProductQuantity');
            Route::post('getOrderProductBySku', 'Api\v1\InventoryController@getOrderProductBySku');
            Route::post('deleteOrderProductBySku', 'Api\v1\InventoryController@deleteOrderProductBySku');
            Route::post('needSyncWithOrder', 'Api\v1\InventoryController@needSyncWithOrder');
            Route::post('getOrderCategoryById', 'Api\v1\InventoryController@getOrderCategoryById');
            Route::post('getSyncStoreOrderProductIds', 'Api\v1\InventoryController@getSyncStoreOrderProductIds');
        });
    });

    Route::group(['middleware' => ['dbCheck', 'checkAuth']], function () { //apilogger

        Route::post('get-paylink-data', 'Api\v1\PaylinkGatewayController@updatePaymentStatus');
        Route::get('static-dropoff-locations', 'Api\v1\AddressController@staticDropoffLocations');

        Route::group(['prefix' => 'estimation'], function () {

            Route::get('get-product-estimation-with-addons', 'Api\v1\ProductEstimationController@getProductEstimationWithAddons');

            Route::post('add-estimated-products-in-cart', 'Api\v1\ProductEstimationController@addEstimatedProductInCart');

            Route::post('remove-products-from-estimated-cart', 'Api\v1\ProductEstimationController@removeProductFromEstimatedCart');
            Route::post('remove-addons-from-estimated-cart', 'Api\v1\ProductEstimationController@removeAddonsFromEstimatedCart');

            Route::post('get-estimation', 'Api\v1\ProductEstimationController@getEstimation');
            Route::post('assign-order-qrcode', 'Api\v1\ProductEstimationController@assingQrcode');

            Route::post('transfer-estimated-cart-products-to-real-cart', 'Api\v1\ProductEstimationController@transferEstimatedCartProductsToRealCart');
        });


        Route::post('sendTestMail', 'Api\v1\BaseController@sendTestMail');

        Route::get('user/registration/document', 'Api\v1\HomeController@UserRegistrationDocument');

        Route::post('/cart/updateCartWalletAmount', 'Api\v1\CartController@updateCartWalletAmount');

        Route::post('product/inquiry', 'Api\v1\ProductInquiryController@store');
        Route::post('cart/add', 'Api\v1\CartController@add');
        Route::post('checkProductAvailibility', 'Api\v1\RentalProductController@checkProductAvailibility');
        Route::get('cart/list', 'Api\v1\CartController@index');
        Route::post('cart/add-booking-option', 'Api\v1\CartController@addBookingOptionToCart');
        Route::post('upload/prescriptions', 'Api\v1\CartController@uploadPrescriptions');
        Route::post('delete/prescriptions', 'Api\v1\CartController@deleteProductPrescription');
        Route::post('mfc/stk/push', 'Api\v1\CartController@stkPushRequest');
        Route::any('vendor/slots', 'Api\v1\CartController@checkScheduleSlots');
        Route::get('vendor/dropoffslots', 'Api\v1\CartController@checkScheduleDropoffSlots'); // Added By Ovi  // To Get Drop Off Slots
        Route::post('homepage', 'Api\v1\HomeController@homepage');
        Route::post('get/subcategory/vendor', 'Api\v1\HomeController@getSubcategoryVendor');
        Route::get('get/edited-orders', 'Api\v1\HomeController@getEditedOrders');
        Route::get('product/{id}', 'Api\v1\ProductController@productById');
        Route::post('getShippingProductDeliverySlots', 'Api\v1\ProductController@getShippingProductDeliverySlots');
        Route::post('getProductDeliverySlotsInterval', 'Api\v1\ProductController@getProductDeliverySlotsInterval');
        Route::POST('checkProductAvailibility', 'Api\v1\ProductController@checkProductAvailibility');
        Route::get('getAllProductTags', 'Api\v1\ProductController@getAllProductTags');
        Route::post('get-products', 'Api\v1\ProductController@productList');
        Route::get('products_faq/{id}', 'Api\v1\ProductController@getProductFaq');
        Route::get('cms/page/list', 'Api\v1\CMSPageController@getPageList');
        Route::get('brand/{id?}', 'Api\v1\BrandController@productsByBrand');
        Route::get('category/{id?}', 'Api\v1\CategoryController@categoryData');
        // get Category kyc document
        Route::post('category_kyc_document', 'Api\v1\CategoryController@getcategoryKycDocument');
        Route::post('submit_category_kyc', 'Api\v1\CartController@updateCartCategoryKyc');
        Route::post('inquiry-mode/store', 'Api\v1\ProductController@storeProductInquiry');
        Route::post('search/{type}/{id?}', 'Api\v1\HomeController@globalSearch');
        Route::post('cms/page/detail', 'Api\v1\CMSPageController@getPageDetail');
        Route::post('brand/filters/{id?}', 'Api\v1\BrandController@brandFilters');
        Route::get('celebrity/{all?}', 'Api\v1\CelebrityController@celebrityList');
        Route::post('category/filters/{id?}', 'Api\v1\CategoryController@categoryFilters');
        Route::get('celebrityProducts/{id?}', 'Api\v1\CelebrityController@celebrityProducts');
        Route::post('celebrity/filters/{id?}', 'Api\v1\CelebrityController@celebrityFilters');
        Route::get('vendor/all', 'Api\v1\VendorController@viewAll');
        Route::get('vendor/{id?}', 'Api\v1\VendorController@productsByVendor');
        Route::get('vendor-optimize/{id?}', 'Api\v1\VendorController@productsByVendorOptimize');
        Route::get('vendor-optimize-filters/{id?}', 'Api\v1\VendorController@productsByVendorOptimizeFilterList');
        Route::post('vendor/filters/{id?}', 'Api\v1\VendorController@vendorFilters');
        Route::post('vendor/category/list', 'Api\v1\VendorController@postVendorCategoryList');
        Route::post('vendor/vendorProductsFilter', 'Api\v1\VendorController@vendorProductsFilter');
        Route::post('vendor/vendorProductsFilterOptimize', 'Api\v1\VendorController@vendorProductsFilterOptimize');
        // Route::post('vendor/category/list', 'Api\v1\VendorController@postVendorCategoryList');
        Route::get('vendor/{slug1}/{slug2}', 'Api\v1\VendorController@vendorCategoryProducts');
        // Route::get('vendor/category/productsFilter/{slug1}/{slug2}', 'Api\v1\VendorController@vendorCategoryProductsFilter');
        Route::post('vendor/register', 'Api\v1\VendorController@postVendorRegister');
        Route::post('driver/register', 'Api\v1\AuthController@driverSignup');
        Route::post('checkIsolateSingleVendor', 'Api\v1\CartController@checkIsolateSingleVendor');
        Route::post('productByVariant/{id}', 'Api\v1\ProductController@getVariantData')->name('productVariant');
        Route::post('contact-us', 'Api\v1\HomeController@contactUs');

        Route::post('upload-image-pickup', 'Api\v1\PickupDeliveryController@uploadImagePickup');  ////// upload image while pickup delivery

        //Bidding Controller
        Route::post('upload/bid/prescriptions',    'Api\v1\BiddingController@uploadBiddingPrescription');
        Route::get('get/vendor/bid/prescriptions', 'Api\v1\BiddingController@getVendorPrescription');
        Route::get('get/user/bid/prescriptions',   'Api\v1\BiddingController@getUserPrescription');
        Route::post('delete/bid/prescriptions',     'Api\v1\BiddingController@deleteProductPrescription');
        Route::post('get/vendor/product/search',   'Api\v1\BiddingController@search');


        Route::post('cart/product/lastAdded', 'Api\v1\CartController@getLastAddedProductVariant');
        Route::post('cart/product/variant/different-addons', 'Api\v1\CartController@getProductVariantWithDifferentAddons');

        Route::post('promo-code-open/list', 'Api\v1\PickupDeliveryController@postPromoCodeListOpen');
        Route::post('order/after/payment', 'Front\PaytabController@after_app_payment');
        //Passbase Store
        Route::post('passbase/store', 'Api\v1\PassbaseController@storeAuthkey');

        Route::post('order-tracking', 'Api\v1\OrderController@OrderTracking');

        Route::post('upload-cart-file', 'Api\v1\CartController@uploadOrderFile');
        Route::get('remove-cart-file', 'Api\v1\CartController@RemoveOrderFile');
        // get slot from dispatcher
        Route::post('getslotsFormDispatcher', 'Api\v1\AppointmentController@getSlotFromDispatchDemand');
        // get GerenalSlot slot from dispatcher
        Route::get('getDispatcherGerenalSlot', 'Api\v1\DispatcherController@getDispatcherGerenalSlot');

        Route::get('home-restaurents', 'Api\v1\HomeController@homeRestaurents');
        Route::get('category-restaurents/{category_id}', 'Api\v1\HomeController@categoryRestaurents');

        Route::get('allergic-items', 'Api\v1\AllergicItemController@index');

    });

    Route::group(['middleware' => ['dbCheck', 'systemAuth']], function () { //apilogger
        Route::get('cart/empty', 'Api\v1\CartController@emptyCart');
        Route::get('coupons/{id?}', 'Api\v1\CouponController@list');
        Route::post('cart/remove', 'Api\v1\CartController@removeItem');
        Route::get('cart/totalItems', 'Api\v1\CartController@getItemCount');
        Route::post('cart/updateQuantity', 'Api\v1\CartController@updateQuantity');
        Route::post('promo-code/list', 'Api\v1\PromoCodeController@postPromoCodeList');
        Route::post('promo-code/verify', 'Api\v1\PromoCodeController@postVerifyPromoCode');
        Route::post('promo-code/remove', 'Api\v1\PromoCodeController@postRemovePromoCode');
        Route::post('promo-code/validate_promo_code', 'Api\v1\PromoCodeController@validate_promo_code');
        Route::post('promo-code/vendor_promo_code', 'Api\v1\PromoCodeController@vendorPromoCodeList');
        Route::post('cart/product-schedule/update', 'Api\v1\CartController@updateProductSchedule');
        Route::post('cart/productfaq/update', 'Api\v1\CartController@updateCartProductFaq');
        Route::post('dropoff-location', 'Api\v1\StaticDropoffController@getStaticLocation');

        //Make Event for tracking
        Route::post('track-event', 'TrackEventController@saveEvents');

        Route::post('cart/updateCartCheckedStatus', 'Api\v1\CartController@updateCartCheckedStatus');
    });
    Route::group(['middleware' => ['dbCheck']], function () {
        Route::post('header', 'Api\v1\HomeController@headerContent');
        Route::post('rental-protection', 'Api\v1\CartController@getRentalProtection');
    });
});
