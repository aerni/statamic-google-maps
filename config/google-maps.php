<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your Google Maps API Key.
    | Make sure you enabled "Places API (New)" in your Google Cloud project.
    |
    */

    'google_places_api_key' => env('GOOGLE_PLACES_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Map ID
    |--------------------------------------------------------------------------
    |
    | This addon uses Advanced Markers which require a map ID.
    | Learn how to create your own map ID:
    | https://developers.google.com/maps/documentation/javascript/map-ids/get-map-id
    |
    */

    'map_id' => '4f33f0e7f6ab915e',

    /*
    |--------------------------------------------------------------------------
    | Center
    |--------------------------------------------------------------------------
    |
    | The center of the map when no place is selected.
    |
    */

    'center' => [
        'lat' => 20,
        'lng' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Zoom
    |--------------------------------------------------------------------------
    |
    | The zoom level of the map when no place is selected.
    |
    */

    'zoom' => 2,

];
