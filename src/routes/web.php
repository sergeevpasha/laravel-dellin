<?php

use Illuminate\Support\Facades\Route;
use SergeevPasha\Dellin\Http\Controllers\EnumController;
use SergeevPasha\Dellin\Http\Controllers\DellinController;
use SergeevPasha\Dellin\Http\Controllers\AuthDellinController;

Route::post('/authorize', [AuthDellinController::class, '__invoke'])
    ->name('dellin.auth');
Route::get('/cities', [DellinController::class, 'queryCity'])
    ->name('dellin.cities');
Route::get('/cities/{city}/terminals', [DellinController::class, 'getTerminals'])
    ->name('dellin.cities.terminals');
Route::get('/cities/{city}/streets', [DellinController::class, 'queryStreet'])
    ->name('dellin.cities.streets');
Route::get('/packages', [DellinController::class, 'getAvailablePackages'])
    ->name('dellin.packages');
Route::get('/services', [DellinController::class, 'getSpecialRequirements'])
    ->name('dellin.services');
Route::get('/counterparties', [DellinController::class, 'getCounterparties'])
    ->name('dellin.counterparties');
Route::post('/calculate', [DellinController::class, 'calculateDeliveryPrice'])
    ->name('dellin.calculate');

Route::get('/deliveryTypes', [EnumController::class, 'deliveryTypes'])
    ->name('dellin.deliveryTypes');
Route::get('/paymentTypes', [EnumController::class, 'paymentTypes'])
    ->name('dellin.paymentTypes');
Route::get('/requesterRoles', [EnumController::class, 'requesterRoles'])
    ->name('dellin.requesterRoles');
Route::get('/shippingTypes', [EnumController::class, 'shippingTypes'])
    ->name('dellin.shippingTypes');
