<?php

use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Mockery as m;
use Ycs77\LaravelRecoverSession\UserSource;

test('can preserve user source data to session', function () {
    now()->setTestNow('2000-01-01 00:00:00');

    $request = Request::create('/');

    /** @var \Illuminate\Session\Store|\Mockery\MockInterface|\Mockery\LegacyMockInterface */
    $session = m::mock(Store::class);
    $session->shouldReceive('put')
        ->once()
        ->with('user_source_for_recover_session_id', [
            'ip' => '127.0.0.1',
            'user_agent' => md5('Symfony'),
            'expired_at' => '2000-01-01 01:00:00',
        ]);

    $userSource = new UserSource($session);

    $userSource->preserve($request);
});

test('user source is validated', function () {
    now()->setTestNow('2000-01-01 00:05:38');

    $request = Request::create('/');

    /** @var \Illuminate\Session\Store|\Mockery\MockInterface|\Mockery\LegacyMockInterface */
    $session = m::mock(Store::class);
    $session->shouldReceive('get')
        ->once()
        ->with('user_source_for_recover_session_id')
        ->andReturn([
            'ip' => '127.0.0.1',
            'user_agent' => md5('Symfony'),
            'expired_at' => '2000-01-01 01:00:00',
        ]);

    $userSource = new UserSource($session);

    expect($userSource->validate($request))->toBeTrue();
});

test('user source is invalid', function () {
    now()->setTestNow('2000-01-01 01:03:07');

    $request = Request::create('/');

    /** @var \Illuminate\Session\Store|\Mockery\MockInterface|\Mockery\LegacyMockInterface */
    $session = m::mock(Store::class);
    $session->shouldReceive('get')
        ->once()
        ->with('user_source_for_recover_session_id')
        ->andReturn([
            'ip' => '127.0.0.1',
            'user_agent' => md5('Symfony'),
            'expired_at' => '2000-01-01 01:00:00',
        ]);

    $userSource = new UserSource($session);

    expect($userSource->validate($request))->toBeFalse();
});

test('can clear user source', function () {
    /** @var \Illuminate\Session\Store|\Mockery\MockInterface|\Mockery\LegacyMockInterface */
    $session = m::mock(Store::class);
    $session->shouldReceive('remove')
        ->once()
        ->with('user_source_for_recover_session_id');

    $userSource = new UserSource($session);

    $userSource->clear();
});
