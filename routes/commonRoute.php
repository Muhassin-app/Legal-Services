<?php

// ADMIN LANGUAGE SWITCH
Route::group(['middleware' => ['domain', 'webAuth'], 'prefix' => '/common'], function () {
    Route::post('chat/sendNotificationToUser', 'Front\ChatDispatcherNotificationController@sendNotificationToUser')->name('chat.sendNotificationToUser');
    Route::get('/s3-sign', 'Front\ChatDispatcherNotificationController@signAws');
});
