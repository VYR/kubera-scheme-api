<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SingleContentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::group([
    'middleware' > ['auth:sanctum'],
    'except' => ['signup']
], function () {
    /**User routes */
    Route::group(['prefix' => 'user'], function() {

        Route::post('/signup', [UserController::class,'signup']);
        Route::post('/login', [UserController::class,'login']);
        Route::post('/update-user-details', [UserController::class,'updateUserDetails']);
        Route::post('/complete-kyc', [UserController::class,'completeKyc']);
        Route::post('/send-otp', [UserController::class,'sendOtpByMobile']);
        Route::post('/verify-otp', [UserController::class,'verifyOtp']);

        Route::post('/get-aadhar-url', [UserController::class,'getAadharUrl']);
        Route::post('/update-bank-details', [UserController::class,'updateBankDetails']);
        Route::post('/update-delivery-address', [UserController::class,'updateDeliveryAddress']);
        Route::post('/add-contact-messages', [UserController::class,'addContactMessages']);

    });

    /**Payment routes */
    Route::group(['prefix' => 'payment'], function() {

        Route::post('/update', [PaymentController::class,'updatePaymentDetails']);
        Route::post('/add', [PaymentController::class,'addPaymentDetails']);
        Route::post('/get-by-user', [PaymentController::class,'getPaymentsByUser']);
        Route::post('/all-users', [PaymentController::class,'getAllPayments']);

    });
    /**Single Content routes */
    Route::group(['prefix' => 'single-content'], function() {

        Route::post('/add', [SingleContentController::class,'addSingleContent']);
        Route::get('/get', [SingleContentController::class,'getSingleContent']);

    });
    /**Admin routes */
    Route::group(['prefix' => 'admin'], function() {

        Route::get('/total-users', [UserController::class,'totalUsers']);

        Route::get('/get-contact-messages', [UserController::class,'totalUsers']);
        Route::get('/update-contact-messages', [UserController::class,'updateContactMessages']);


    });
     /**Call micro services */
     Route::any('{serviceName}/{segment1?}/{segment2?}', [UserController::class,'callMicroServices']);
});

Route::fallback(function () {
    return response()->json(['message'=> 'Invalid Request'],404);
});
