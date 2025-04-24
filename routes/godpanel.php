<?php
Route::group(['prefix' => '/godpanel'], function () {
	Route::get('login', function(){return view('godpanel/login');});
	Route::post('login','Godpanel\LoginController@Login')->name('god.login');
	Route::middleware(['middleware' => 'auth:admin'])->group(function () {
        
		Route::resource('client','Godpanel\ClientController');
		Route::resource('map','Godpanel\MapProviderController');
		Route::resource('sms','Godpanel\SmsProviderController');
		Route::resource('language','Godpanel\LanguageController');
		Route::resource('currency','Godpanel\CurrencyController');
		Route::post('delete/client/{id}', 'Godpanel\ClientController@remove');
		Route::get('map/destroy/{id}', 'Godpanel\MapProviderController@destroy');
		Route::get('sms/destroy/{id}', 'Godpanel\SmsProviderController@destroy');
		Route::post('/logout', 'Godpanel\LoginController@logout')->name('god.logout');
		Route::get('dashboard','Godpanel\DashBoardController@index')->name('god.dashboard');
		
		Route::post('migrateDefaultData/{id}', 'Godpanel\ClientController@migrateDefaultData')->name('client.migrateDefaultData');
		Route::post('singleVendorSetting/{id}', 'Godpanel\ClientController@singleVendorSetting')->name('client.update_single_vendor');

		/**socket url update */
		Route::post('socketUpdate/{id}', 'Godpanel\ClientController@socketUrl')->name('client.socketUpdate');
		Route::post('socketUpdateAction/{id}', 'Godpanel\ClientController@socketUpdateAction')->name('client.socketUpdateAction');
		/**socket url update */

		Route::post('exportDb/{dbname}', 'Godpanel\ClientController@exportDb')->name('client.exportdb');
        

		Route::get('dashboard', 'Godpanel\DashBoardController@dashboard')->name('dashboard');

		Route::get('billingplans', 'Godpanel\billingController@getBillingPlans')->name('billingplans');
		Route::post('billingplans/save/{slug?}', 'Godpanel\billingController@saveBillingPlan')->name('billingplans.save');
		Route::get('billingplans/edit/{slug}', 'Godpanel\billingController@editBillingPlan')->name('billingplans.edit');
        Route::post('billingplans/updateStatus/{slug}', 'Godpanel\billingController@updateBillingPlanStatus')->name('billingplans.updateStatus');

		Route::get('billingtimeframes', 'Godpanel\billingController@getBillingTimeframe')->name('billingtimeframes');
		Route::post('billingtimeframes/save/{slug?}', 'Godpanel\billingController@saveBillingTimeframe')->name('billingtimeframes.save');
		Route::get('billingtimeframes/edit/{slug}', 'Godpanel\billingController@editBillingTimeframe')->name('billingtimeframes.edit');
        Route::post('billingtimeframes/updateStatus/{slug}', 'Godpanel\billingController@updateBillingTimeframeStatus')->name('billingtimeframes.updateStatus');

		Route::get('billingpricings', 'Godpanel\billingController@getBillingPricing')->name('billingpricing');
		Route::post('billingpricings/save/{slug?}', 'Godpanel\billingController@saveBillingPricing')->name('billingpricing.save');
		Route::get('billingpricings/edit/{slug}', 'Godpanel\billingController@editBillingPricing')->name('billingpricing.edit');
        Route::post('billingpricings/updateStatus/{slug}', 'Godpanel\billingController@updateBillingPricingStatus')->name('billingpricing.updateStatus');

		Route::get('democlients', 'Godpanel\billingController@getDemoClientList')->name('democlients');
		Route::get('clientsubscriptions', 'Godpanel\billingController@getClientSubscription')->name('clientsubscription');
		Route::get('clientsubscriptions/add', 'Godpanel\billingController@addClientSubscription')->name('clientsubscription.add');
		Route::post('clientsubscriptions/save', 'Godpanel\billingController@saveClientSubscription')->name('clientsubscription.save');
		Route::get('clientsubscriptions/filter', 'Godpanel\billingController@filter')->name('clientsubscription.filter');
		Route::get('clientsubscriptions/edit/{slug}', 'Godpanel\billingController@editClientSubscription')->name('clientsubscription.edit');
		Route::post('clientsubscriptions/update/{slug}', 'Godpanel\billingController@updateClientSubscription')->name('clientsubscription.update');
		Route::get('getclientbillingdetails/{clientid}/{plantype}', 'Godpanel\billingController@getclientbillingdetails')->name('getclientbillingdetails.details');
		Route::get('deleteclientsubscription/{slug}', 'Godpanel\billingController@deleteClientSubscription')->name('clientsubscription.delete');
		Route::get('clientsubscriptions/editpayment/{slug}', 'Godpanel\billingController@editSubscriptionPayment')->name('clientsubscription.editpayment');
		Route::post('clientsubscriptions/updatepayment', 'Godpanel\billingController@updateClientSubscriptionPayment')->name('clientsubscription.updatepayment');

		/** 24 june 2022  chatsocket crud */
		Route::get('chatsocket', 'Godpanel\chatSocketController@chatsocket')->name('chatsocket');
		Route::post('chatsocket/save/{id?}', 'Godpanel\chatSocketController@chatsocketSave')->name('chatsocket.save');
		Route::get('chatsocket/edit/{id}', 'Godpanel\chatSocketController@editchatsocket')->name('chatsocket.edit');
		Route::post('chatsocket/upDateSocket/{id}', 'Godpanel\chatSocketController@upDateSocket')->name('chatsocket.upDateSocket');
        Route::post('chatsocket/upDateSocketStatus/{id}', 'Godpanel\chatSocketController@upDateSocketStatus')->name('chatsocket.upDateSocketStatus');
		Route::get('chatsocket/deleteSocketUrl/{id}', 'Godpanel\chatSocketController@deleteSocketUrl')->name('chatsocket.delete');
        Route::get('/lumen','Godpanel\DashBoardController@lumen')->name('lumen');
        Route::post('/lumen-client-save','Godpanel\DashBoardController@lumenClientSave')->name('lumen-client-save');
        Route::post('/enable-lumen-service','Godpanel\DashBoardController@enableLumenService')->name('enable-lumen-service');
        Route::post('/enable-campaign-service','Godpanel\DashBoardController@enableCampaignService')->name('enable-campaign-service');
		/** */
		
	});
});