<?php

Route::group(['prefix' => 'v1/v2', 'middleware' => ['ApiLocalization']], function () {
    Route::group(['middleware' => ['dbCheck', 'checkAuth']], function() {

        Route::get('category/{id?}', 'Api\v1\v2\CategoryController@categoryData');
        Route::post('attribute/category/{id?}', 'Api\v1\v2\P2PController@categoryData');
        Route::post('category/filters/{id?}', 'Api\v1\v2\CategoryController@categoryFilters');

        Route::get('vendor/{id?}', 'Api\v1\v2\VendorController@productsByVendor');
        Route::post('vendor/category/list', 'Api\v1\v2\VendorController@postVendorCategoryList');
        Route::get('vendor/{slug1}/{slug2}', 'Api\v1\v2\VendorController@vendorCategoryProducts');
        Route::post('vendor/vendorProductsFilter', 'Api\v1\v2\VendorController@vendorProductsFilter');
        Route::post('vendor/filters/{id?}', 'Api\v1\v2\VendorController@vendorFilters');
        Route::post('vendor/register', 'Api\v1\v2\VendorController@postVendorRegister');
        Route::get('vendor-optimize/{id?}', 'Api\v1\v2\VendorController@productsByVendorOptimize');
        Route::match(['get','post'],'vendor-optimize-category/{id}', 'Api\v1\v2\VendorController@productsByVendorCategoryOptimize');
        Route::post('vendor/vendorProductsFilterOptimize', 'Api\v1\v2\VendorController@vendorProductsFilterOptimize');
        Route::post('homepage', 'Api\v1\v2\HomeController@homepage');
        Route::post('categoriesAll', 'Api\v1\v2\HomeController@categoriesAll');
        Route::post('get_products', 'Api\v1\v2\HomeController@get_spotlight_deals_selected_producst');
        Route::post('search/{type}/{id?}', 'Api\v1\v2\HomeController@globalSearch');

        Route::post('productByVariant/{id}', 'Api\v1\v2\ProductController@getVariantData');

        Route::get('getP2pCategories', 'Api\v1\v2\P2PController@getP2pCategories');
        Route::get('getRentalCategories', 'Api\v1\v2\P2PController@getRentalCategories');

        Route::post('homePageDataV2', 'Api\v1\v2\HomeController@postHomePageDataV2');
        Route::post('get/subcategory/vendor', 'Api\v1\v2\HomeController@getSubcategoryVendor');


        // new cart route
        Route::get('cart/list', 'Api\v1\v2\CartController@index');

        // new cart route
        Route::post('sendInvoiceEmail', 'Api\v1\PickupDeliveryController@sendInvoiceEmail');



//------------------get all data (category, banner, vendor, products) based on category type
        Route::get('getCategoryAllData/{id?}', 'Api\v1\v2\CategoryController@getCategoryAllData');
    });
    Route::group(['middleware' => ['dbCheck','systemAuth']], function() {

    });
});
