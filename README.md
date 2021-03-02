[![Maintainability](https://api.codeclimate.com/v1/badges/8c6bc9b387b44de574d4/maintainability)](https://codeclimate.com/github/sergeevpasha/laravel-dellin/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/8c6bc9b387b44de574d4/test_coverage)](https://codeclimate.com/github/sergeevpasha/laravel-dellin/test_coverage)
[![CodeFactor](https://www.codefactor.io/repository/github/sergeevpasha/laravel-dellin/badge)](https://www.codefactor.io/repository/github/sergeevpasha/laravel-dellin)
[![Generic badge](https://img.shields.io/badge/PHP-^7.4.*-blue.svg)](https://www.php.net)
[![Generic badge](https://img.shields.io/badge/Laravel-^8.27-red.svg)](https://laravel.com)

# Laravel Dellin API Wrapper
Allows you to:
* Find a City by query string
* Find a Street by City ID and query string
* Find Terminals in the City by City ID
* Calculate a delivery

## Pre-requirements
You need to get Dellin API key, login and password.
Key can be obtained at https://dev.dellin.ru/registration

## Installation
<pre>composer require sergeevpasha/laravel-dellin</pre>

## Configuration
This package has a few configuration values:
<pre>
'key'        => env('DELLIN_KEY', null),
'login'      => env('DELLIN_LOGIN', null),
'password'   => env('DELLIN_PASSWORD', null),
'prefix'     => 'dellin',
'middleware' => ['web']
</pre>
If you only need to use DellinClient, you may completely skip this configuration. Otherwise, you can use default options and specify some data in .env file:
* DELLIN_KEY
* DELLIN_LOGIN
* DELLIN_PASSWORD

To make full use of predefined routes, you will need to publish config:
<pre>
php artisan vendor:publish --provider="SergeevPasha\Dellin\Providers\DellinServiceProvider" --tag="config"
</pre>
Now you can change routes prefix and middleware to whatever you need

### Use Case #1
After installing you may just import the client
<pre>use SergeevPasha\Dellin\Libraries\DellinClient;</pre>
Firstly let's initialize our client
<pre>
$client = new DellinClient('key');
/* 
    You may also authorize your user and get a session ID.
    You may use it in getPrice() method to adjust prices (Authorized users have lower prices)
*/
$session = client->authorize('login', 'password);
</pre>
Now we can use these methods:
<pre>
$client->findCity(string $query)
$client->findCityStreet(int $city, string $query)
$client->getCityTerminals(int $city, bool $arrival = true)
/* This one requires a Delivery Object, see next to see how to build it */
$client->getPrice(Delivery $delivery)
</pre>
## Delivery Object
To build a Delivery object you will need to pass an array to fromArray() method just like that:<br>
<pre>
Delivery::fromArray([
    'session_id'             => '12345', // User Session ID, Not required
    'delivery_type'          => '1', // Delivery Type, see available Delivery Types below
    'arrival_shipping_type'  => '1', // Shipping Type, see available Shipping Types below
    /* Only one of the following is required */
    'arrival_terminal_id'    => '123'
    'arrival_address_id'     => '123'
    'arrival_street_code'    => '123'
    'arrival_city_code'      => '123'
    /* --- */
    /* Next lines are NOT required */
    'arrival_worktime_start' => '12:30', // Time format HH:MM
    'arrival_worktime_end'   => '13:00',
    'arrival_break_start'    => '15:30',
    'arrival_break_end'      => '16:00',
    'arrival_exact_time'     => '1', // Boolean flag to show that there should be an exact time pickup a cargo
    'arrival_freight_lift'   => '1', // Boolean flag, specify if there is a lift on arrival location
    'arrival_to_floor'       => '10', // Floor level to deliver
    'arrival_carry'          => '100', // Meters to deliver
    'arrival_requirements'   => [
        '0x92fce2284f000b0241dad7c2e88b1655',
    ], // You can get these codes from getSpecialRequirements() method
    'derival_produce_date'   => '2020-10-10' // Date, when this order should be done (YYYY-MM-DD)
    /* Below are the same specs, but for the derival */
    'derival_shipping_type'  => '1', // Shipping Type, see available Shipping Types below
    'derival_terminal_id'    => '123'
    'derival_address_id'     => '123'
    'derival_street_code'    => '123'
    'derival_city_code'      => '123'
    'derival_worktime_start' => '12:30', // Time format HH:MM
    'derival_worktime_end'   => '13:00',
    'derival_break_start'    => '15:30',
    'derival_break_end'      => '16:00',
    'derival_exact_time'     => '1', // Boolean flag to show that there should be an exact time pickup a cargo
    'derival_freight_lift'   => '1', // Boolean flag, specify if there is a lift on arrival location
    'derival_to_floor'       => '10', // Floor level to deliver
    'derival_carry'          => '100', // Meters to deliver
    'derival_requirements'   => [
        '0x92fce2284f000b0241dad7c2e88b1655',
    ]
    'packages'               => [
        '0x838fc70baeb49b564426b45b1d216c15'
    ], // You can get these codes from getAvailablePackages() method
    'ac_docs_send'           => '1', // Boolean flag to send accompanying documents
    'ac_docs_return'         => '1', // Boolean flag to return accompanying documents
    'requester_role'         => '1', // Requester Role, see available Requester Roles below
    'requester_uid'          => 'xxxx-xxxx' // Who is ordering delivery. You can get this UID from getCounterparties() method
    'cargo_quantity'         => '1', // Amount of packages to ship
    'cargo_length'           => '10', // Max package Length in Meters
    'cargo_height'           => '10', // Max package Height in Meters
    'cargo_width'            => '10', // Max package Width in Meters
    'cargo_weight'           => '10', // Weight in KG
    'cargo_total_volume'     => '0.05, // Volume in M<sup>3</sup>
    'cargo_total_weight'     => '10', // Total Weight in KG
    /* Next lines are NOT required */
    'cargo_oversized_weight' => '10', // Total Weight of oversized packages
    'cargo_oversized_volume' => '10', // Total Volume of oversized packages
    /* Only one is required */
    'cargo_freight_uid'      => 'xxxx-xxx', // Freight UID
    'cargo_freight_name'     => 'mybook', // Freight Name
    /* --- */
    'cargo_hazard_class'     => '1.1' // Cargo hazard level. Default and recommended is 0, unsless you know what you are doing
    'insurance_value'        => '100000', // Insurance value
    'insurance_term'         => '1', // Boolean flag, that specifies that you need to insure delivery time too
    'payment_city'           => '7700000000000000000000000' // KLADR Code of payment city
    'payment_type'           => '1', // Payment type, see below
])
</pre>

## Available Delivery Types
<pre>
    AUTO = 0
    EXPRESS = 1
    LETTER = 2
    AVIA = 3
    SMALL = 4
</pre>

## Available Payment Types
<pre>
    CASH = 0
    NONCASH = 1
</pre>

## Available Requester Roles
<pre>
    SENDER = 0
    RECEIVER = 1
    PAYER = 2
    THIRD = 3
</pre>

## Available Shipping Types
<pre>
    ADDRESS = 0
    TERMINAL = 1
</pre>

### Use Case #2

There are some predefined routes, that will be merged with your routes as well. You may check it by using
<code>php artisan routes:list</code>
It actually exposes the same methods to the routes, so it should be pretty clear on how to use it.
For more information on how to use it, please check out `src/` folder.
