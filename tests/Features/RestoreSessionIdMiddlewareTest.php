<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ycs77\LaravelRestoreSessionId\Middleware\RestoreSessionId;
use Ycs77\LaravelRestoreSessionId\UserSource;

$sid = 'eyJpdiI6IjB2TVU4SWFYUHJiMDJveU5WLzRiR2c9PSIsInZhbHVlIjoiYW01VzdRQ0RIakQzUklISkJmWTMyVDd6bDdISHRLL2dDb1QxaXVDS2hUVnJ3T1dJTEhaQWFsb1ZTTlZWMlRHZCIsIm1hYyI6IjEzNjAzOWNiNTRlMzQ1NmU0N2I0YWUyMzAzOTcwZTA3MWRiNTUzYjIyZDhmNjYzOGMxMzk5MDk1ZThmZjk1YjIiLCJ0YWciOiIifQ=='; // encrypted "sessionid0000000000000000000000000000000"

test('can restore session ID from url', function () use ($sid) {
    now()->setTestNow('2000-01-01 00:00:00');

    $request = Request::create("/?sid=$sid", 'POST');

    /** @var \Illuminate\Session\Store */
    $session = $this->app->make('session.store');
    $session->setId(null);
    $session->put('user_source_for_restore_session_id', [
        'ip' => '127.0.0.1',
        'user_agent' => md5('Symfony'),
        'expired_at' => '2000-01-01 01:00:00',
    ]);

    /** @var \Illuminate\Encryption\Encrypter */
    $encrypter = $this->app->make('encrypter');

    /** @var \Ycs77\LaravelRestoreSessionId\UserSource */
    $userSource = $this->app->make(UserSource::class);

    $middleware = new RestoreSessionId($session, $encrypter, $userSource);

    $middleware->handle($request, fn () => new Response());

    expect($session->getId())->toBe('sessionid0000000000000000000000000000000');
});

test('can pass if session ID is not from url', function () {
    $request = Request::create('/', 'POST');

    /** @var \Illuminate\Session\Store */
    $session = $this->app->make('session.store');
    $session->setId(null);

    /** @var \Illuminate\Encryption\Encrypter */
    $encrypter = $this->app->make('encrypter');

    /** @var \Ycs77\LaravelRestoreSessionId\UserSource */
    $userSource = $this->app->make(UserSource::class);

    $middleware = new RestoreSessionId($session, $encrypter, $userSource);

    $middleware->handle($request, fn () => new Response());

    expect($session->getId())->not()->toBe('sessionid0000000000000000000000000000000');
});

test('can pass if session ID is expired', function () use ($sid) {
    now()->setTestNow('2000-01-01 01:03:07');

    $request = Request::create("/?sid=$sid", 'POST');

    /** @var \Illuminate\Session\Store */
    $session = $this->app->make('session.store');
    $session->setId(null);
    $session->put('user_source_for_restore_session_id', [
        'ip' => '127.0.0.1',
        'user_agent' => md5('Symfony'),
        'expired_at' => '2000-01-01 01:00:00',
    ]);

    /** @var \Illuminate\Encryption\Encrypter */
    $encrypter = $this->app->make('encrypter');

    /** @var \Ycs77\LaravelRestoreSessionId\UserSource */
    $userSource = $this->app->make(UserSource::class);

    $middleware = new RestoreSessionId($session, $encrypter, $userSource);

    $middleware->handle($request, fn () => new Response());

    expect($session->getId())->not()->toBe('sessionid0000000000000000000000000000000');
});
