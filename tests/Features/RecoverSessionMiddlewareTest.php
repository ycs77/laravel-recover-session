<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ycs77\LaravelRecoverSession\Middleware\RecoverSession;
use Ycs77\LaravelRecoverSession\UserSource;

$sid = 'eyJpdiI6IjB2TVU4SWFYUHJiMDJveU5WLzRiR2c9PSIsInZhbHVlIjoiYW01VzdRQ0RIakQzUklISkJmWTMyVDd6bDdISHRLL2dDb1QxaXVDS2hUVnJ3T1dJTEhaQWFsb1ZTTlZWMlRHZCIsIm1hYyI6IjEzNjAzOWNiNTRlMzQ1NmU0N2I0YWUyMzAzOTcwZTA3MWRiNTUzYjIyZDhmNjYzOGMxMzk5MDk1ZThmZjk1YjIiLCJ0YWciOiIifQ=='; // encrypted "sessionid0000000000000000000000000000000"

test('can recover session ID from url', function () use ($sid) {
    /** @var \Illuminate\Foundation\Application */
    $app = $this->app;

    now()->setTestNow('2000-01-01 00:00:00');

    $request = Request::create("/?sid=$sid", 'POST');

    /** @var \Illuminate\Session\Store */
    $session = $app->make('session.store');
    $session->setId(null);
    $session->put('user_source_for_recover_session', [
        'hash' => md5(json_encode([
            'ip' => '127.0.0.1',
            'user_agent' => 'Symfony',
        ])),
        'expired_at' => '2000-01-01 01:00:00',
    ]);

    /** @var \Illuminate\Encryption\Encrypter */
    $encrypter = $app->make('encrypter');

    /** @var \Ycs77\LaravelRecoverSession\UserSource */
    $userSource = $app->make(UserSource::class);

    $middleware = new RecoverSession($session, $encrypter, $userSource);

    $middleware->handle($request, fn () => new Response());

    expect($session->getId())->toBe('sessionid0000000000000000000000000000000');
    expect($session->get('user_source_for_recover_session'))->toBeNull();
});

test('can pass if session ID is not from url', function () {
    /** @var \Illuminate\Foundation\Application */
    $app = $this->app;

    $request = Request::create('/', 'POST');

    /** @var \Illuminate\Session\Store */
    $session = $app->make('session.store');
    $session->setId(null);

    /** @var \Illuminate\Encryption\Encrypter */
    $encrypter = $app->make('encrypter');

    /** @var \Ycs77\LaravelRecoverSession\UserSource */
    $userSource = $app->make(UserSource::class);

    $middleware = new RecoverSession($session, $encrypter, $userSource);

    $middleware->handle($request, fn () => new Response());

    expect($session->getId())->not()->toBe('sessionid0000000000000000000000000000000');
    expect($session->get('user_source_for_recover_session'))->toBeNull();
});

test('can pass if session ID is expired', function () use ($sid) {
    /** @var \Illuminate\Foundation\Application */
    $app = $this->app;

    now()->setTestNow('2000-01-01 01:03:07');

    $request = Request::create("/?sid=$sid", 'POST');

    /** @var \Illuminate\Session\Store */
    $session = $app->make('session.store');
    $session->setId(null);
    $session->put('user_source_for_recover_session', [
        'hash' => md5(json_encode([
            'ip' => '127.0.0.1',
            'user_agent' => 'Symfony',
        ])),
        'expired_at' => '2000-01-01 01:00:00',
    ]);

    /** @var \Illuminate\Encryption\Encrypter */
    $encrypter = $app->make('encrypter');

    /** @var \Ycs77\LaravelRecoverSession\UserSource */
    $userSource = $app->make(UserSource::class);

    $middleware = new RecoverSession($session, $encrypter, $userSource);

    $middleware->handle($request, fn () => new Response());

    expect($session->getId())->not()->toBe('sessionid0000000000000000000000000000000');
    expect($session->get('user_source_for_recover_session'))->toBeNull();
});
