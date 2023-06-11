<?php

namespace Ycs77\LaravelRecoverSession;

use Illuminate\Http\Request;

class RecoverSession
{
    /**
     * Preserve the user information into session.
     */
    public static function preserveUserSource(Request $request)
    {
        return app(UserSource::class)->preserve($request);
    }
}
