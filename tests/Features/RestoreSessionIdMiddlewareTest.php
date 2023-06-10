<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ycs77\LaravelRestoreSessionId\Middleware\RestoreSessionId;

test('can restore session ID from url', function () {
    $sid = 'eyJpdiI6IjB2TVU4SWFYUHJiMDJveU5WLzRiR2c9PSIsInZhbHVlIjoiYW01VzdRQ0RIakQzUklISkJmWTMyVDd6bDdISHRLL2dDb1QxaXVDS2hUVnJ3T1dJTEhaQWFsb1ZTTlZWMlRHZCIsIm1hYyI6IjEzNjAzOWNiNTRlMzQ1NmU0N2I0YWUyMzAzOTcwZTA3MWRiNTUzYjIyZDhmNjYzOGMxMzk5MDk1ZThmZjk1YjIiLCJ0YWciOiIifQ=='; // encrypted "sessionid0000000000000000000000000000000"

    $request = Request::create("/?sid=$sid", 'POST');

    /** @var \Illuminate\Session\Store */
    $session = $this->app->make('session.store');
    $session->setId('sessionid0000000000000000000000000000001');

    /** @var \Illuminate\Encryption\Encrypter */
    $encrypter = $this->app->make('encrypter');

    $middleware = new RestoreSessionId($session, $encrypter);

    $middleware->handle($request, fn () => new Response());

    expect($session->getId())->toBe('sessionid0000000000000000000000000000000');
});

test('can pass if session ID is not from url', function () {
    $request = Request::create('/', 'POST');

    /** @var \Illuminate\Session\Store */
    $session = $this->app->make('session.store');
    $session->setId('sessionid0000000000000000000000000000001');

    /** @var \Illuminate\Encryption\Encrypter */
    $encrypter = $this->app->make('encrypter');

    $middleware = new RestoreSessionId($session, $encrypter);

    $middleware->handle($request, fn () => new Response());

    expect($session->getId())->toBe('sessionid0000000000000000000000000000001');
});
