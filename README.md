[![Maintainability](https://api.codeclimate.com/v1/badges/8c6bc9b387b44de574d4/maintainability)](https://codeclimate.com/github/sergeevpasha/laravel-dellin/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/8c6bc9b387b44de574d4/test_coverage)](https://codeclimate.com/github/sergeevpasha/laravel-dellin/test_coverage)
[![CodeFactor](https://www.codefactor.io/repository/github/sergeevpasha/laravel-dellin/badge)](https://www.codefactor.io/repository/github/sergeevpasha/laravel-dellin)
[![Generic badge](https://img.shields.io/badge/PHP-^8.0.*-blue.svg)](https://www.php.net)
[![Generic badge](https://img.shields.io/badge/Laravel-^8.27-red.svg)](https://laravel.com)

# Laravel Dellin API Wrapper
Allows you to:
* Find a City by query string
* Find a Street by City ID and query string
* Find Terminals in the City by City ID
* Calculate a delivery
* Find a track (order) by track number

## Pre-requirements
You need to get a Dellin API key. A login and password are required to fetch authenticated data (delivery type, full order details).
Key can be obtained at https://dev.dellin.ru/registration

## Installation
<pre>composer require sergeevpasha/laravel-dellin</pre>

## Configuration
This package has a few configuration values:
<pre>
'key'      => env('DL_KEY', null),
'login'    => env('DL_LOGIN', null),   // phone number: +7XXXXXXXXXX
'password' => env('DL_PASSWORD', null),
'prefix'   => 'dellin',
'middleware' => ['web']
</pre>
Specify the values in your `.env` file:
* `DL_KEY` — API key
* `DL_LOGIN` — phone number used to log in to dellin.ru (`+7XXXXXXXXXX` format)
* `DL_PASSWORD` — password

To make full use of predefined routes, publish the config:
<pre>
php artisan vendor:publish --provider="SergeevPasha\Dellin\Providers\DellinServiceProvider" --tag="config"
</pre>

### Use Case #1 — Track lookup
<pre>
use SergeevPasha\Dellin\Libraries\DellinClient;

$client = app(DellinClient::class); // or new DellinClient(config('dellin.key'))

// Returns a cached session ID (28-day TTL). Requires DL_LOGIN and DL_PASSWORD.
// Session unlocks authenticated fields: delivery type, service kind, etc.
// Returns null if credentials are not configured.
$sessionId = $client->getSession();

// Invalidate the cached session (e.g. after receiving a 401 from the API).
$client->forgetSession();

// Find a track by order/waybill number. Automatically includes session if available.
// A cached session Dellin rejects (HTTP 401) is replaced by a fresh one and the request is retried once.
// An order Dellin does not know (error 717001) comes back with status null and notFound = true.
$track = $client->findByTrackNumber('61389220');

// DellinTrack fields:
// $track->notFound        — Dellin does not know the order (bool)
// $track->status          — order status name (string|null)
// $track->state           — order state code: inway, arrived, finished, declined... (string|null)
// $track->declineReason   — why Dellin declined the order (string|null); isDeclined() checks the state
// $track->link            — tracking URL (string)
// $track->price           — total order cost (float)
// $track->startDate       — dispatch date (Carbon|null)
// $track->receiveDate     — expected delivery date (Carbon|null)
// $track->actualReceiveDate — actual delivery date (Carbon|null)
// $track->warehousing     — paid storage start date (Carbon|null)
// $track->deliveryDays    — estimated delivery days (int|null)
// $track->deliveryType    — DeliveryType enum key: AUTO|EXPRESS|AVIA|LETTER|SMALL (string|null)
//                           Requires authenticated session to be populated.
// $track->derivalIsTerminal / $track->arrivalIsTerminal — bool
// $track->derivalCityCode / $track->arrivalCityCode     — settlement KLADR code (string|null)
// $track->derivalCityId / $track->arrivalCityId         — city of the serving terminal in v3/public/terminals
//                           (int|null), not a cityID of findCity()/findCityByCode()
// $track->derivalCity / $track->arrivalCity             — string|null
// $track->derivalTerminalId / $track->arrivalTerminalId — int|null
// $track->derivalTerminalName / $track->arrivalTerminalName — string|null
// $track->derivalTerminalAddress / $track->arrivalTerminalAddress — string|null
// $track->derivalAddress / $track->arrivalAddress       — street address (string|null)
// $track->isPaid          — bool|null
// $track->statedValue     — declared cargo value (float|null)
</pre>

### Use Case #2 — Price calculation
<pre>
use SergeevPasha\Dellin\Libraries\DellinClient;

$client = app(DellinClient::class);

// Optionally authorize to get personalized prices
$session = $client->authorize();  // uses DL_LOGIN / DL_PASSWORD from config

// Build a Delivery object and get a price
$price = $client->getPrice(Delivery::fromArray([...]));
</pre>

Now we can use these methods:
<pre>
$client->findCity(string $query)
$client->findCityByCode(string $code) // a 25-digit code with a leading zero is sent as 24 digits
$client->getOversizedThresholds() // per service kind; a place above any threshold is oversized
$client->findCityStreet(int $city, string $query)
$client->getCityTerminals(int $city, bool $arrival = true)
$client->findByTrackNumber(string $trackNumber)
$client->getSession()
$client->forgetSession()
/* This one requires a Delivery Object, see next to see how to build it */
$client->getPrice(Delivery $delivery)
</pre>

## Errors
Requests use a 10 s connection timeout and a 30 s overall timeout. `SergeevPasha\Dellin\Exceptions\DellinApiException`
is thrown on a transport error, HTTP 429 or 5xx, a body that is not JSON, and on HTTP 401 or any error except
"order not found" from `findByTrackNumber()`. Other 4xx answers with a JSON body are returned as arrays, so calculator
validation errors are still available as `{"errors":[...]}`.

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

### Use Case #3 — Predefined routes

There are some predefined routes that will be merged with your routes. You may check them by running:
<code>php artisan route:list</code>

It exposes the same methods to HTTP routes. For more information check out the `src/` folder.
