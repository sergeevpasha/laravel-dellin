<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dellin Default configuration
    |--------------------------------------------------------------------------
    |
    | This options must be set in order to use Dellin API.
    | You can obtain an APP KEY by registering here https://dev.dellin.ru/registration
    |
    */

    'key'      => env('DL_KEY', null),
    'login'    => env('DL_LOGIN', null),
    'password' => env('DL_PASSWORD', null),
    'prefix'     => 'dellin',
    'middleware' => ['web'],

];
