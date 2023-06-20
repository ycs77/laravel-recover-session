<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Session ID Key
    |--------------------------------------------------------------------------
    |
    | The query key for the session ID key in the URL.
    |
    */

    'session_id_key' => 'sid',

    /*
    |--------------------------------------------------------------------------
    | Auto Recover Session on Global
    |--------------------------------------------------------------------------
    |
    | This config controls whether to automatically recover the session
    | globally.
    |
    */

    'global' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Driver
    |--------------------------------------------------------------------------
    |
    | The cache driver for saving encrypted session ID.
    |
    */

    'cache_driver' => env('CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Cached Session ID Expired Minites
    |--------------------------------------------------------------------------
    |
    | The number of the cached session ID expired minutes.
    |
    */

    'ttl' => 60,

];
