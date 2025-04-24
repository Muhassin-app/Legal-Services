<?php

use App\Http\Controllers\BookingOptionController;
use App\Http\Controllers\Front\SearchController;
use App\Http\Controllers\Client\CMS\PageController;
use App\Http\Controllers\Client\CMS\EmailController;
use App\Http\Controllers\Client\CMS\NotificationController;
use App\Http\Controllers\Client\CMS\SmsController;
use App\Http\Controllers\Client\CMS\ReasonController;
use App\Http\Controllers\Client\SocialMediaController;
use App\Http\Controllers\Client\VendorPayoutController;
use App\Http\Controllers\Client\VendorBidController;
use App\Http\Controllers\Client\DownloadFileController;
use App\Http\Controllers\Client\ProductImportController;
use App\Http\Controllers\Client\Accounting\TaxController;
use App\Http\Controllers\Client\Accounting\OrderController;
use App\Http\Controllers\Client\Accounting\VendorController;
use App\Http\Controllers\Client\Accounting\LoyaltyController;
use App\Http\Controllers\Client\CategoryKycDocumentController;
use App\Http\Controllers\Client\Accounting\PromoCodeController;
use App\Http\Controllers\Client\UserRegistrationDocumentController;
use App\Http\Controllers\Client\VendorRegistrationDocumentController;
use App\Http\Controllers\Client\SubscriptionPlansUserController;
use App\Http\Controllers\Client\TagController;
use App\Http\Controllers\Client\ClientSlotController;
use App\Http\Controllers\Client\DestinationController;
use App\Http\Controllers\Client\DriverRegistrationDocumentController;
use App\Http\Controllers\Client\ProductFaqController;
use App\Http\Controllers\Client\EstimationController;
use App\Http\Controllers\Client\RazorpayGatwayController;
use App\Http\Controllers\Client\StaticDropoffController;

use App\Http\Controllers\Client\GiftCard\GiftcardController;
use App\Http\Controllers\Client\RentalProtectionController;
use App\Http\Controllers\LawyerController;
use Illuminate\Support\Facades\Route;

Route::get('email-test', function () {
    $details['email'] = 'testmail@yopmail.com';
    dispatch(new App\Jobs\SendVerifyEmailJob($details))->delay(now()->addSeconds(2))->onQueue('course_interactions');
    dd('done');
});

Route::get('admin/login', 'Auth\LoginController@getClientLogin')->name('admin.login')->middleware('domain');
Route::get('file-download/{filename}', [DownloadFileController::class, 'index'])->name('file.download.index');
Route::post('admin/login/client', 'Auth\LoginController@clientLogin')->name('client.login');
Route::get('admin/wrong/url', 'Auth\LoginController@wrongurl')->name('wrong.client');

