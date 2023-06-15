<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ycs77\LaravelRecoverSession\Middleware\RecoverSession;
use Ycs77\LaravelRecoverSession\RecoverSession as SessionRecoverer;
use Ycs77\LaravelRecoverSession\UserSource;

$key = 'sessionkey000000000000000000000000000000';

$sid = 'eyJpdiI6IjB2TVU4SWFYUHJiMDJveU5WLzRiR2c9PSIsInZhbHVlIjoiYW01VzdRQ0RIakQzUklISkJmWTMyVDd6bDdISHRLL2dDb1QxaXVDS2hUVnJ3T1dJTEhaQWFsb1ZTTlZWMlRHZCIsIm1hYyI6IjEzNjAzOWNiNTRlMzQ1NmU0N2I0YWUyMzAzOTcwZTA3MWRiNTUzYjIyZDhmNjYzOGMxMzk5MDk1ZThmZjk1YjIiLCJ0YWciOiIifQ=='; // encrypted "sessionid0000000000000000000000000000000"

test('can recover session ID from url', function () use ($key, $sid) {
    /** @var \Illuminate\Foundation\Application */
    $app = $this->app;

    now()->setTestNow('2000-01-01 00:00:00');

    $request = Request::create("/?sid=$key", 'POST');

    /** @var \Illuminate\Config\Repository */
    $config = $app->make('config');

    /** @var \Illuminate\Cache\Repository */
    $cache = $app->make('cache.store');
    $cache->add('recover_session_sessionkey000000000000000000000000000000', $sid);

    /** @var \Illuminate\Session\Store */
    $session = $app->make('session.store');
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

    $sessionRecoverer = new SessionRecoverer(
        $config, $cache, $session, $encrypter, $userSource
    );

    $middleware = new RecoverSession($config, $sessionRecoverer);

    $middleware->handle($request, fn () => new Response());

    expect($cache->has('recover_session_sessionkey000000000000000000000000000000'))->toBeFalse();
    expect($session->getId())->toBe('sessionid0000000000000000000000000000000');
    expect($session->has('user_source_for_recover_session'))->toBeFalse();
});
