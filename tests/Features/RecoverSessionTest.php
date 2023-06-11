<?php

use Illuminate\Http\Request;
use Mockery as m;
use Ycs77\LaravelRecoverSession\RecoverSession;
use Ycs77\LaravelRecoverSession\UserSource;

test('call RecoverSession::preserveUserSource()', function () {
    /** @var \Illuminate\Foundation\Application */
    $app = $this->app;

    $request = Request::create('/');

    /** @var \Ycs77\LaravelRecoverSession\UserSource|\Mockery\MockInterface|\Mockery\LegacyMockInterface */
    $userSource = m::mock(UserSource::class);
    $userSource->shouldReceive('preserve')
        ->once()
        ->with($request, 60);

    $app->instance(UserSource::class, $userSource);

    RecoverSession::preserveUserSource($request);
});