// ADMIN LANGUAGE SWITCH
Route::group(['middleware' => 'adminLanguageSwitch'], function () {
    Route::group(['middleware' => ['ClientAuth', 'database', 'permission'], 'prefix' => '/client'], function () {
        Route::prefix('jitsi')->controller('Client\JitsiMeetingController')->group(function () {
            Route::post('create', 'create')->name('jitsi.create');
            Route::get('join/{order_number}', 'join')->name('jitsi.join');
            Route::post('leave/{room_name}', 'leave')->name('jitsi.leave');
        });

        Route::post('/webhook/set', 'AhoyController@setWebhook')->name('setWebhook');
        Route::any('/logout', 'Auth\LoginController@logout')->name('client.logout');
        Route::get('profile', 'Client\UserController@profile')->name('client.profile');


        Route::get('notifications/list', 'Client\DashBoardController@notificationList')->name('noti.list');
        Route::get('role/add', 'Client\RolePermissionController@indexRole')->name('roles');
        Route::post('role/save', 'Client\RolePermissionController@saveRole')->name('save.roles');
        Route::POST('role/getRole', 'Client\RolePermissionController@getRole')->name('get.role');

        Route::POST('role/getRolePermission', 'Client\RolePermissionController@getRolePermission')->name('get.role.permission');
        Route::post('role/savePermissions', 'Client\RolePermissionController@saveRolePermissions')->name('save.role.permissions');

        Route::get('manage-cache', 'Client\ManageCacheController@index')->name('manageCache');

        Route::get('permission/add', 'Client\RolePermissionController@indexPermission')->name('permissions');
        Route::post('permission/save', 'Client\RolePermissionController@savePermission')->name('save.permission');
        Route::post('permission/assign', 'Client\RolePermissionController@assignPermission')->name('assign.permissions');

        Route::POST('role/getRolePermission', 'Client\RolePermissionController@getRolePermission')->name('get.role.permission');
        Route::post('role/savePermissions', 'Client\RolePermissionController@saveRolePermissions')->name('save.role.permissions');

        Route::get('dashboard', 'Client\DashBoardController@index')->name('client.dashboard');
        Route::get('dashboard_old', 'Client\DashBoardController@dashboard_old')->name('client.dashboard_old');
        Route::get('dashboard/filter', 'Client\DashBoardController@postFilterData')->name('client.dashboard.filter');
        Route::get('dashboard_new/filter', 'Client\DashBoardController@postFilterDataNew')->name('client.dashboard.filter_new');
        Route::get('salesInfo/monthly', 'Client\DashBoardController@monthlySalesInfo')->name('client.monthlySalesInfo');
        Route::get('salesInfo/yearly', 'Client\DashBoardController@yearlySalesInfo')->name('client.yearlySalesInfo');
        Route::get('salesInfo/weekly', 'Client\DashBoardController@weeklySalesInfo')->name('client.weeklySalesInfo');
        Route::get('categoryInfo', 'Client\DashBoardController@categoryInfo')->name('client.categoryInfo');
        Route::get('cms/pages', [PageController::class, 'index'])->name('cms.pages');
        Route::get('cms/page/{id}', [PageController::class, 'show'])->name('cms.page.show');
        Route::post('cms/page/update', [PageController::class, 'update'])->name('cms.page.update');
        Route::post('cms/page/create', [PageController::class, 'store'])->name('cms.page.create');
        Route::post('cms/page/delete', [PageController::class, 'destroy'])->name('cms.page.delete');
        Route::post('cms/page/ordering', [PageController::class, 'saveOrderOfPage'])->name('cms.page.saveOrderOfPage');
        Route::get('cms/emails', [EmailController::class, 'index'])->name('cms.emails');
        Route::get('cms/emails/{id}', [EmailController::class, 'show'])->name('cms.emails.show');
        Route::post('cms/emails/update', [EmailController::class, 'update'])->name('cms.emails.update');
        Route::get('cms/notifications', [NotificationController::class, 'index'])->name('cms.notifications');
        Route::get('cms/notifications/{id}', [NotificationController::class, 'show'])->name('cms.notifications.show');
        Route::post('cms/notifications/update', [NotificationController::class, 'update'])->name('cms.notifications.update');
        Route::get('cms/sms', [SmsController::class, 'index'])->name('cms.sms');
        Route::get('cms/sms/{id}', [SmsController::class, 'show'])->name('cms.sms.show');
        Route::post('cms/sms/update', [SmsController::class, 'update'])->name('cms.sms.update');

        // Route::get('cms/reasons', [ReasonController::class, 'index'])->name('cms.reasons');
        Route::resource('reason', 'Client\CMS\ReasonController');

        Route::get('account/orders', [OrderController::class, 'index'])->name('account.orders');
        Route::get('account/promo-code', [PromoCodeController::class, 'index'])->name('account.promo.code');
        Route::post('woocommerce/save', [ProductImportController::class, 'postWoocommerceDetail'])->name('woocommerce.save');
        Route::post('product/import', [ProductImportController::class, 'getProductImportViaWoocommerce'])->name('product.import.woocommerce');
        Route::get('account/promo-code/filter', [PromoCodeController::class, 'filter'])->name('account.promo-code.filter');
        Route::get('account/promo-code/export', [PromoCodeController::class, 'export'])->name('account.promo-code.export');
        Route::get('social/media', [SocialMediaController::class, 'index'])->name('social.media.index');
        Route::post('social/media/create', [SocialMediaController::class, 'create'])->name('social.media.create');
        Route::post('social/media/update', [SocialMediaController::class, 'update'])->name('social.media.update');
        Route::get('social/media/edit', [SocialMediaController::class, 'edit'])->name('social.media.edit');
        Route::post('social/media/delete', [SocialMediaController::class, 'delete'])->name('social.media.delete');
        Route::get('account/loyalty', [LoyaltyController::class, 'index'])->name('account.loyalty');
        Route::get('account/tax', [TaxController::class, 'index'])->name('account.tax');
        Route::get('account/vendor', [VendorController::class, 'index'])->name('account.vendor');
        // Route::get('account/vendor/payout', [VendorPayoutController::class, 'index'])->name('account.vendor.payout');
        // Route::get('account/vendor/payout/filter', [VendorPayoutController::class, 'filter'])->name('account.vendor.payout.filter');
        Route::get('account/vendor/payout/get/create-account-details', [VendorPayoutController::class, 'createAccountDetails'])->name('account.vendor.payout.createAccountDetails');
        Route::post('vendor/payout/create-razorpay-details', [RazorpayGatwayController::class, 'razorpay_create_contact'])->name('vendor.razorpay_connect');
        Route::post('vendor/payout/create-razorpay-add-funds', [RazorpayGatwayController::class, 'razorpay_add_funds_accounts'])->name('vendor.add.fund.account');



        Route::get('account/vendor/payout/requests', [VendorPayoutController::class, 'vendorPayoutRequests'])->name('account.vendor.payout.requests');
        Route::get('vendor/bid/requests/{id?}', [VendorBidController::class, 'bidRequests'])->name('vendor.bid.request');
        Route::get('vendor/bid/product-search/{id?}', [VendorBidController::class, 'search'])->name('searchProduct'); //vendor product search


        Route::POST('vendor/bid/store', [VendorBidController::class, 'storeBidRequests'])->name('vendor.bid.store');
        Route::get('account/vendor/payout/requests/filter', [VendorPayoutController::class, 'vendorPayoutRequestsFilter'])->name('account.vendor.payout.requests.filter');

        Route::get('backend/order/refund', [OrderController::class, 'backendOrderRefund'])->name('backend.order.refund');
        Route::get('backend/order/refund/filter', [OrderController::class, 'backendOrderRefundFilter'])->name('backend.order.refund.filter');
        Route::get('failed-marg-orders', [OrderController::class, 'getFailedMargOrders'])->name('failed-marg-orders');

        Route::post('account/vendor/payout/request/complete', [VendorPayoutController::class, 'vendorPayoutRequestComplete'])->name('account.vendor.payout.request.complete');
        Route::get('account/tax/filter', [TaxController::class, 'filter'])->name('account.tax.filter');
        Route::get('account/tax/export', [TaxController::class, 'export'])->name('account.tax.export');
        Route::get('account/vendor/filter', [VendorController::class, 'filter'])->name('account.vendor.filter');
        Route::get('account/vendor/getVendorCalculations', [VendorController::class, 'getOrderVendorCalculations'])->name('account.vendor.calculations');
        Route::get('account/vendor/export', [VendorController::class, 'export'])->name('account.vendor.export');
        Route::get('account/order/filter', [OrderController::class, 'filter'])->name('account.order.filter');
        Route::get('account/order/margfilter', [OrderController::class, 'margFilter'])->name('account.order.margFilter');
        Route::get('sync-marg-order/{order_id}', [OrderController::class, 'syncMargOrder'])->name('sync-marg-order');
        Route::post('sync-marg-all-order', [OrderController::class, 'syncMargAllOrder'])->name('sync-marg-all-order');

        Route::get('account/order/getOrderCalculations', [OrderController::class, 'getOrderVendorCalculations'])->name('account.order.calculations');
        Route::get('account/loyalty/filter', [LoyaltyController::class, 'filter'])->name('account.loyalty.filter');
        Route::get('account/loyalty/export', [LoyaltyController::class, 'export'])->name('account.loyalty.export');
        Route::get('account/order/export', [OrderController::class, 'export'])->name('account.order.export');
        Route::get('configure', 'Client\ClientPreferenceController@index')->name('configure.index');
        Route::post('nomenclature/add', 'Client\NomenclatureController@store')->name('nomenclature.store');
        Route::post('cleanSoftDeleted', 'Client\ManageContentController@deleteAllSoftDeleted')->name('config.cleanSoftDeleted');
        Route::post('importDemoContent', 'Client\ManageContentController@importDemoContent')->name('config.importDemoContent');
        Route::post('hardDeleteEverything', 'Client\ManageContentController@hardDeleteEverything')->name('config.hardDeleteEverything');
        Route::get('customize', 'Client\ClientPreferenceController@getCustomizePage')->name('configure.customize');
        Route::post('configUpdate/{code}', 'Client\ClientPreferenceController@update')->name('configure.update');
        Route::post('configUpdate', 'Client\ClientPreferenceController@updateTaxInclusivePrice')->name('configure.taxinclusive');
        Route::post('additionalUpdate', 'Client\ClientPreferenceController@additionalupdate')->name('additional.update');
        Route::post('resetDefault', 'Client\ClientPreferenceController@resetToDefault')->name('reset.config');
        Route::post('toggleDatabase', 'Client\ClientPreferenceController@toggleDatabase')->name('configure.toggleDatabase');
        Route::post('updateIsPriceEnable', 'Client\ClientPreferenceController@updateIsPriceEnable')->name('customize.updateIsPriceEnable');
        Route::post('configUpdateAdditional/{code}', 'Client\ClientPreferenceController@updateAdditional')->name('configure.updateAdditional');

        Route::post('custom/mod/verification', 'Client\ClientPreferenceController@customModVerification')->name('custom.mod.verification');


        Route::post('updatePreferenceAdditional/{code}', 'Client\ClientPreferenceController@updatePreferenceAdditional')->name('configure.updatePreferenceAdditional');
        Route::get('deleteKeysContainingWord/{code}', 'Client\ManageCacheController@deleteKeysContainingWord')->name('configure.deleteKeysContainingWord');


        Route::post('referandearnUpdate/{code}', 'Client\ClientPreferenceController@referandearnUpdate')->name('referandearn.update');
        Route::post('updateDomain/{code}', 'Client\ClientPreferenceController@postUpdateDomain')->name('client.updateDomain');
        Route::resource('banner', 'Client\BannerController');
        Route::post('banner/saveOrder', 'Client\BannerController@saveOrder');
        Route::post('banner/changeValidity', 'Client\BannerController@validity');
        Route::post('vendor/saveLocation/{id}', 'Client\VendorController@updateLocation')->name('vendor.config.pickuplocation');
        Route::post('vendor/ahoyLocation/{id}', 'Client\VendorController@updateAhoyLocation')->name('vendor.config.ahoy.pickuplocation');
        Route::post('banner/toggle', 'Client\BannerController@toggleAllBanner')->name('banner.toggle');
        Route::resource('mobilebanner', 'Client\MobileBannerController');
        Route::post('mobilebanner/saveOrder', 'Client\MobileBannerController@saveOrder');
        Route::post('mobilebanner/changeValidity', 'Client\MobileBannerController@validity');
        Route::post('mobilebanner/toggle', 'Client\MobileBannerController@toggleAllBanner')->name('mobilebanner.toggle');
        Route::get('web-styling', 'Client\WebStylingController@index')->name('webStyling.index');
        Route::post('web-styling/updateWebStyles', 'Client\WebStylingController@updateWebStyles')->name('styling.updateWebStyles');
        Route::post('web-styling/updateOrderIcon', 'Client\WebStylingController@updateOrderStatusIcons')->name('styling.updateOrderStatusIcons');
        Route::post('web-styling/updatePaymentMethods', 'Client\WebStylingController@updatePaymentMethods')->name('styling.updatePaymentMethods');
        Route::post('web-styling/updatePaymentIcons', 'Client\WebStylingController@updatePaymentIcons')->name('styling.updatePaymentIcons');
        Route::post('web-styling/updateWebStylesNew', 'Client\WebStylingController@updateWebStylesNew')->name('styling.updateWebStylesNew');
        Route::get('web-styling/get-html-data-in-modal', 'Client\WebStylingController@getHtmlDatainModal')->name('get-html-data-in-modal');
        Route::get('web-styling/get-image-data-in-modal', 'Client\WebStylingController@getImageDatainModal')->name('get-image-data-in-modal');
        Route::get('web-styling/get-product-data-in-modal', 'Client\WebStylingController@getProductDatainModal')->name('get-products-data-in-modal');
        Route::post('web-styling/update-image-data-in-modal', 'Client\WebStylingController@updateImageDatainModal')->name('update-image-data-in-modal');
        Route::put('web-styling/update-products-data-in-modal', 'Client\WebStylingController@updateProductsDatainModal')->name('update-products-data-in-modal');
        Route::post('web-styling/updateDarkMode', 'Client\WebStylingController@updateDarkMode')->name('styling.updateDarkMode');
        Route::post('homepagelabel/saveOrder', 'Client\WebStylingController@saveOrder');
        Route::post('pickuplabel/saveOrder', 'Client\WebStylingController@saveOrderPickup');
        Route::post('web-styling/pickup-add-section', 'Client\WebStylingController@addNewPickupSection')->name('pickup.add.section');
        Route::post('web-styling/edit-Dynamic-Html-Section', 'Client\WebStylingController@editDynamicHtmlSection')->name('edit.Dynamic.Html.Section');
        Route::post('web-styling/pickup-append-section', 'Client\WebStylingController@appendPickupSection')->name('pickup.append.section');
        Route::get('web-styling/pickup-delete-section/{id}', 'Client\WebStylingController@deletePickupSection')->name('pickup.delete.section');
        Route::post('web-styling/updateHomePageStyle', 'Client\WebStylingController@updateHomePageStyle')->name('web.styling.updateHomePageStyle');
        Route::post('web-styling/update-contact-up', 'Client\WebStylingController@updateContactUs')->name('web.styling.update_contact_up');
        Route::post('web-styling/update-single-category-products', 'Client\WebStylingController@updateSingleCategoryProducts')->name('web.styling.update_single_category_products');
        Route::get('app-styling', 'Client\AppStylingController@index')->name('appStyling.index');
        Route::post('app-styling/updateFont', 'Client\AppStylingController@updateFont')->name('styling.updateFont');
        Route::post('app-styling/updateColor', 'Client\AppStylingController@updateColor')->name('styling.updateColor');
        Route::post('app-styling/updateTabBar', 'Client\AppStylingController@updateTabBar')->name('styling.updateTabBar');
        Route::post('app-styling/updateAppStylesNew', 'Client\AppStylingController@updateAppStylesNew')->name('styling.updateAppStylesNew');
        Route::post('app-styling/pickup-append-section', 'Client\AppStylingController@appendPickupSection')->name('app.pickup.append.section');
        Route::post('app-styling/updateHomePage', 'Client\AppStylingController@updateHomePage')->name('styling.updateHomePage');
        Route::post('app-styling/updateSignupTagLine', 'Client\AppStylingController@updateSignupTagLine')->name('styling.updateSignupTagLine');
        Route::post('app-styling/addTutorials', 'Client\AppStylingController@addTutorials')->name('styling.addTutorials');
        Route::post('app_styling/saveOrderTutorials', 'Client\AppStylingController@saveOrderTutorials')->name('styling.saveOrderTutorials');
        Route::delete('app-styling/deleteTutorials/{id}', 'Client\AppStylingController@deleteTutorials')->name('styling.deleteTutorials');
        Route::resource('category', 'Client\CategoryController');
        Route::post('categoryOrder', 'Client\CategoryController@updateOrder')->name('category.order');
        Route::post('category/translation', 'Client\CategoryController@getCategoryTranslation');
        Route::get('category/delete/{id}', 'Client\CategoryController@destroy');
        Route::resource('variant', 'Client\VariantController');
        Route::post('variant/order', 'Client\VariantController@updateOrders')->name('variant.order');
        Route::post('variant/delete-option', 'Client\VariantController@deleteVariantOption')->name('variant.delete.option');
        Route::get('variant/cate/{cid}', 'Client\VariantController@variantbyCategory');
        Route::resource('brand', 'Client\BrandController');
        Route::post('brand/order', 'Client\BrandController@updateOrders')->name('brand.order');
        Route::resource('cms', 'Client\CmsController');
        Route::resource('vendorregistrationdocument', 'Client\VendorRegistrationDocumentController');
        Route::resource('lawyer', 'LawyerController');
        // Route::get('/lawyer/refferal/{id}', [LawyerController::class, 'generateCode'])->name('generate.refferalcode');
        Route::get('/lawyer/delete/{id}', [LawyerController::class, 'destroy'])->name('lawyer.delete');
        Route::post('/upload-documents', [LawyerController::class, 'upload'])->name('documents.upload');
        Route::post('lawyer/status/{user}', 'LawyerController@toggleStatus');
        Route::get('assign-lawyer/{lawyerId}/{orderId}', 'LawyerController@assignLawyer');
        Route::get('vendor/registration/document/edit', [VendorRegistrationDocumentController::class, 'show'])->name('vendor.registration.document.edit');
        Route::post('vendorregistrationdocument/create', [VendorRegistrationDocumentController::class, 'store'])->name('vendor.registration.document.create');
        Route::post('vendorregistrationdocument/update', [VendorRegistrationDocumentController::class, 'update'])->name('vendor.registration.document.update');
        Route::post('vendor/registration/document/delete', [VendorRegistrationDocumentController::class, 'destroy'])->name('vendor.registration.document.delete');
        // Attribute routes
        Route::resource('attribute', 'Client\AttributeController');
        Route::post('delete-attribute', 'Client\AttributeController@deleteAttribute')->name('deleteAttribute');
        Route::any('updateAttributeOption', 'Client\AttributeController@updateAttributeOption')->name('updateAttributeOption');

        // user registreation document
        Route::resource('userregistrationdocument', 'Client\UserRegistrationDocumentController');
        Route::get('user/registration/document/edit', [UserRegistrationDocumentController::class, 'show'])->name('user.registration.document.edit');
        Route::post('userregistrationdocument/create', [UserRegistrationDocumentController::class, 'store'])->name('user.registration.document.create');
        Route::post('userregistrationdocument/update', [UserRegistrationDocumentController::class, 'update'])->name('user.registration.document.update');
        Route::post('user/registration/document/delete', [UserRegistrationDocumentController::class, 'destroy'])->name('user.registration.document.delete');

        // Category Kyc document
        Route::resource('categorykycdocument', 'Client\CategoryKycDocumentController');
        Route::get('categorykyc/document/edit', [CategoryKycDocumentController::class, 'show'])->name('categorykyc.document.edit');
        Route::post('categorykycdocument/create', [CategoryKycDocumentController::class, 'store'])->name('categorykyc.document.create');
        Route::post('categorykycdocument/update', [CategoryKycDocumentController::class, 'update'])->name('categorykyc.document.update');
        Route::post('categorykyc/document/delete', [CategoryKycDocumentController::class, 'destroy'])->name('categorykyc.document.delete');
        Route::get('categorykyc/list', [CategoryKycDocumentController::class, 'getCategory'])->name('categorykyc.getCategory');

        Route::resource('tag', 'Client\TagController');

        Route::get('tag/edit', [TagController::class, 'show'])->name('tag.edit');
        Route::post('tag/create', [TagController::class, 'store'])->name('tag.create');
        Route::post('tag/update', [TagController::class, 'update'])->name('tag.update');
        Route::post('tag/delete', [TagController::class, 'destroy'])->name('tag.delete');

        Route::get('estimations/barcode/{vendor?}', [EstimationController::class, 'barcode'])->name('estimations.barcode');
        Route::resource('estimations', 'Client\EstimationController');
        Route::resource('estimationsAddon', 'Client\EstimationAddonController');

        Route::get('estimations/edit', [EstimationController::class, 'show'])->name('estimations.edit');
        Route::post('estimations/create', [EstimationController::class, 'store'])->name('estimations.create');
        Route::post('estimations/update', [EstimationController::class, 'update'])->name('estimations.update');
        Route::post('estimations/delete', [EstimationController::class, 'destroy'])->name('estimations.delete');

        Route::post('estimations/update-estimation-matching-logic', [EstimationController::class, 'updateEstimationMatchingLogic'])->name('estimations.updateEstimationMatchingLogic');

        Route::resource('slot', 'Client\ClientSlotController');

        Route::get('slot/edit', [ClientSlotController::class, 'show'])->name('slot.edit');
        Route::post('slot/create', [ClientSlotController::class, 'store'])->name('slot.create');
        Route::post('slot/update', [ClientSlotController::class, 'update'])->name('slot.update');
        Route::post('slot/delete', [ClientSlotController::class, 'destroy'])->name('slot.delete');


        Route::resource('productfaq', 'Client\ProductFaqController');
        Route::get('product/faq/edit', [ProductFaqController::class, 'show'])->name('product.faq.edit');
        Route::post('productfaq/create', [ProductFaqController::class, 'store'])->name('product.faq.create');
        Route::post('productfaq/update', [ProductFaqController::class, 'update'])->name('product.faq.update');
        Route::post('product/faq/delete', [ProductFaqController::class, 'destroy'])->name('product.faq.delete');



        Route::resource('driverregistrationdocument', 'Client\DriverRegistrationDocumentController');
        Route::get('driver/registration/document/edit', [DriverRegistrationDocumentController::class, 'show'])->name('driver.registration.document.edit');
        Route::post('driverregistrationdocument/create', [DriverRegistrationDocumentController::class, 'store'])->name('driver.registration.document.create');
        Route::post('driverregistrationdocument/update', [DriverRegistrationDocumentController::class, 'update'])->name('driver.registration.document.update');
        Route::post('driver/registration/document/delete', [DriverRegistrationDocumentController::class, 'destroy'])->name('driver.registration.document.delete');
        Route::resource('tax', 'Client\TaxCategoryController');
        Route::resource('taxRate', 'Client\TaxRateController');
        Route::resource('addon', 'Client\AddonSetController');
        Route::post('addonoption/delete', 'Client\AddonSetController@deleteAddonOption')->name('addonoption_delete');
        Route::resource('payment', 'Client\PaymentController');
        Route::resource('accounting', 'Client\AccountController');
        Route::get('vendor/filterdata', 'Client\VendorController@getFilterData')->name('vendor.filterdata');
        Route::POST('vendor/assignManager', 'Client\VendorController@assignManager')->name('assign.manager');
        Route::POST('vendor/importglobalproducts', 'Client\VendorController@importGlobalProducts')->name('import.global.product');
        Route::post('vendor/status/update', 'Client\VendorController@postUpdateStatus')->name('vendor.status');
        Route::get('user/filterdata', 'Client\UserController@getFilterData')->name('user.filterdata');

        Route::get('vendor-payment-report', 'Client\VendorController@vendorPaymentReport')->name('vendorPaymentReport');
        Route::post('vendor-report-export', 'Client\VendorController@vendorReportExport')->name('vendorReportExport');

        Route::resource('vendor', 'Client\VendorController');
        Route::get('vendor/categories/{id}', 'Client\VendorController@vendorCategory')->name('vendor.categories');
        Route::get('getInvetoryToken', 'Client\VendorController@getInvetoryToken')->name('getInvetoryToken');
        Route::post('vendor/search/customer', 'Client\VendorController@searchUserForPermission')->name('searchUserForPermission');
        Route::post('vendor/permissionsForUserViaVendor', 'Client\VendorController@permissionsForUserViaVendor')->name('permissionsForUserViaVendor');
        Route::DELETE('vendor/vendor-permission-del/{id}', 'Client\VendorController@userVendorPermissionDestroy')->name('user.vendor.permission.destroy');
        Route::get('vendor/catalogs/{id}', 'Client\VendorController@vendorCatalog')->name('vendor.catalogs');
        Route::get('vendor/dashboard/{id}', 'Client\VendorController@vendorDashboard')->name('vendor.dashboard');
        Route::get('vendor/product/export/{id}', 'Client\VendorController@vendorProductExport')->name('vendor.product.export');
        Route::get('vendor/product/list/{id}', 'Client\VendorController@VendorProductFilter')->name('vendor.product');
        Route::get('seller/product/list/{id}', 'Client\SellerController@SellerProductFilter')->name('seller.product');
        Route::get('global/product/list', 'Client\VendorController@VendorGlobalProductFilter')->name('vendor.global.product');
        Route::get('vendor/inventory-import/{id}', 'Client\VendorController@getInventoryImport')->name('get.inventory.import');
        Route::post('vendor/get-inventory-store-products', 'Client\VendorController@getInventoryStoreProducts')->name('get.inventory.store.products');
        Route::post('vendor/post-inventory-store-products', 'Client\VendorController@postInventoryStoreProducts')->name('post.inventory.store.products');
        Route::post('vendor/get-inventory-category-products', 'Client\VendorController@getInventoryCategoryListProducts')->name('get.inventory.category.products');
        Route::get('vendor/payout/{id}', 'Client\VendorController@vendorPayout')->name('vendor.payout');
        Route::get('vendor/payout/filter/{id}', 'Client\VendorController@payoutFilter')->name('vendor.payout.filter');
        Route::post('vendor/payout/create/{id}', 'Client\VendorController@vendorPayoutCreate')->name('vendor.payout.create');
        Route::post('vendor/saveConfig/{id}', 'Client\VendorController@updateConfig')->name('vendor.config.update');
        Route::post('vendor/info/{id}', 'Client\VendorController@updateVendorInfo')->name('vendor.config.additioninfo');
        Route::post('vendor/saveConfig/profile/{id}', 'Client\VendorController@updateVendorConfigProfile')->name('vendor.config.update.profile');
        Route::post('vendor/social/media/urls', 'Client\VendorController@updateVendorSocialMediaUrls')->name('vendor.social.media.urls');
        Route::post('vendor/social/media/delete', 'Client\VendorController@deleteVendorSocialMediaUrl')->name('vendor.social.media.delete');
        Route::post('vendor/saveLocation/{id}', 'Client\VendorController@updateLocation')->name('vendor.config.pickuplocation');
        Route::post('vendor/activeCategory/{id}', 'Client\VendorController@activeCategory')->name('vendor.category.update');
        Route::post('vendor/addCategory/{id}', 'Client\TableBookingController@storeCategory')->name('vendor.addCategory');
        Route::get('vendor/vendor_specific_categories/{id}', 'Client\VendorController@vendor_specific_categories')->name('vendor.specific_categories');
        Route::post('vendor/saveCronStatusForServiceArea/{id}', 'Client\VendorController@updateCronStatusForServiceArea')->name('vendor.serviceArea.cron.update');
        Route::post('vendor/updateCategory/{id}', 'Client\TableBookingController@updateCategory')->name('vendor.updateCategory');
        Route::get('vendor/table/category/edit', 'Client\TableBookingController@editCategory')->name('vendor_table_category_edit');
        Route::get('vendor/table/number/edit', 'Client\TableBookingController@editTable')->name('vendor_table_edit');
        Route::post('vendor/category/delete/{id}', 'Client\TableBookingController@destroyCategory')->name('vendor.category.delete');
        Route::post('vendor/addTable/{id}', 'Client\TableBookingController@storeTable')->name('vendor.addTable');
        Route::post('vendor/updateTable/{id}', 'Client\TableBookingController@updateTable')->name('vendor.updateTable');
        Route::post('vendor/table/delete/{id}', 'Client\TableBookingController@destroyTable')->name('vendor.table.delete');
        Route::post('vendor/parentStatus/{id}', 'Client\VendorController@checkParentStatus')->name('category.parent.status');
        Route::get('calender/data/{id}', 'Client\VendorSlotController@returnJson')->name('vendor.calender.data');

        Route::get('seller/filterdata', 'Client\SellerController@getFilterData')->name('seller.filterdata');
        Route::resource('seller', 'Client\SellerController');
        Route::get('seller/catalogs/{id}', 'Client\SellerController@sellerCatalog')->name('seller.catalogs');

        Route::resource('pincode', 'Client\PincodeController');

        Route::get('pincodeData', 'Client\PincodeController@pincodeData')->name('pincode.pincodeData');

        Route::resource('delivery-slot', 'Client\DeliverySlotController');

        Route::get('calender/pickup/data/{id}', 'Client\Laundry\PickupSlotController@returnJson')->name('vendor.calender.pickup'); // Added by Ovi
        Route::post('calender/pickup/slot/{id}', 'Client\Laundry\PickupSlotController@store')->name('vendor.pickup.saveSlot'); // Added by Ovi
        Route::post('calender/pickup/updateSlot/{id}', 'Client\Laundry\PickupSlotController@update')->name('vendor.pickup.updateSlot'); // Added by Ovi
        Route::post('calender/pickup/deleteSlot/{id}', 'Client\Laundry\PickupSlotController@destroy')->name('vendor.pickup.deleteSlot'); // Added by Ovi

        Route::get('calender/dropoff/data/{id}', 'Client\Laundry\DropoffSlotController@returnJson')->name('vendor.calender.dropoff'); // Added by Ovi
        Route::post('calender/dropoff/slot/{id}', 'Client\Laundry\DropoffSlotController@store')->name('vendor.dropoff.saveSlot'); // Added by Ovi
        Route::post('calender/dropoff/updateSlot/{id}', 'Client\Laundry\DropoffSlotController@update')->name('vendor.dropoff.updateSlot'); // Added by Ovi
        Route::post('calender/dropoff/deleteSlot/{id}', 'Client\Laundry\DropoffSlotController@destroy')->name('vendor.dropoff.deleteSlot'); // Added by Ovi


        Route::post('vendor/slot/{id}', 'Client\VendorSlotController@store')->name('vendor.saveSlot');
        Route::post('vendor/updateSlot/{id}', 'Client\VendorSlotController@update')->name('vendor.updateSlot');
        Route::post('vendor/deleteSlot/{id}', 'Client\VendorSlotController@destroy')->name('vendor.deleteSlot');
        Route::post('vendor/importCSV', 'Client\VendorController@importCsv')->name('vendor.import');
        Route::get('vendor/export/CSV', 'Client\VendorController@export')->name('vendor.export');
        Route::post('vendor/serviceArea/{vid}', 'Client\ServiceAreaController@store')->name('vendor.serviceArea');
        Route::post('vendor/editArea/{vid}', 'Client\ServiceAreaController@edit')->name('vendor.serviceArea.edit');
        Route::post('vendor/updateArea/{id}', 'Client\ServiceAreaController@update');
        Route::post('vendor/updateAreaStatusForSlot/{id}', 'Client\ServiceAreaController@updateActiveStatusForSlot');
        Route::post('vendor/deleteArea/{vid}', 'Client\ServiceAreaController@destroy')->name('vendor.serviceArea.delete');
        Route::post('draw-circle-with-radius/{vid}', 'Client\ServiceAreaController@drawCircleWithRadius')->name('draw.circle.with.radius');
        Route::resource('order', 'Client\OrderController');
        Route::post('orders/filter', 'Client\OrderController@postOrderFilter')->name('orders.filter');
        Route::get('orders/product_faq/{product_id}', 'Client\OrderController@viewProductForm')->name('orders.product_faq');
        Route::get('order/return/{status}', 'Client\OrderController@returnOrders')->name('backend.order.returns');
        Route::post('order/return-filter', 'Client\OrderController@returnOrderFilter')->name('backend.order.returns.filter');
        Route::get('rescheduled-orders', 'Client\OrderController@rescheduledOrders')->name('rescheduled.orders'); //Added By Ovi
        Route::get('order/return-modal/get-return-product-modal', 'Client\OrderController@getReturnProductModal')->name('get-return-product-modal');
        Route::post('order/update-product-return-client', 'Client\OrderController@updateProductReturn')->name('update.order.return.client');
        Route::get('order/{order_id}/{vendor_id}', 'Client\OrderController@getOrderDetail')->name('order.show.detail');
        Route::get('order-edit/{order_id}/{vendor_id}', 'Client\OrderController@getOrderDetailEdit')->name('order.edit.detail');
        Route::post('order/update/product/price', 'Client\OrderController@updateOrderProductPriceByVendor')->name('update.product.price');
        Route::post('order/updateStatus', 'Client\OrderController@changeStatus')->name('order.changeStatus');
        Route::post('upload/lawyer-document', 'Client\OrderController@uploadLawyerDocuments')->name('lawyer.submitDocument');
        Route::post('order/updateVendorProductStatus', 'Client\OrderController@changeVendorProductStatus')->name('order.changeVendorProductStatus');
        Route::post('order/create-dispatch-request', 'Client\OrderController@createDispatchRequest')->name('create.dispatch.request'); # create dispatch request
        Route::resource('customer', 'Client\UserController');
        Route::get('customer/account/{user}/{action}', 'Client\UserController@deleteCustomer')->name('customer.account.action');
        Route::get('customer/edit/{id}', 'Client\UserController@newEdit')->name('customer.new.edit');
        Route::post('customer/import', 'Client\UserController@importCsv')->name('customer.import');
        Route::post('customer/custom/search', 'Client\UserController@customSearch')->name('customer.customSearch');
        Route::post('customer/pay-receive', 'Client\UserController@payReceive')->name('customer.pay.receive');
        Route::post('order/updateReport', 'Client\OrderController@uploadReport')->name('order.upload.report');
        Route::get('orderReport/delete/{id}', 'Client\OrderController@deleteReport')->name('order.report.delete');
        Route::post('order/delay_time', 'Client\OrderController@addExtraPrepTimeToOrder')->name('order.delay_time');
        Route::post('order/upload/documents/{order_id}/{vendor_id}', 'Client\OrderController@orderDocument')->name('orderDocument');
        Route::get('order/delete/documents/{id}', 'Client\OrderController@deleteDocument')->name('deleteDocument');


        Route::post('admin/company', 'Client\CompanyController@store')->name('company.add');
        Route::get('admin/company', 'Client\CompanyController@index')->name('company.getList');
        Route::post('admin/deleteCompany', 'Client\CompanyController@destroy')->name('company.delete');
        Route::post('admin/editCompany', 'Client\CompanyController@edit')->name('company.edit');
        Route::post('admin/updateCompany/{id}', 'Client\CompanyController@update')->name('company.update');

        Route::get('orders/getBlockchainOrderDetail', 'Client\OrderController@getBlockchainOrderDetail')->name('orders.getBlockchainOrderDetail');

        // Admin Service Area Routes
        Route::post('admin/serviceArea', 'Client\AdminServiceAreaController@store')->name('admin.serviceArea');
        Route::get('admin/serviceArea', 'Client\AdminServiceAreaController@index')->name('admin.serviceArea.index');
        Route::post('admin/deleteArea', 'Client\AdminServiceAreaController@destroy')->name('admin.serviceArea.delete');
        Route::post('admin/editArea', 'Client\AdminServiceAreaController@edit')->name('admin.serviceArea.edit');
        Route::post('admin/updateArea/{id}', 'Client\AdminServiceAreaController@update');

        Route::get('rental-return-modal/get-rental-return-product-modal', 'Client\OrderController@getRentalReturnProductModal')->name('get-rental-return-product-modal');
        Route::post('order/update-product-rental-return-client', 'Client\OrderController@updateProductRentalReturn')->name('update.order.rental.return.client');

        Route::put('newUpdate/edit/{id}', 'Client\UserController@newUpdate')->name('customer.new.update');
        Route::put('profile/{id}', 'Client\UserController@updateProfile')->name('client.profile.update');
        Route::post('password/update', 'Client\UserController@changePassword')->name('cl.password.update');
        Route::post('customer/change/status', 'Client\UserController@changeStatus')->name('customer.changeStatus');
        Route::get('customer/wallet/transactions', 'Client\UserController@filterWalletTransactions')->name('customer.filterWalletTransactions');
        Route::get('customer/export/export', 'Client\UserController@export')->name('customer.export');
        Route::resource('product', 'Client\ProductController');
        Route::post('product/updateActions', 'Client\ProductController@updateActions')->name('product.update.action');   # update all product actions
        Route::post('product/importCSVQrCode', 'Client\ProductController@importCsvQrcode')->name('qrcode.import');
        Route::post('product/importCSV', 'Client\ProductController@importCsv')->name('product.import');
        Route::post('product/validate', 'Client\ProductController@validateData')->name('product.validate');
        Route::post('product/sku/validate', 'Client\ProductController@validateSku')->name('product.sku.validate');
        Route::get('product/add/{vendor_id}', 'Client\ProductController@create')->name('product.add');
        Route::post('product/getImages', 'Client\ProductController@getImages')->name('productImage.get');
        Route::get('product/import-error/{id}', 'Client\ProductImportController@importErrorLogs')->name('productImport.error');
        Route::post('product/deleteVariant', 'Client\ProductController@deleteVariant')->name('product.deleteVariant');
        Route::post('product/images', 'Client\ProductController@images')->name('product.images');
        Route::post('product/translation', 'Client\ProductController@translation')->name('product.translation');
        Route::post('product/variantRows', 'Client\ProductController@makeVariantRows')->name('product.makeRows');
        Route::post('product/getVariant', 'Client\ProductController@getProductVariant')->name('product.getVariant');
        Route::post('product/updateRolePrice', 'Client\ProductController@updateRolePrice')->name('product.updateRolePrice');
        Route::post('product/getRolePrice', 'Client\ProductController@getRolePrice')->name('product.getRolePrice');
        Route::post('product/variantImage/update', 'Client\ProductController@updateVariantImage')->name('product.variant.update');
        Route::get('product/image/delete/{pid}/{id}', 'Client\ProductController@deleteImage')->name('product.deleteImg');
        Route::resource('loyalty', 'Client\LoyaltyController');
        Route::post('loyalty/changeStatus', 'Client\LoyaltyController@changeStatus')->name('loyalty.changeStatus');
        Route::post('loyalty/getRedeemPoints', 'Client\LoyaltyController@getRedeemPoints')->name('loyalty.getRedeemPoints');
        Route::post('loyalty/setRedeemPoints', 'Client\LoyaltyController@setRedeemPoints')->name('loyalty.setRedeemPoints');
        Route::post('loyalty/setLoyaltyCheck', 'Client\LoyaltyController@setLoyaltyCheck')->name('loyalty.setLoyaltyCheck');
        Route::resource('celebrity', 'Client\CelebrityController');
        Route::post('celebrity/changeStatus', 'Client\CelebrityController@changeStatus')->name('celebrity.changeStatus');
        Route::post('celebrity/getBrands', 'Client\CelebrityController@getBrandList')->name('celebrity.getBrands');
        Route::resource('wallet', 'Client\WalletController');
        Route::resource('promocode', 'Client\PromocodeController');
        Route::resource('payoption', 'Client\PaymentOptionController');
        Route::resource('shipoption', 'Client\ShippingOptionController');
        Route::resource('deliveryoption', 'Client\DeliveryOptionController');
        Route::resource('verifyoption', 'Client\VerificationController');
        Route::post('delivery/dunzo', 'Client\DeliveryOptionController@dunzo')->name('delivery.dunzo');
        Route::post('delivery/d4b_dunzo', 'Client\DeliveryOptionController@d4b_dunzo')->name('delivery.d4b_dunzo');
        Route::post('delivery/roadie', 'Client\DeliveryOptionController@roadie')->name('delivery.roadie');
        Route::post('delivery/ahoy', 'Client\DeliveryOptionController@ahoy')->name('delivery.ahoy');
        Route::post('delivery/last_mile_delivery', 'Client\DeliveryOptionController@last_mile_delivery')->name('delivery.last_mile_delivery');
        Route::resource('tools', 'Client\ToolsController');
        Route::post('tools/copy-catalog', 'Client\ToolsController@storeData')->name('tools.storeData');
        Route::get('database-logs', 'Client\ToolsController@databaseAuditingLogs')->name('databaseAuditingLogs'); // Added By Ovi
        Route::get('database-log/{table_name}', 'Client\ToolsController@singleDatabaseAuditingLogs')->name('singleDatabaseAuditingLogs'); // Added By Ovi
        Route::post('tools/tax', 'Client\ToolsController@taxCopy')->name('tools.taxCopy');
        Route::post('tool/uploadImage', 'Client\ToolsController@uploadImage')->name('tools.uploadImage');
        Route::post('updateAll', 'Client\PaymentOptionController@updateAll')->name('payoption.updateAll');
        Route::post('shippment/updateAll', 'Client\ShippingOptionController@updateAll')->name('shipoption.updateAll');
        Route::post('shippo/updateAll', 'Client\ShippoController@updateAll')->name('shippo.updateAll');
        Route::post('kwikapi/updateAll', 'Client\DeliveryOptionController@updateKwikapi')->name('kwikapi.updateAll');
        Route::post('payoutUpdateAll', 'Client\PaymentOptionController@payoutUpdateAll')->name('payoutOption.payoutUpdateAll');
        Route::post('shipengine/updateAll', 'Client\DeliveryOptionController@updateShipEngine')->name('shipengine.updateAll');
        Route::post('borzoe/updateAll', 'Client\DeliveryOptionController@updateBorzoe')->name('borzoe.updateAll');
        Route::get('borzoe', 'Client\BorzoeDeliveryController@borzoe')->name('borzoe');
        Route::get('borzoe/delivery', 'Client\BorzoeDeliveryController@borzoeDelivery')->name('borzoeDelivery');
        Route::resource('measurement', 'Client\ProductMeasurmentController');
        Route::post('measurement/storeData', 'Client\ProductMeasurmentController@storeData')->name('measurement.storeData');

        Route::resource('inquiry', 'Client\ProductInquiryController');
        Route::get('inquiry/filter', [ProductInquiryController::class, 'show'])->name('inquiry.filter');

        Route::get('subscription/plans/user', 'Client\SubscriptionPlansUserController@getSubscriptionPlans')->name('subscription.plans.user');
        Route::post('subscription/plan/save/user/{slug?}', 'Client\SubscriptionPlansUserController@saveSubscriptionPlan')->name('subscription.plan.save.user');
        Route::get('subscription/plan/edit/user/{slug}', 'Client\SubscriptionPlansUserController@editSubscriptionPlan')->name('subscription.plan.edit.user');
        Route::get('subscription/plan/delete/user/{slug}', 'Client\SubscriptionPlansUserController@deleteSubscriptionPlan')->name('subscription.plan.delete.user');
        Route::post('subscription/plan/updateStatus/user/{slug}', 'Client\SubscriptionPlansUserController@updateSubscriptionPlanStatus')->name('subscription.plan.updateStatus.user');
        Route::post('show/subscription/plan/customer', 'Client\SubscriptionPlansUserController@showSubscriptionPlanCustomer')->name('show.subscription.plan.customer');
        Route::get('subscription/plans/vendor', 'Client\SubscriptionPlansVendorController@getSubscriptionPlans')->name('subscription.plans.vendor');
        Route::post('subscription/plan/save/vendor/{slug?}', 'Client\SubscriptionPlansVendorController@saveSubscriptionPlan')->name('subscription.plan.save.vendor');
        Route::get('subscription/plan/edit/vendor/{slug}', 'Client\SubscriptionPlansVendorController@editSubscriptionPlan')->name('subscription.plan.edit.vendor');
        Route::get('subscription/plan/delete/vendor/{slug}', 'Client\SubscriptionPlansVendorController@deleteSubscriptionPlan')->name('subscription.plan.delete.vendor');
        Route::post('subscription/plan/updateStatus/vendor/{slug}', 'Client\SubscriptionPlansVendorController@updateSubscriptionPlanStatus')->name('subscription.plan.updateStatus.vendor');
        Route::post('subscription/plan/updateOnRequest/vendor/{slug}', 'Client\SubscriptionPlansVendorController@updateSubscriptionPlanOnRequest')->name('subscription.plan.updateOnRequest.vendor');

        Route::get('vendor/subscription/plans/{id}', 'Client\VendorController@getSubscriptionPlans')->name('vendor.subscription.plans');
        Route::get('vendor/subscription/select/{slug}', 'Client\VendorSubscriptionController@selectSubscriptionPlan')->name('vendor.subscription.plan.select');
        Route::post('vendor/subscription/purchase/{id}/{slug}', 'Client\VendorSubscriptionController@purchaseSubscriptionPlan')->name('vendor.subscription.plan.purchase');
        Route::post('vendor/subscription/cancel/{id}/{slug}', 'Client\VendorSubscriptionController@cancelSubscriptionPlan')->name('vendor.subscription.plan.cancel');
        Route::get('vendor/subscription/checkActive/{id}/{slug}', 'Client\VendorSubscriptionController@checkActiveSubscription')->name('vendor.subscription.plan.checkActive');
        Route::any('vendor/subscriptions/filterData', 'Client\VendorSubscriptionController@getSubscriptionsFilterData')->name('vendor.subscriptions.filterData');
        Route::post('vendor/subscription/status/update/{slug}', 'Client\VendorSubscriptionController@updateSubscriptionStatus')->name('vendor.subscription.status.update');

        Route::post('vendor/update_all', 'Client\VendorController@updateActions')->name('vendor.updateall');

        Route::post('subscription/payment/stripe', 'Client\StripeGatewayController@subscriptionPaymentViaStripe')->name('subscription.payment.stripe');
        Route::post('subscription/payment/flutterwave', 'Client\FlutterwaveController@createHash')->name('vendor.subscription.payment');

        // Vendor Payout via gateway
        Route::get('verify/oauth/token/stripe', 'Client\StripeGatewayController@verifyOAuthToken')->name('verify.oauth.token.stripe');
        // Route::get('create/custom/connected-account/stripe/{vendor_id}', 'Client\StripeGatewayController@createCustomConnectedAccount')->name('create.custom.connected-account.stripe');
        Route::post('vendor/payout/stripe', 'Client\StripeGatewayController@vendorPayoutViaStripe')->name('vendor.payout.stripe');
        Route::post('vendor/payout/account/create/pagarme', 'Client\PagarmeController@createVendorPayoutAccount')->name('vendor.payout.account.create.pagarme');

        Route::get('/admin/signup', 'Client\AdminSignUpController@index')->name('admin.signup');
        Route::post('save_fcm_token', 'Client\UserController@save_fcm')->name('client.save_fcm');

        // pickup & delivery
        Route::group(['prefix' => 'vendor/dispatcher'], function () {
            Route::post('updateCreateVendorInDispatch', 'Client\VendorController@updateCreateVendorInDispatch')->name('update.Create.Vendor.In.Dispatch');
            Route::post('updateCreateVendorInDispatchOnDemand', 'Client\VendorController@updateCreateVendorInDispatchOnDemand')->name('update.Create.Vendor.In.Dispatch.OnDemand');
            Route::post('updateCreateVendorInDispatchLaundry', 'Client\VendorController@updateCreateVendorInDispatchLaundry')->name('update.Create.Vendor.In.Dispatch.Laundry');
            Route::post('updateCreateVendorInDispatchAppointment', 'Client\VendorController@updateCreateVendorInDispatchAppointment')->name('update.Create.Vendor.In.Dispatch.Appointment');
        });

        Route::get('vendor-marg-config/{vendor_id}', 'Client\ClientPreferenceController@vendorMargConfig')->name("vendor.margConfig");
        Route::post('vendor-marg-config-update/{vendor_id}', 'Client\ClientPreferenceController@vendorMargConfigUpdate')->name("vendorMargConfig.update");

        Route::get('reports/productperformance', 'Client\ReportController@productPerformance')->name('report.productperformance');
        Route::post('reports/searchproduct', 'Client\ReportController@getOrdersListAjax')->name('report.searchproduct');
        Route::post('reports/productreport', 'Client\ReportController@getProductReportAjax')->name('report.loadproductreport');

        Route::resource('campaign', 'Client\CampaignController');
        Route::get('campaign-push-option', 'Client\CampaignController@GetPushOptions')->name('campaign.pushoptions');
        //Route::get('test-notification', 'Client\CampaignController@testnotification');
        // Route::post('celebrity/changeStatus', 'Client\CelebrityController@changeStatus')->name('celebrity.changeStatus');
        // Route::post('celebrity/getBrands', 'Client\CelebrityController@getBrandList')->name('celebrity.getBrands');

        Route::get('notification', 'Client\UserController@customNotification')->name('customer.notification');
        Route::post('sendnotification', 'Client\UserController@sendNotification')->name('send.notification');
        Route::get('/review/delect/{id}', 'Client\ReviewController@destroy')->name('review.delete');
        Route::resource('review', 'Client\ReviewController');
        Route::get('/get-vendor-rating/{id}', 'Client\ReviewController@getVendorRating')->name('get-vendor-rating-details');
        Route::post('/update-vendor-review', 'Client\ReviewController@update_vendor_rating')->name('update-vendor-review');
        // Cancel order requests routes
        Route::get('cancel-order/requests', 'Client\OrderCancelRequestsController@index')->name('cancel-order.requests');
        Route::get('cancel-order/requests/filter', 'Client\OrderCancelRequestsController@filter')->name('cancel-order.requests.filter');
        Route::post('cancel-order/request/status/update', 'Client\OrderCancelRequestsController@updateStatus')->name('cancel-order.request.status.update');


        Route::get('return/dispatcher_requests', 'Client\RentalProductDispatchReturnController@index')->name('return.dispatcher.form');

        /**Chat resourses */
        //Route::resource('chat', 'Client\ChatController');
        //Route::get('chat/user/{room_id?}', 'Client\ChatController@index')->name("chat.index");
        Route::get('chat/userUser/{room_id?}', 'Client\ChatController@userUserChat')->name("chat.userUserChat");
        Route::get('chat/vendorUser/{room_id?}', 'Client\ChatController@VendorUserChat')->name("chat.VendorUserChat");
        Route::post('chat/startChat', 'Client\ChatController@startChat')->name('chat.startChat');
        Route::get('chat/vendor/{room_id?}', 'Client\ChatController@UserVendorChat')->name("chat.UserVendorChat");
        Route::post('chat/joinChatRoom', 'Client\ChatController@JoinRoom')->name('chat.joinChatRoom');
        Route::post('chat/sendMessage', 'Client\ChatController@sendMessage')->name('chat.sendMessage');
        Route::get('chat/agentUser/{room_id?}', 'Client\ChatController@userAgentChatRoom')->name("chat.userAgentChatRoom");

        Route::post('chat/fetchOrderDetail', 'Client\ChatController@fetchOrderDetail')->name('chat.fetchOrderDetail');
        //static dropoff edit
        Route::get('static-dropoff/index', 'Client\StaticDropoffController@index')->name('static-dropoff.index');
        Route::post('static-dropoff/save', 'Client\StaticDropoffController@store')->name('static-dropoff.create');
        Route::get('static-dropoff/edit', 'Client\StaticDropoffController@edit')->name('static-dropoff.edit');
        Route::delete('static-dropoff/destroy/{id}', 'Client\StaticDropoffController@delete')->name('static-dropoff.destroy');



        Route::post('facilty/store', 'Client\FaciltyController@store')->name('facilty.store');
        Route::post('facilty/update', 'Client\FaciltyController@update')->name('facilty.update');
        Route::get('facilty/edit', 'Client\FaciltyController@show')->name('facilty.edit');
        Route::post('facilty/delete', 'Client\FaciltyController@destroy')->name('facilty.delete');

        // Service Area for Banners Routes
        Route::post('banner/serviceArea', 'Client\ServiceAreaForBannerController@store')->name('banner.serviceArea');
        Route::post('banner/editArea/{id}', 'Client\ServiceAreaForBannerController@edit')->name('banner.serviceArea.edit');
        Route::post('banner/updateArea/{id}', 'Client\ServiceAreaForBannerController@update');
        Route::post('banner/deleteArea/{id}', 'Client\ServiceAreaForBannerController@destroy')->name('banner.serviceArea.delete');
        Route::post('banner/draw-circle-with-radius', 'Client\ServiceAreaForBannerController@drawCircleWithRadius')->name('banner.draw.circle.with.radius');

        // vendor section route
        Route::post('vsection/store', 'Client\VendorSectionController@store')->name('vsection.store');
        Route::post('vsection/deleteSection/{sid}', 'Client\VendorSectionController@destroy')->name('vsection.delete');
        Route::get('vsection/show/{sid}', 'Client\VendorSectionController@show')->name('vsection.edit');
        Route::post('vsection/update', 'Client\VendorSectionController@update')->name('vsection.update');


        // booking route
        Route::post('booking/addBlockSlot', 'Client\Booking\ProductBookingController@addBlockSlot')->name('product-booking.addBlockSlot');   # update all product actions
        Route::get('booking/deleteSlot/{id}', 'Client\Booking\ProductBookingController@deleteSlot')->name('product-booking.deleteSlot');   # update all product actions
        Route::post('booking/updateBlockSlot', 'Client\Booking\ProductBookingController@updateBlockSlot')->name('product-booking.updateBlockSlot');   # update all product actions


        // rental product
        Route::post('rentalVariantRow', 'Client\RentalProductController@getRow')->name('rental-product.variant_row');   # update all product actions
        Route::post('updateProductVariantSet', 'Client\RentalProductController@updateProductVariantSet')->name('rental-product.updateProductVariantSet');   # update all product actions
        Route::get('getScheduleTableData', 'Client\RentalProductController@getScheduleTableData')->name('rental-product.getScheduleTableData');   # update all product actions
        Route::get('getScheduleTableBlockedData', 'Client\RentalProductController@getScheduleTableBlockedData')->name('rental-product.getScheduleTableBlockedData');   # update all product actions

        // vendor cities for home page by harbans :)
        Route::get('vendor_city/index', 'Client\VendorCitiesController@index')->name("vendor_city.index");
        Route::post('vendor_city/store', 'Client\VendorCitiesController@store')->name('vendor_city.store');
        Route::get('vendor_city/show/{id?}', 'Client\VendorCitiesController@show')->name("vendor_city.show");
        Route::post('vendor_city/update', 'Client\VendorCitiesController@update')->name("vendor_city.update");
        Route::get('vendor_city/destroy/{id}', 'Client\VendorCitiesController@destroy')->name('vendor_city.destroy');

        // vendor multi banner for t6 :)
        Route::post('vendor_banner/store', 'Client\VendorMultiBannerController@store')->name("vendor_banner.store");
        Route::get('vendor_banner/destroy/{id}', 'Client\VendorMultiBannerController@destroy')->name("vendor_banner.destroy");

        /** Refer and earn  */
        Route::resource('tier', 'Client\TierController');

        Route::prefix('influencer-user')->group(function () {
            Route::name('influencer-user.')->group(function () {
                Route::resource('influencer-user', 'Client\InfluencerUserController');
                Route::get('getUploadedData', 'Client\InfluencerUserController@getUploadedData')->name('getUploadedData');
                Route::get('getkycData', 'Client\InfluencerUserController@getkycData')->name('getkycData');
                Route::post('approveReject', 'Client\InfluencerUserController@approveReject')->name('approveReject');
            });
        });
        Route::prefix('influencer-refer-earn')->group(function () {


            Route::name('influencer-refer-earn.')->group(function () {
                Route::get('index', 'Client\InfluencerReferAndEarnController@index')->name('index');
                Route::get('create', 'Client\InfluencerReferAndEarnController@create')->name('create');
                Route::get('edit/{id}', 'Client\InfluencerReferAndEarnController@edit')->name('edit');
                Route::post('store', 'Client\InfluencerReferAndEarnController@store')->name('store');
                Route::post('update', 'Client\InfluencerReferAndEarnController@update')->name('update');
                Route::get('list', 'Client\InfluencerReferAndEarnController@userList')->name('list');
                Route::post('update-user-commision', 'Client\InfluencerReferAndEarnController@updateUserCommision')->name('update-user-commision');
                Route::get('editInfluencerUser', 'Client\InfluencerReferAndEarnController@editInfluencerUser')->name('editInfluencerUser');
            });
            Route::prefix('attribute')->group(function () {
                Route::name('attribute-influencer-refer-earn.')->group(function () {
                    Route::get('create', 'Client\InfluencerAttributeController@create')->name('create');
                    Route::get('edit/{id}', 'Client\InfluencerAttributeController@edit')->name('edit');
                    Route::post('store', 'Client\InfluencerAttributeController@store')->name('store');
                    Route::put('update/{id}', 'Client\InfluencerAttributeController@update')->name('update');
                    Route::delete('delete/{id}', 'Client\InfluencerAttributeController@delete')->name('delete');
                });
            });
        });

        /**  Hubspot Create a contact.
         *
         */
        Route::post('/hubspot/create-contact', 'Hubspot\HubspotApiController@create');
        /** end */
        // long term Serive by harbans :)
        Route::post('long_term_service/store', 'Client\LongTermServiceController@store')->name("long_term_service.store");
        Route::get('long_term_service/index/{vendor_id}', 'Client\LongTermServiceController@index')->name("long_term_service.index");
        Route::get('long_term_service/edit/{id}', 'Client\LongTermServiceController@edit')->name('long_term_service.edit');
        Route::get('long_term_service/delete/{id}', 'Client\LongTermServiceController@destroy')->name("long_term_service.destroy");


        /***
         *  Mtn momo payment gateway configation
         */

        Route::post('mtn-mom-api-key', 'Client\PaymentOptionController@MtnmomoApiKey')->name('payoption.mtn_momo_api_key');

        Route::post('long_term_service/updateBooking', 'Client\LongTermServiceController@updateBooking')->name("long_term_service.updateBooking");

        /**
         * Gift Card.
         */
        Route::get('gitcart', 'Client\GiftCard\GiftcardController@index')->name("giftCart.index");
        Route::post('gitcart/store', 'Client\GiftCard\GiftcardController@store')->name("giftCart.store");
        Route::get('gitcart/show/{id}', 'Client\GiftCard\GiftcardController@edit')->name("giftCart.show");
        Route::post('gitcart/update/{id}', 'Client\GiftCard\GiftcardController@update')->name("giftCart.update");
        Route::get('gitcart/delete/{id}', 'Client\GiftCard\GiftcardController@destroy')->name("giftCart.destroy");

        Route::get('account/redeemedcard', [GiftcardController::class, 'redeemedCard'])->name('account.redeemedcard');
        Route::get('giftcard/list/filter', [GiftcardController::class, 'filter'])->name('gift.card.list.filter');

        Route::get('account/usersubscriptions', [SubscriptionPlansUserController::class, 'userSubscriptionReport'])->name('account.userSubscription');
        Route::get('usersubscriptions/list/filter', [SubscriptionPlansUserController::class, 'subscriptionfilter'])->name('subscription.list.filter');

        Route::group(['prefix' => '/attributes'], function () {
            Route::get('index', 'Client\CategoryController@manageAttribute')->name('manage.attribute');
            Route::get('add', 'Client\CategoryController@getAddAttributeForm')->name('manage.attribute.add');
            Route::get('edit/{id}', 'Client\CategoryController@getEditAttributeForm')->name('manage.attribute.edit');
            Route::post('store', 'Client\CategoryController@storeAttributeForm')->name('manage.attribute.store');
            Route::put('update/{id}', 'Client\CategoryController@updateAttributeForm')->name('manage.attribute.update');
            Route::delete('delete/{id}', 'Client\CategoryController@destroyAttribute')->name('manage.attribute.delete');
        });
        Route::group(['middleware' => 'onlysuperadmin', 'prefix' => '/mealSubscription'], function () {
            Route::get('packages', 'Client\MealSubscriptionController@getMealSubscriptionPlans')->name('mealSubscription.plans');
            Route::post('package/save/{slug?}', 'Client\MealSubscriptionController@saveSubscriptionPlan')->name('mealSubscription.plan.save');
            Route::post('package/updateStatus/{slug}', 'Client\MealSubscriptionController@updateSubscriptionPlanStatus')->name('mealSubscription.plan.updateStatus');
            Route::get('package/edit/{slug}', 'Client\MealSubscriptionController@editSubscriptionPlan')->name('mealSubscription.plan.edit');
            Route::get('package/delete/user/{slug}', 'Client\MealSubscriptionController@deleteSubscriptionPlan')->name('mealSubscription.plan.delete');
            Route::group(['prefix' => 'rental-protection/'], function () {
                Route::get('', [RentalProtectionController::class, 'index'])->name('rental.protection');
                Route::match(['put', 'post'], 'store/{id?}', [RentalProtectionController::class, 'store'])->name('rental.protection.store');
                Route::get('{id}/edit', [RentalProtectionController::class, 'edit'])->name('rental.protection.edit');
                Route::delete('delete/{id}', [RentalProtectionController::class, 'delete'])->name('rental.protection.delete');
            });
            Route::group(['prefix' => 'booking-option/'], function () {
                Route::get('', [BookingOptionController::class, 'index'])->name('booking.option');
                Route::match(['put', 'post'], 'store/{id?}', [BookingOptionController::class, 'store'])->name('booking.option.store');
                Route::get('{id}/edit', [BookingOptionController::class, 'edit'])->name('booking.option.edit');
                Route::delete('delete/{id}', [BookingOptionController::class, 'delete'])->name('booking.option.delete');
            });
            Route::group(['prefix' => 'destination/'], function () {
                Route::get('', [DestinationController::class, 'index'])->name('destinations');
                Route::match(['put', 'post'], 'store/{id?}', [DestinationController::class, 'store'])->name('destination.store');
                Route::get('{id}/edit', [DestinationController::class, 'edit'])->name('destination.edit');
                Route::delete('delete/{id}', [DestinationController::class, 'delete'])->name('destination.delete');
            });
        });
    });


    Route::get('/search11', [SearchController::class, 'search']);

    Route::group(['middleware' => 'auth:client', 'prefix' => '/admin'], function () {
        Route::get('/', 'Client\DashBoardController@index')->name('home');
        Route::get('{first}/{second}/{third}', 'Client\RoutingController@thirdLevel')->name('third');
        Route::get('{first}/{second}', 'Client\RoutingController@secondLevel')->name('second');
        Route::get('{any}', 'Client\RoutingController@root')->name('any');
    });

    Route::group(['prefix' => '/gofrugal'], function () {
        Route::get('/', 'Client\GoFrugalController@index')->name('gofrugal.home');
    });
});
