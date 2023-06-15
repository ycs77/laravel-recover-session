<?php

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Ycs77\LaravelRecoverSession\RecoverSession;
use Ycs77\LaravelRecoverSession\UserSource;

$key = 'sessionkey000000000000000000000000000000';
$sid = 'sessionid0000000000000000000000000000000';

test('can preserve session ID', function () use ($key, $sid) {
    now()->setTestNow('2000-01-01 00:00:00');

    Str::createRandomStringsUsing(fn () => $key);

    /** @var \Illuminate\Foundation\Application */
    $app = $this->app;

    $request = Request::create('/');

    /** @var \Illuminate\Config\Repository */
    $config = $app->make('config');

    /** @var \Illuminate\Cache\Repository */
    $cache = $app->make('cache.store');

    /** @var \Illuminate\Session\Store */
    $session = $app->make('session.store');
    $session->setId($sid);

    /** @var \Illuminate\Encryption\Encrypter */
    $encrypter = $app->make('encrypter');

    $userSource = new UserSource($session);

    $recoverSession = new RecoverSession(
        $config, $cache, $session, $encrypter, $userSource
    );

    $actualKey = $recoverSession->preserve($request);

    expect($actualKey)->toBe('sessionkey000000000000000000000000000000');
    expect($encrypter->decryptString($cache->get('recover_session_sessionkey000000000000000000000000000000')))->toBe('sessionid0000000000000000000000000000000');
});

test('can preserve user source', function () {
    now()->setTestNow('2000-01-01 00:00:00');

    /** @var \Illuminate\Foundation\Application */
    $app = $this->app;

    $request = Request::create('/');

    /** @var \Illuminate\Config\Repository */
    $config = $app->make('config');

    /** @var \Illuminate\Cache\Repository */
    $cache = $app->make('cache.store');

    /** @var \Illuminate\Session\Store */
    $session = $app->make('session.store');

    /** @var \Illuminate\Encryption\Encrypter */
    $encrypter = $app->make('encrypter');

    $userSource = new UserSource($session);

    $recoverSession = new RecoverSession(
        $config, $cache, $session, $encrypter, $userSource
    );

    $recoverSession->preserveUserSource($request);

    expect($session->get('user_source_for_recover_session'))->toBe([
        'hash' => '333a58a1d7d2c4db8f80a4557bf7ba37',
        'expired_at' => '2000-01-01 01:00:00',
    ]);
});

test('can recover session ID', function () use ($key, $sid) {
    now()->setTestNow('2000-01-01 00:00:00');

    /** @var \Illuminate\Foundation\Application */
    $app = $this->app;

    $request = Request::create('/');

    /** @var \Illuminate\Config\Repository */
    $config = $app->make('config');

    /** @var \Illuminate\Cache\Repository */
    $cache = $app->make('cache.store');

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

    $cache->add('recover_session_sessionkey000000000000000000000000000000', $encrypter->encryptString($sid));

    $userSource = new UserSource($session);

    $recoverSession = new RecoverSession(
        $config, $cache, $session, $encrypter, $userSource
    );

    $result = $recoverSession->recover($request, $key);

    expect($result)->toBeTrue();
    expect($cache->has('recover_session_sessionkey000000000000000000000000000000'))->toBeFalse();
    expect($session->getId())->toBe('sessionid0000000000000000000000000000000');
    expect($session->has('user_source_for_recover_session'))->toBeFalse();
});

test('can retrieve session ID', function () use ($key, $sid) {
    /** @var \Illuminate\Foundation\Application */
    $app = $this->app;

    /** @var \Illuminate\Config\Repository */
    $config = $app->make('config');

    /** @var \Illuminate\Cache\Repository */
    $cache = $app->make('cache.store');

    /** @var \Illuminate\Session\Store */
    $session = $app->make('session.store');

    /** @var \Illuminate\Encryption\Encrypter */
    $encrypter = $app->make('encrypter');

    $cache->add('recover_session_sessionkey000000000000000000000000000000', $encrypter->encryptString($sid));

    $userSource = new UserSource($session);

    $recoverSession = new RecoverSession(
        $config, $cache, $session, $encrypter, $userSource
    );

    $actualSid = $recoverSession->retrieve($key);

    expect($cache->has('recover_session_sessionkey000000000000000000000000000000'))->toBeFalse();
    expect($encrypter->decryptString($actualSid))->toBe('sessionid0000000000000000000000000000000');
});
